<?php

namespace App\Http\Requests\Users;

use App\Models\Band;
use App\Models\Organization;
use App\Models\Rehearsal;
use App\Rules\User\AfterTimeWhenOrganizationIsOpened;
use App\Rules\User\BeforeTimeWhenOrganizationIsClosed;
use Illuminate\Foundation\Http\FormRequest;

class CreateRehearsalRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        $bandId = $this->get('band_id');

        // if we dont have band id parameter,
        // then user books rehearsal on behalf of himself,
        // which is always allowed
        if (!$bandId) {
            return true;
        }

        //laravel runs authorization before validation,
        //so we have to check band existence manually
        if (Band::where('id', $bandId)->doesntExist()) {
            // if band doesnt exist, just return true,
            // request will fail at validation
            // TODO: remove duplicated query (custom form request? move this logic into controller? https://github.com/laravel/framework/issues/27808)
            return true;
        }

        // if we have band id parameter, then its booking rehearsal
        // on behalf of a band. we need to ensure that user
        // who books rehearsal on behalf of a band can do it
        // logic for that check is contained in rehearsal policy
        return auth()->user()->can('createOnBehalfOfBand', [Rehearsal::class, Band::find($bandId)]);
    }

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
            ],
            'band_id' => [
                'exists:bands,id'
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
            'is_confirmed' => false,
            'band_id' => $this->get('band_id')
        ];
    }
}
