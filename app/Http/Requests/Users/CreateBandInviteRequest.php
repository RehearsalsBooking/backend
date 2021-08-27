<?php

namespace App\Http\Requests\Users;

use App\Models\Invite;
use Illuminate\Foundation\Http\FormRequest;

class CreateBandInviteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'role' => 'nullable|array',
        ];
    }

    public function inviteParams(): array
    {
        return [
            'email' => $this->get('email'),
            'roles' => $this->get('roles'),
            'status' => Invite::STATUS_PENDING,
        ];
    }

    public function getInvitedEmail(): string
    {
        return $this->get('email');
    }
}
