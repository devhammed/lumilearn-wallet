<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Requests\UserRegisterRequest;

class UserRegisterController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UserRegisterRequest $request): UserResource
    {
        $user = User::create($request->validated());

        $user->loadMissing('wallet');

        return UserResource::make($user);
    }
}
