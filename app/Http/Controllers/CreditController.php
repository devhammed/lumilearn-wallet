<?php

namespace App\Http\Controllers;

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

            $amount = $request->money('amount');

            $wallet = $user->wallet()->lockForUpdate()->sole();

            $wallet->update([
                'balance' => $wallet->balance->add($amount),
            ]);

            return response()->json([
                'message' => __('Transaction successful'),
            ]);
        });
    }
}
