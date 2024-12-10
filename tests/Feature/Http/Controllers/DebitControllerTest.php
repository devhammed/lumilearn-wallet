<?php


use App\Models\User;
use App\Models\Wallet;
use App\Http\Controllers\DebitController;
use function Pest\Laravel\withToken;
use function Pest\Laravel\withoutToken;

it('requires authentication', function () {
    $targetUser = User::factory()->create();

    $response = withoutToken()->postJson(route('debit'), [
        'to_user_id' => $targetUser->getKey(),
        'amount' => fake()->randomFloat(2, 1, 100),
    ]);

    $response->assertUnauthorized();

    $response->assertJsonStructure(['message']);
})->coversClass(DebitController::class);

it('fails if the target user does not exist', function () {
    $user = User::factory()->create();

    $token = $user->createToken('auth_token')->plainTextToken;

    $response = withToken($token)->postJson(route('debit'), [
        'to_user_id' => 'non-existent-id',
        'amount' => fake()->randomFloat(2, 1, 100),
    ]);

    $response->assertUnprocessable();

    $response->assertJsonValidationErrors(['to_user_id']);
})->coversClass(DebitController::class);

it('fails if the target user is the same as the current user', function () {
    $user = User::factory()->create();

    Wallet::factory()->for($user)->create();

    $token = $user->createToken('auth_token')->plainTextToken;

    $response = withToken($token)->postJson(route('debit'), [
        'to_user_id' => $user->getKey(),
        'amount' => 10,
    ]);

    $response->assertUnprocessable();

    $response->assertJsonValidationErrors(['to_user_id']);
})->coversClass(DebitController::class);

it('fails if the amount is not greater than 0', function () {
    $user = User::factory()->create();

    $targetUser = User::factory()->create();

    $token = $user->createToken('auth_token')->plainTextToken;

    $userWallet = Wallet::factory()->for($user)->withZeroBalance()->create();

    $targetUserWallet = Wallet::factory()->for($targetUser)->withZeroBalance()->create();

    $response = withToken($token)->postJson(route('debit'), [
        'to_user_id' => $targetUser->getKey(),
        'amount' => 0,
    ]);

    $response->assertUnprocessable();

    $response->assertJsonValidationErrors(['amount']);

    $this->assertDatabaseHas('wallets', [
        'id' => $userWallet->getKey(),
        'user_id' => $user->getKey(),
        'balance->amount' => $userWallet->balance->getAmount(),
        'balance->currency' => $userWallet->balance->getCurrency()->getCurrency(),
    ]);

    $this->assertDatabaseHas('wallets', [
        'id' => $targetUserWallet->getKey(),
        'user_id' => $targetUser->getKey(),
        'balance->amount' => $targetUserWallet->balance->getAmount(),
        'balance->currency' => $targetUserWallet->balance->getCurrency()->getCurrency(),
    ]);
})->coversClass(DebitController::class);

it('fails if the current user has insufficient balance', function () {
    $user = User::factory()->create();

    $targetUser = User::factory()->create();

    $userWallet = Wallet::factory()->for($user)->create();

    $targetUserWallet = Wallet::factory()->for($targetUser)->withZeroBalance()->create();

    $token = $user->createToken('auth_token')->plainTextToken;

    $response = withToken($token)->postJson(route('debit'), [
        'to_user_id' => $targetUser->getKey(),
        'amount' => $userWallet->balance->multiply(2)->getValue(),
    ]);

    $response->assertBadRequest();

    $response->assertJson([
        'message' => __('Insufficient balance'),
    ]);

    $this->assertDatabaseHas('wallets', [
        'id' => $userWallet->getKey(),
        'user_id' => $user->getKey(),
        'balance->amount' => $userWallet->balance->getAmount(),
        'balance->currency' => $userWallet->balance->getCurrency()->getCurrency(),
    ]);

    $this->assertDatabaseHas('wallets', [
        'id' => $targetUserWallet->getKey(),
        'user_id' => $targetUser->getKey(),
        'balance->amount' => $targetUserWallet->balance->getAmount(),
        'balance->currency' => $targetUserWallet->balance->getCurrency()->getCurrency(),
    ]);
})->coversClass(DebitController::class)->repeat(50);

it('debits the current user wallet and credits the target user wallet', function () {
    $user = User::factory()->create();

    $targetUser = User::factory()->create();

    $userWallet = Wallet::factory()->for($user)->create();

    $targetUserWallet = Wallet::factory()->for($targetUser)->withZeroBalance()->create();

    $token = $user->createToken('auth_token')->plainTextToken;

    $debitAmount = money(fake()->randomFloat(2, 1, $userWallet->balance->getValue()), convert: true);

    $response = withToken($token)->postJson(route('debit'), [
        'to_user_id' => $targetUser->getKey(),
        'amount' => $debitAmount->getValue(),
    ]);

    $response->assertOk();

    $response->assertJson([
        'message' => __('Transaction successful'),
    ]);

    $this->assertDatabaseHas('wallets', [
        'id' => $userWallet->getKey(),
        'user_id' => $user->getKey(),
        'balance->amount' => $userWallet->balance->subtract($debitAmount)->getAmount(),
        'balance->currency' => $userWallet->balance->getCurrency()->getCurrency(),
    ]);

    $this->assertDatabaseHas('wallets', [
        'id' => $targetUserWallet->getKey(),
        'user_id' => $targetUser->getKey(),
        'balance->amount' => $targetUserWallet->balance->add($debitAmount)->getAmount(),
        'balance->currency' => $targetUserWallet->balance->getCurrency()->getCurrency(),
    ]);
})->coversClass(DebitController::class)->repeat(50);
