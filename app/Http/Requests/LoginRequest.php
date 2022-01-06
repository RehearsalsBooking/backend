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
        ];
    }

    public function getCredentials(): array
    {
        return [
            'email' => $this->get('email'),
            'password' => $this->get('password')
        ];
    }
}
