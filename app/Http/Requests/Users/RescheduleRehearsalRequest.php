<?php

namespace App\Http\Requests\Users;

use App\Exceptions\User\InvalidRehearsalDurationException;
use App\Exceptions\User\PriceCalculationException;
use App\Models\RehearsalPrice;
use App\Models\TimestampRange;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class RescheduleRehearsalRequest extends FormRequest
{
    /**
     * @var object|string
     */
    private $rehearsal;

    /**
     * @return bool
     */
    public function authorize(): bool
    {
        $this->rehearsal = $this->route()->parameter('rehearsal');

        return auth()->user()->can('reschedule', $this->rehearsal);
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
     * @throws PriceCalculationException
     * @throws InvalidRehearsalDurationException
     */
    public function getRehearsalAttributes(): array
    {
        $rehearsalPrice = new RehearsalPrice(
            $this->rehearsal->organization_id,
            Carbon::parse($this->get('starts_at')),
            Carbon::parse($this->get('ends_at'))
        );

        return [
            'time' => new TimestampRange(
                Carbon::parse($this->get('starts_at'))->setSeconds(0)->toDateTimeString(),
                Carbon::parse($this->get('ends_at'))->setSeconds(0)->toDateTimeString()
            ),
            'user_id' => auth()->id(),
            'is_confirmed' => false,
            'price' => $rehearsalPrice()
        ];
    }
}
