<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendPasswordRecoverLinkRequest extends FormRequest
{
    public function rules(): array
    {
        return [];
    }

    public function getEmail(): string
    {
        return $this->get('email');
    }
}
