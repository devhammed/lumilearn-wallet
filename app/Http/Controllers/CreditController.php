<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\CreditRequest;

class CreditController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(CreditRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request): JsonResponse {
            $user = $request->user();

            $amount = $request->float('amount');

            $wallet = Wallet::query()
                            ->whereUserId($user->id)
                            ->lockForUpdate()
                            ->firstOrFail();

            $wallet->update([
                'balance' => $wallet->balance + $amount,
            ]);

            return response()->json([
                'message' => __('Transaction successful'),
            ]);
        });
    }
}
