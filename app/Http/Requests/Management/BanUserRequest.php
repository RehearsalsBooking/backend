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
    public function rules(): array
    {
        return [
            'user_id' => 'required|numeric|exists:users,id',
            'comment' => 'nullable|string',
        ];
    }

    /**
     * @return array
     */
    public function organizationUserBan(): array
    {
        /** @noinspection NullPointerExceptionInspection */
        return [
            /** @phpstan-ignore-next-line  */
            'organization_id' => $this->route()->originalParameter('organization'),
            'user_id' => $this->get('user_id'),
            'comment' => $this->get('comment'),
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
