<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SocialiteLoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'token' => 'required|string',
            'provider' => [
                'required',
                'string',
                Rule::in(['google', 'vkontakte'])
            ]
        ];
    }

    public function getToken(): string
    {
        return $this->get('token');
    }

    public function getProvider(): string
    {
        return $this->get('provider');
    }
}
