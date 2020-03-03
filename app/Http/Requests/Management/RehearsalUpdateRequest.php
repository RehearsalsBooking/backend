<?php

namespace App\Http\Requests\Management;

use Illuminate\Foundation\Http\FormRequest;

class RehearsalUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->user()->can('managementUpdateStatus', $this->route()->parameter('rehearsal'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'is_confirmed' => 'required|boolean'
        ];
    }

    /**
     * @return array
     */
    public function getStatusAttribute(): array
    {
        return ['is_confirmed' => $this->get('is_confirmed')];
    }
}
