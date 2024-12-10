<?php


use App\Models\User;
use function Pest\Laravel\withToken;

it('debits the current user\'s wallet and credits the target user\'s wallet', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $targetUser = User::factory()->create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);

    $user->wallet()->create(['balance' => 100]);

    $targetUser->wallet()->create(['balance' => 50]);

    $token = $user->createToken('auth_token')->plainTextToken;

    $response = withToken($token)->postJson(route('debit'), [
        'to_user_id' => $targetUser->id,
        'amount' => 30,
    ]);

    $response->assertOk();

    $response->assertJson([
        'message' => __('Transaction successful'),
    ]);

    $this->assertDatabaseHas('wallets', [
        'user_id' => $user->id,
        'balance' => 70,
    ]);

    $this->assertDatabaseHas('wallets', [
        'user_id' => $targetUser->id,
        'balance' => 80,
    ]);
});

it('fails if the amount is not greater than 0', function () {
    $user = User::factory()->create();

    $targetUser = User::factory()->create();

    $token = $user->createToken('auth_token')->plainTextToken;

    $response = withToken($token)->postJson(route('debit'), [
        'to_user_id' => $targetUser->id,
        'amount' => 0,
    ]);

    $response->assertUnprocessable();

    $response->assertJsonValidationErrors(['amount']);
});

it('fails if the current user has insufficient balance', function () {
    $user = User::factory()->create();

    $targetUser = User::factory()->create();

    $user->wallet()->create(['balance' => 10]);

    $token = $user->createToken('auth_token')->plainTextToken;

    $response = withToken($token)->postJson(route('debit'), [
        'to_user_id' => $targetUser->id,
        'amount' => 20,
    ]);

    $response->assertBadRequest();

    $response->assertJson([
        'message' => __('Insufficient balance'),
    ]);
});
