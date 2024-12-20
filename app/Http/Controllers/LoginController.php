<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\LoginRequest;
use Illuminate\Contracts\Auth\Guard;
use App\Http\Resources\PersonalAccessTokenResource;

class LoginController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(LoginRequest $request, Guard $guard): PersonalAccessTokenResource
    {
        $user = User::whereEmail($request->email)->first();

        if ( ! $user || ! $guard->validate($request->validated())) {
            abort(401, __('These credentials do not match our records.'));
        }

        $deviceName = str($request->userAgent())
            ->substr(0, 255)
            ->toString();

        $token = $user->createToken($deviceName);

        return PersonalAccessTokenResource::make($token);
    }
}
