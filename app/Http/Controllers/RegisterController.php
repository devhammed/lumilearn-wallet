<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Requests\RegisterRequest;

class RegisterController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(RegisterRequest $request): UserResource
    {
        $user = User::create($request->validated());

        $user->loadMissing('wallet');

        return UserResource::make($user);
    }
}
