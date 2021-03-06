<?php

namespace App\Http\Controllers;

use App\Builders\RobuxAccountBuilder;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class SupplierController extends Controller
{
    public function index(): JsonResponse
    {
        /** @var Collection $suppliers */
        $suppliers = User::withTotalSupplierWithdrawals()
            ->where('role', User::ROLE_SUPPLIER)
            ->with(['robuxAccounts' => static function (HasMany $query) {
                /** @var RobuxAccountBuilder $query */
                $query->select(['id', 'supplier_user_id'])->withTotalEarnings()->withTrashed();
            }])
            ->paginate(10);

        $suppliersArr = $suppliers->append(['formatted_robux_rate', 'total_supplier_withdrawals']);

        $suppliersArr->map(static function (User $supplier) {
            $totalEarnings = (float) bcdiv((string) floor((float) bcmul($supplier->robuxAccounts->sum('total_earnings'), '100')), '100', 2);
            $supplier['formatted_accounts_total_earnings'] = currency_format($totalEarnings);
            $supplier['formatted_total_supplier_withdrawals'] = currency_format($supplier->total_supplier_withdrawals);
            $supplier['formatted_accounts_available_earnings'] = currency_format($totalEarnings - $supplier->total_supplier_withdrawals);
        });

        $pagination = $suppliers->toArray();
        unset($pagination['data']);

        return response()->json([
            'suppliers' => $suppliersArr,
            'pagination' => $pagination,
        ]);
    }

    public function show(User $supplier): JsonResponse
    {
        $this->authorize('update', $supplier);

        if (! $supplier->robux_rate) {
            $supplier->robux_rate = Cache::get('robux-supplier-rate');
        }

        $supplier->robux_rate = (float) bcmul($supplier->robux_rate, '1000', 2);

        return response()->json($supplier->only(['id', 'username', 'robux_rate']));
    }

    public function update(User $supplier, UpdateSupplierRequest $request): JsonResponse
    {
        $payload = $request->validated();

        $supplier->update([
            'robux_rate' => $payload['robux_rate'] ? $payload['robux_rate'] / 1000 : null,
        ]);

        return response()->json($supplier->append('formatted_robux_rate'));
    }
}
