<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class RegistrationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'code' => 'required'
        ];
    }

    public function getUserAttributes(): array
    {
        return [
            'name' => $this->get('name'),
            'email' => $this->get('email'),
            'password' => Hash::make($this->get('password')),
        ];
    }

    public function getEmailConfirmationCode(): array
    {
        return [
            'email' => $this->get('email'),
            'code' => $this->get('code')
        ];
    }
}
