<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserEmailRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|string|email',
            'code' => 'required',
        ];
    }

    public function getNewEmail(): string
    {
        return $this->get('email');
    }

    public function getEmailConfirmationCode(): array
    {
        return [
            'email' => $this->get('email'),
            'code' => $this->get('code')
        ];
    }
}
