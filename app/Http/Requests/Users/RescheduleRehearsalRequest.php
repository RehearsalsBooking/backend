<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class RescheduleRehearsalRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        $rehearsal = $this->route()->parameter('rehearsal');

        return auth()->user()->can('reschedule', $rehearsal);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'starts_at' => [
                'bail',
                'required',
                'date',
                'after:now'
            ],
            'ends_at' => [
                'bail',
                'required',
                'date',
                'after:starts_at'
            ],
        ];
    }

    /**
     * @return array
     */
    public function getRehearsalAttributes(): array
    {
        return [
            'starts_at' => $this->get('starts_at'),
            'ends_at' => $this->get('ends_at'),
            'user_id' => auth()->id(),
            'is_confirmed' => false,
        ];
    }
}
