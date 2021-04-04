<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBandMemberRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'role' => 'required|string',
        ];
    }

    public function getNewRole(): string
    {
        return $this->get('role');
    }
}
