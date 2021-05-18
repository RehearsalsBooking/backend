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
            'role' => 'nullable|string',
        ];
    }

    public function inviteParams(): array
    {
        return [
            'email' => $this->get('email'),
            'role' => $this->get('role'),
            'status' => Invite::STATUS_SENT,
        ];
    }

    public function getInvitedEmail(): string
    {
        return $this->get('email');
    }
}
