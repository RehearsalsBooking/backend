<?php

namespace App\Http\Requests\Users;

use App\Exceptions\User\InvalidRehearsalDurationException;
use App\Exceptions\User\PriceCalculationException;
use App\Models\Organization\Organization;
use App\Models\RehearsalPrice;
use Belamov\PostgresRange\Ranges\TimestampRange;
use Carbon\Carbon;
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
            'band_id' => 'bail|numeric|exists:bands,id',
            'organization_id' => 'bail|required|numeric|exists:organizations,id',
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
            'is_paid' => false,
            'band_id' => $this->get('band_id'),
            'organization_id' => $this->get('organization_id'),
            'price' => $rehearsalPrice(),
        ];
    }

    /**
     * @return Organization
     */
    public function organization(): Organization
    {
        /** @phpstan-ignore-next-line */
        return Organization::find($this->get('organization_id'));
    }
}
