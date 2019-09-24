<?php

namespace App\Http\Requests\Users;

use App\Models\Organization;
use App\Rules\User\AfterTimeWhenOrganizationIsOpened;
use App\Rules\User\BeforeTimeWhenOrganizationIsClosed;
use Illuminate\Foundation\Http\FormRequest;

class CreateRehearsalRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        /**
         * @var $organization Organization
         */
        $organization = $this->route()->parameter('organization');

        return [
            'starts_at' => [
                'bail',
                'required',
                'date',
                'after:now',
                new AfterTimeWhenOrganizationIsOpened($organization)
            ],
            'ends_at' => [
                'bail',
                'required',
                'date',
                'after:starts_at',
                new BeforeTimeWhenOrganizationIsClosed($organization)
            ]
        ];
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return [
            'starts_at' => $this->get('starts_at'),
            'ends_at' => $this->get('ends_at'),
            'user_id' => auth()->id(),
        ];
    }
}
