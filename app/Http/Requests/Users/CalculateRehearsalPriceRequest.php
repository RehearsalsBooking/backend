<?php

namespace App\Http\Requests\Users;

class CalculateRehearsalPriceRequest extends CreateRehearsalRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                'rehearsal_id' => 'bail|nullable|numeric|exists:rehearsals,id',
            ]
        );
    }

    public function id(): ?int
    {
        return $this->get('rehearsal_id');
    }

}
