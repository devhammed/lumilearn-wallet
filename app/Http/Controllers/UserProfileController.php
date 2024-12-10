<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;

class UserProfileController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): UserResource
    {
        $user = $request->user();

        $user->loadMissing('wallet');

        return UserResource::make($user);
    }
}
