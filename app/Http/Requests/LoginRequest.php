<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required'],
            'remember_me' => ['nullable', 'boolean']
        ];
    }

    public function getCredentials(): array
    {
        return [
            'email' => $this->get('email'),
            'password' => $this->get('password')
        ];
    }

    public function doRemember(): bool
    {
        return (bool) $this->get('remember_me', false);
    }
}
