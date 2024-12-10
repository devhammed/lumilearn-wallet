<?php

namespace App\Http\Requests;

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
}
