<?php

namespace App\Http\Controllers;

use App\Builders\RobuxGroupBuilder;
use App\Http\Requests\IndexSupplierPaymentRequest;
use App\Http\Requests\StoreSupplierPaymentRequest;
use App\Http\Requests\UpdateSupplierPaymentRequest;
use App\Mail\SupplierPaymentMail;
use App\Models\SupplierPayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class SupplierPaymentController extends Controller
{
    public function index(IndexSupplierPaymentRequest $request): JsonResponse
    {
        $supplierPayments = null;
        $payload = $request->validated();

        $totals = [];

        if (! isset($payload['user_id'])) {
            $supplierPayments = SupplierPayment::with('supplierUser:id,username')->orderByDesc('id')->paginate(10);
        } else {
            /** @var User $supplier */
            $supplier = User::find($payload['user_id']);
            $this->authorize('update', $supplier);

            $supplierPayments = $supplier->supplierPayments()->orderByDesc('id')->paginate(10);

            $supplier->loadTotalPendingOrPaidSupplierWithdrawals()
                ->load(['robuxGroups' => static function (HasMany $query) {
                    /** @var RobuxGroupBuilder $query */
                    $query->select(['id', 'supplier_user_id'])->withTotalEarnings()->withTrashed();
                }])->append(['total_supplier_withdrawals']);

            $totals['total_earnings'] = currency_format($supplier->robuxGroups->sum('total_earnings'));
            $totals['total_withdrawals'] = currency_format($supplier->total_supplier_withdrawals);
            $totals['available_earnings'] = currency_format($supplier->robuxGroups->sum('total_earnings') - $supplier->total_supplier_withdrawals);
        }

        $pagination = $supplierPayments->toArray();
        $supplierPaymentsArr = $pagination['data'];
        unset($pagination['data']);

        return response()->json(array_merge([
            'payments' => $supplierPaymentsArr,
            'pagination' => $pagination,
        ], $totals));
    }

    public function store(StoreSupplierPaymentRequest $request): JsonResponse
    {
        $payload = $request->validated();

        /** @var User $supplier */
        $supplier = auth()->user();
        $supplier->loadTotalPendingOrPaidSupplierWithdrawals()
            ->load(['robuxGroups' => static function (HasMany $query) {
                /** @var RobuxGroupBuilder $query */
                $query->select(['id', 'supplier_user_id'])->withTotalEarnings()->withTrashed();
            }]);

        $availableEarnings = $supplier->robuxGroups->sum('total_earnings') - $supplier->total_supplier_withdrawals;
        $formattedAvailableEarnings = currency_format($availableEarnings);

        abort_if($availableEarnings < (int) $payload['value'], 422, "You have only \${$formattedAvailableEarnings} available earnings.");

        /** @var SupplierPayment $supplierPayment */
        $supplierPayment = $supplier->supplierPayments()->create([
            'method' => $payload['method'],
            'destination' => $payload['destination'],
            'value' => $payload['value'],
            'status' => SupplierPayment::STATUS_PENDING,
        ]);

        Mail::send(new SupplierPaymentMail($supplierPayment));

        return response()->json($supplierPayment);
    }

    public function update(SupplierPayment $supplierPayment, UpdateSupplierPaymentRequest $request): JsonResponse
    {
        $payload = $request->validated();

        if ($payload['status'] !== SupplierPayment::STATUS_DENIED) {
            $payload['denial_reason'] = null;
        }

        $supplierPayment->update($payload);

        return response()->json($supplierPayment);
    }
}
