<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBandMembersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // see BandPolicy for rules
        return auth()->user()->can('update-members', [$this->route()->parameter('band')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'users' => 'present|array',
            'users.*' => 'numeric|exists:users,id'
        ];
    }

    public function messages(): array
    {
        return [
            'users.*.exists' => 'user not found',
            'users.*.numeric' => 'user not found'
        ];
    }

    public function membersIds()
    {
        return $this->get('users');
    }
}
