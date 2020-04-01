<?php

namespace App\Http\Requests\Management;

use Illuminate\Foundation\Http\FormRequest;

class BanUserRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => 'required|numeric|exists:users,id',
            'comment' => 'nullable|string'
        ];
    }

    /**
     * @return array
     */
    public function organizationUserBan(): array
    {
        return [
            'organization_id' => $this->route()->originalParameter('organization'),
            'user_id' => $this->get('user_id'),
            'comment' => $this->get('comment')
        ];
    }

    /**
     * @return int
     */
    public function bannedUserId(): int
    {
        return $this->get('user_id');
    }
}
