<?php


use App\Models\User;
use App\Http\Controllers\DebitController;
use function Pest\Laravel\withToken;

it('debits the current user\'s wallet and credits the target user\'s wallet', function () {
    $user = User::factory()->create();

    $targetUser = User::factory()->create();

    $targetUserBalance = fake()->numberBetween(10, 100);

    $userBalance = fake()->numberBetween($targetUserBalance + 1, 200);

    $toDebit = fake()->numberBetween(1, $userBalance - $targetUserBalance);

    $userWallet = $user->wallet()->create(['balance' => $userBalance]);

    $targetUserWallet = $targetUser->wallet()->create(['balance' => $targetUserBalance]);

    $token = $user->createToken('auth_token')->plainTextToken;

    $response = withToken($token)->postJson(route('debit'), [
        'to_user_id' => $targetUser->id,
        'amount' => $toDebit,
    ]);

    $response->assertOk();

    $response->assertJson([
        'message' => __('Transaction successful'),
    ]);

    $this->assertDatabaseHas('wallets', [
        'user_id' => $user->id,
        'balance' => $userWallet->currency->toDatabaseAmount($userBalance - $toDebit),
    ]);

    $this->assertDatabaseHas('wallets', [
        'user_id' => $targetUser->id,
        'balance' => $targetUserWallet->currency->toDatabaseAmount($targetUserBalance + $toDebit),
    ]);
})->repeat(50)->coversClass(DebitController::class);

it('fails if the target user does not exist', function () {
    $user = User::factory()->create();

    $token = $user->createToken('auth_token')->plainTextToken;

    $response = withToken($token)->postJson(route('debit'), [
        'to_user_id' => 'non-existent-id',
        'amount' => 10,
    ]);

    $response->assertUnprocessable();

    $response->assertJsonValidationErrors(['to_user_id']);
})->coversClass(DebitController::class);

it('fails if the target user is the same as the current user', function () {
    $user = User::factory()->create();

    $userBalance = fake()->numberBetween(10, 100);

    $token = $user->createToken('auth_token')->plainTextToken;

    $user->wallet()->create(['balance' => $userBalance]);

    $response = withToken($token)->postJson(route('debit'), [
        'to_user_id' => $user->id,
        'amount' => 10,
    ]);

    $response->assertUnprocessable();

    $response->assertJsonValidationErrors(['to_user_id']);
})->coversClass(DebitController::class);

it('fails if the amount is not greater than 0', function () {
    $user = User::factory()->create();

    $targetUser = User::factory()->create();

    $userBalance = fake()->numberBetween(10, 100);

    $token = $user->createToken('auth_token')->plainTextToken;

    $user->wallet()->create(['balance' => $userBalance]);

    $targetUser->wallet()->create(['balance' => 0]);

    $response = withToken($token)->postJson(route('debit'), [
        'to_user_id' => $targetUser->id,
        'amount' => 0,
    ]);

    $response->assertUnprocessable();

    $response->assertJsonValidationErrors(['amount']);
})->coversClass(DebitController::class);

it('fails if the current user has insufficient balance', function () {
    $user = User::factory()->create();

    $targetUser = User::factory()->create();

    $userBalance = fake()->numberBetween(10, 100);

    $user->wallet()->create(['balance' => $userBalance]);

    $targetUser->wallet()->create(['balance' => 0]);

    $token = $user->createToken('auth_token')->plainTextToken;

    $response = withToken($token)->postJson(route('debit'), [
        'to_user_id' => $targetUser->id,
        'amount' => $userBalance * 2,
    ]);

    $response->assertBadRequest();

    $response->assertJson([
        'message' => __('Insufficient balance'),
    ]);
})->repeat(50)->coversClass(DebitController::class);
