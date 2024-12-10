<?php

use App\Models\User;
use App\Models\Wallet;
use App\Http\Controllers\CreditController;
use function Pest\Laravel\withToken;
use function Pest\Laravel\withoutToken;

it('requires authentication', function () {
    $response = withoutToken()->postJson(route('credit'), [
        'amount' => fake()->randomFloat(2, 1, 100),
    ]);

    $response->assertUnauthorized();

    $response->assertJsonStructure(['message']);
})->coversClass(CreditController::class);

it('fails if the amount is not greater than 0', function () {
    $user = User::factory()->create();

    $wallet = Wallet::factory()->for($user)->create();

    $token = $user->createToken('auth_token')->plainTextToken;

    $response = withToken($token)->postJson(route('credit'), [
        'amount' => 0,
    ]);

    $response->assertUnprocessable();

    $response->assertJsonValidationErrors(['amount']);

    $this->assertDatabaseHas('wallets', [
        'id' => $wallet->getKey(),
        'user_id' => $user->getKey(),
        'balance->amount' => $wallet->balance->getAmount(),
        'balance->currency' => $wallet->balance->getCurrency()->getCurrency(),
    ]);
})->coversClass(CreditController::class);

it('credits the authenticated user wallet', function () {
    $user = User::factory()->create();

    $wallet = Wallet::factory()->for($user)->create();

    $creditAmount = money(fake()->randomFloat(2, 1, 100), convert: true);

    $token = $user->createToken('auth_token')->plainTextToken;

    $response = withToken($token)->postJson(route('credit'), [
        'amount' => $creditAmount->getValue(),
    ]);

    $response->assertOk();

    $response->assertJson([
        'message' => __('Transaction successful'),
    ]);

    $this->assertDatabaseHas('wallets', [
        'id' => $wallet->getKey(),
        'user_id' => $user->getKey(),
        'balance->amount' => $wallet->balance->add($creditAmount)->getAmount(),
        'balance->currency' => $wallet->balance->getCurrency()->getCurrency(),
    ]);
})->coversClass(CreditController::class)->repeat(50);
