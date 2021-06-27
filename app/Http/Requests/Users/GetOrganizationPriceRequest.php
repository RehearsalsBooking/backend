<?php

namespace App\Http\Requests\Users;

use App\Exceptions\User\InvalidRehearsalDurationException;
use App\Exceptions\User\PriceCalculationException;
use App\Models\Organization\Organization;
use App\Models\Rehearsal;
use App\Models\RehearsalPrice;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class GetOrganizationPriceRequest extends FormRequest
{
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
                'after:now',
            ],
            'ends_at' => [
                'bail',
                'required',
                'date',
                'after:starts_at',
            ],
        ];
    }

    /**
     * @return float
     * @throws InvalidRehearsalDurationException
     * @throws PriceCalculationException
     */
    public function getRehearsalPrice(): float
    {
        $rehearsalPrice = new RehearsalPrice(
            $this->getOrganization()->id,
            Carbon::parse($this->get('starts_at'))->setSeconds(0),
            Carbon::parse($this->get('ends_at'))->setSeconds(0)
        );

        return $rehearsalPrice();
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */

    /** @noinspection NullPointerExceptionInspection */
    public function getOrganization(): Organization
    {
        /** @phpstan-ignore-next-line */
        return $this->route()->parameter('organization');
    }

    public function getReschedulingRehearsal(): ?Rehearsal
    {
        if (!$this->has('rehearsal_id')) {
            return null;
        }
        return Rehearsal::firstWhere('id', $this->get('rehearsal_id'));
    }
}
