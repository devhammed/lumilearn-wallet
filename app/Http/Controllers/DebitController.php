<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
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

            $amount = $request->float('amount');

            $fromUserWallet = Wallet::query()
                                    ->whereUserId($fromUser->id)
                                    ->lockForUpdate()
                                    ->firstOrFail();

            $toUserWallet = Wallet::query()
                                  ->whereUserId($request->to_user_id)
                                  ->lockForUpdate()
                                  ->firstOrFail();

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
