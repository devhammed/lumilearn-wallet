<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\AuthManager;
use App\Http\Requests\UserLoginRequest;
use App\Http\Resources\PersonalAccessTokenResource;

class UserLoginController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UserLoginRequest $request, AuthManager $authManager): PersonalAccessTokenResource
    {
        $user = User::whereEmail($request->email)->first();

        if ( ! $user || ! $authManager->validate($request->validated())) {
            abort(401, __('These credentials do not match our records.'));
        }

        $deviceName = str($request->userAgent())
            ->substr(0, 255)
            ->toString();

        $token = $user->createToken($deviceName);

        return PersonalAccessTokenResource::make($token);
    }
}
