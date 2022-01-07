<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PasswordRecoverRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ];
    }

    public function getCredentials(): array
    {
        return [
            'email' => $this->get('email'),
            'password' => $this->get('password'),
            'password_confirmation' => $this->get('password_confirmation'),
            'token' => $this->get('token')
        ];
    }
}
