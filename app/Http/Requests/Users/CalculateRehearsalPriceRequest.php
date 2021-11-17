<?php

namespace App\Http\Requests\Users;

use App\Exceptions\User\InvalidRehearsalDurationException;
use App\Exceptions\User\PriceCalculationException;
use App\Models\Organization\OrganizationRoom;
use App\Models\Rehearsal;
use App\Models\RehearsalPrice;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class CalculateRehearsalPriceRequest extends FormRequest
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

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    /** @noinspection NullPointerExceptionInspection */
    public function getRoom(): OrganizationRoom
    {
        /** @phpstan-ignore-next-line */
        return $this->route()->parameter('room');
    }

    public function getReschedulingRehearsal(): ?Rehearsal
    {
        if (!$this->has('rehearsal_id')) {
            return null;
        }
        return Rehearsal::firstWhere('id', $this->get('rehearsal_id'));
    }
}
