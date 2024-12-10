<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthManager;
use App\Http\Resources\UserResource;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Resources\PersonalAccessTokenResource;

class UserController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(UserRegisterRequest $request): UserResource
    {
        $user = User::create($request->validated());

        $user->loadMissing('wallet');

        return UserResource::make($user);
    }

    /**
     * Login the user.
     */
    public function login(UserLoginRequest $request, AuthManager $authManager): PersonalAccessTokenResource
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

    /**
     * Get the authenticated user.
     */
    public function user(Request $request): UserResource
    {
        $user = $request->user();

        $user->loadMissing('wallet');

        return UserResource::make($user);
    }
}
