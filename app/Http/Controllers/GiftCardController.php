<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexGiftCardRequest;
use App\Http\Requests\StoreGiftCardRequest;
use App\Http\Requests\UpdateGiftCardRequest;
use App\Models\GiftCard;
use Illuminate\Http\JsonResponse;

class GiftCardController extends Controller
{
    public function index(IndexGiftCardRequest $request): JsonResponse
    {
        $payload = $request->validated();

        $giftCards = GiftCard::when(isset($payload['provider']), static fn ($query) => $query->where('provider', $payload['provider']))
            ->with('transaction.user')
            ->orderByDesc('id')
            ->paginate(10);

        $pagination = $giftCards->toArray();
        $giftCardsArr = $pagination['data'];
        unset($pagination['data']);

        return response()->json([
            'gift_cards' => $giftCardsArr,
            'pagination' => $pagination,
        ]);
    }

    public function store(StoreGiftCardRequest $request): JsonResponse
    {
        $payload = $request->validated();

        $giftCards = [];

        foreach ($payload['codes'] as $code) {
            $giftCards[] = [
                'code' => $code['code'],
                'country' => $payload['country'],
                'provider' => $payload['provider'],
                'value' => $payload['value'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        GiftCard::insert($giftCards);

        return response()->json([
            'gift_cards' => GiftCard::orderByDesc('id')->limit(count($giftCards))->get(),
        ], 201);
    }

    public function update(GiftCard $giftCard, UpdateGiftCardRequest $request): JsonResponse
    {
        $payload = $request->validated();

        $giftCard->update($payload);

        return response()->json([
            'gift_card' => $giftCard,
        ]);
    }

    public function destroy(GiftCard $giftCard): void
    {
        $giftCard->delete();
    }
}
