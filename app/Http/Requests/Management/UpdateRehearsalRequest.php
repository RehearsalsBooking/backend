<?php

namespace App\Http\Requests\Management;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRehearsalRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'is_paid' => 'required|boolean',
        ];
    }

    /**
     * @return array
     */
    public function getStatusAttribute(): array
    {
        return ['is_paid' => $this->get('is_paid')];
    }
}
