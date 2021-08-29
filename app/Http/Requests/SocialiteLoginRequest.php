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
            ],
            'email' => [
                Rule::requiredIf(function () {
                    return $this->getProvider() === 'vkontakte';
                })
            ]
        ];
    }

    public function getToken(): string|array
    {
        // workaround for vkontakte driver, see https://github.com/SocialiteProviders/Providers/pull/216
        if ($this->getProvider() === 'vkontakte') {
            return [
                'email' => $this->get('email'),
                'access_token' => $this->get('token')
            ];
        }

        return $this->get('token');
    }

    public function getProvider(): string
    {
        return $this->get('provider');
    }
}
