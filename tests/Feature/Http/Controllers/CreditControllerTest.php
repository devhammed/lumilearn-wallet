<?php

use App\Models\User;
use App\Models\Wallet;
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

    $wallet = Wallet::factory()->for($user)->create();

    $token = $user->createToken('auth_token')->plainTextToken;

    $response = withToken($token)->postJson(route('credit'), [
        'amount' => 0,
    ]);

    $response->assertUnprocessable();

    $response->assertJsonValidationErrors(['amount']);

    $this->assertDatabaseHas('wallets', [
        'user_id' => $user->getKey(),
        'balance->amount' => $wallet->balance->getAmount(),
    ]);
})->coversClass(CreditController::class);

it('credits the authenticated user wallet', function () {
    $user = User::factory()->create();

    $wallet = Wallet::factory()->for($user)->create();

    $creditAmount = money(fake()->numberBetween(10, 100), convert: true);

    $token = $user->createToken('auth_token')->plainTextToken;

    $response = withToken($token)->postJson(route('credit'), [
        'amount' => $creditAmount->getValue(),
    ]);

    $response->assertOk();

    $response->assertJson([
        'message' => __('Transaction successful'),
    ]);

    $this->assertDatabaseHas('wallets', [
        'user_id' => $user->getKey(),
        'balance->amount' => $wallet->balance->add($creditAmount)->getAmount(),
    ]);
})->coversClass(CreditController::class)->repeat(50);
