<?php

namespace App\Http\Requests\Users;

use App\Exceptions\User\InvalidRehearsalDurationException;
use App\Exceptions\User\PriceCalculationException;
use App\Models\Band;
use App\Models\Organization;
use App\Models\Ranges\TimestampRange;
use App\Models\Rehearsal;
use App\Models\RehearsalPrice;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class CreateRehearsalRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        if (!$this->onBehalfOfTheBand()) {
            return true;
        }

        $bandId = $this->get('band_id');

        //laravel runs authorization before validation,
        //so we have to check band existence manually
        //
        // if band doesnt exist, just return true,
        // request must fail at validation
        // TODO: remove duplicated query (custom form request? move this logic into controller? https://github.com/laravel/framework/issues/27808)
        if (Band::where('id', $bandId)->doesntExist()) {
            return true;
        }

        // if we have band id parameter, then its booking rehearsal
        // on behalf of a band. we need to ensure that user
        // who books rehearsal on behalf of a band can do it
        // logic for that check is contained in rehearsal policy
        return auth()->user()->can('createOnBehalfOfBand', [Rehearsal::class, Band::find($bandId)]);
    }

    /**
     * Determines if request is on behalf of the band
     *
     * @return bool
     */
    public function onBehalfOfTheBand(): bool
    {
        return $this->has('band_id');
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
            'band_id' => 'bail|numeric|exists:bands,id',
            'organization_id' => 'bail|required|numeric|exists:organizations,id'
        ];
    }

    /**
     * @return array
     * @throws PriceCalculationException
     * @throws InvalidRehearsalDurationException
     */
    public function getAttributes(): array
    {
        $rehearsalPrice = new RehearsalPrice(
            $this->get('organization_id'),
            Carbon::parse($this->get('starts_at'))->setSeconds(0),
            Carbon::parse($this->get('ends_at'))->setSeconds(0)
        );

        return [
            'time' => new TimestampRange(
                Carbon::parse($this->get('starts_at'))->setSeconds(0)->toDateTimeString(),
                Carbon::parse($this->get('ends_at'))->setSeconds(0)->toDateTimeString(),
            ),
            'user_id' => auth()->id(),
            'is_confirmed' => false,
            'band_id' => $this->get('band_id'),
            'organization_id' => $this->get('organization_id'),
            'price' => $rehearsalPrice()
        ];
    }

    /**
     * @return Organization
     */
    public function organization(): Organization
    {
        return Organization::find($this->get('organization_id'));
    }
}
