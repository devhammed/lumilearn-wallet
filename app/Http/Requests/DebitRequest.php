<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class DebitRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'to_user_id' => [
                'required',
                'exists:users,id',
                Rule::notIn($this->user()->getKey()),
            ],
            'amount' => [
                'required',
                'numeric',
                'gt:0',
                'lte:10000',
            ],
        ];
    }

    /**
     * Get the target user.
     */
    public function toUser(): User
    {
        return User::findOrFail($this->integer('to_user_id'), 'id');
    }

    /**
     * Get the amount as a float.
     */
    public function amount(): float
    {
        return $this->float('amount');
    }
}
