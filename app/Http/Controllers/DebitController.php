<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\DebitRequest;

class DebitController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(DebitRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request): JsonResponse {
            $fromUser = $request->user();

            $toUser = $request->toUser();

            $amount = $request->amount();

            $fromUserWallet = $fromUser->wallet()->lockForUpdate()->first();

            $toUserWallet = $toUser->wallet()->lockForUpdate()->first();

            if ($fromUserWallet->balance < $amount) {
                return response()->json([
                    'message' => __('Insufficient balance'),
                ], 400);
            }

            $fromUserWallet->update([
                'balance' => $fromUserWallet->balance - $amount,
            ]);

            $toUserWallet->update([
                'balance' => $toUserWallet->balance + $amount,
            ]);

            return response()->json([
                'message' => __('Transaction successful'),
            ]);
        });
    }
}
