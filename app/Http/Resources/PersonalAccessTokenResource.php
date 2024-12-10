<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Laravel\Sanctum\NewAccessToken;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin NewAccessToken
 */
class PersonalAccessTokenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->accessToken->id,
            'tokenable_id' => $this->accessToken->tokenable_id,
            'tokenable_type' => $this->accessToken->tokenable_type,
            'name' => $this->accessToken->name,
            'token' => $this->plainTextToken,
            'abilities' => $this->accessToken->abilities,
            'expires_at' => $this->accessToken->expires_at,
        ];
    }
}
