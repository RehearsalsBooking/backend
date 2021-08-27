<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBandMemberRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'roles' => 'required|array',
        ];
    }

    public function getNewRoles(): array
    {
        return $this->get('roles');
    }
}
