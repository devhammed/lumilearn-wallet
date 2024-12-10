<?php

use App\Models\User;
use App\Http\Controllers\CreditController;
use function Pest\Laravel\withToken;
use function Pest\Laravel\withoutToken;

it('requires authentication', function () {
    $response = withoutToken()->postJson(route('credit'), [
        'amount' => 10,
    ]);

    $response->assertUnauthorized();

    $response->assertJsonStructure(['message']);
})->coversClass(CreditController::class);

it('fails if the amount is not greater than 0', function () {
    $user = User::factory()->create();

    $userBalance = fake()->numberBetween(10, 100);

    $wallet = $user->wallet()->create(['balance' => $userBalance]);

    $token = $user->createToken('auth_token')->plainTextToken;

    $response = withToken($token)->postJson(route('credit'), [
        'amount' => 0,
    ]);

    $response->assertUnprocessable();

    $response->assertJsonValidationErrors(['amount']);

    $this->assertDatabaseHas('wallets', [
        'user_id' => $user->id,
        'balance' => $wallet->currency->toDatabaseAmount($userBalance),
    ]);
})->coversClass(CreditController::class);

it('credits the authenticated user\'s wallet', function () {
    $user = User::factory()->create();

    $creditAmount = fake()->numberBetween(10, 100);

    $userBalance = fake()->numberBetween(10, 100);

    $wallet = $user->wallet()->create(['balance' => $userBalance]);

    $token = $user->createToken('auth_token')->plainTextToken;

    $finalAmount = $userBalance + $creditAmount;

    $response = withToken($token)->postJson(route('credit'), [
        'amount' => $creditAmount,
    ]);

    $response->assertOk();

    $response->assertJson([
        'message' => __('Transaction successful'),
    ]);

    $this->assertDatabaseHas('wallets', [
        'user_id' => $user->id,
        'balance' => $wallet->currency->toDatabaseAmount($finalAmount),
    ]);
})->coversClass(CreditController::class)->repeat(50);
