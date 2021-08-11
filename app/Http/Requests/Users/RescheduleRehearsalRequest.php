<?php

namespace App\Http\Requests\Users;

use App\Exceptions\User\InvalidRehearsalDurationException;
use App\Exceptions\User\PriceCalculationException;
use App\Models\Rehearsal;
use App\Models\RehearsalPrice;
use Belamov\PostgresRange\Ranges\TimestampRange;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class RescheduleRehearsalRequest extends FormRequest
{
    private Rehearsal $rehearsal;

    /**
     * @noinspection PhpFieldAssignmentTypeMismatchInspection
     * @noinspection NullPointerExceptionInspection
     */
    public function authorize(): bool
    {
        /** @phpstan-ignore-next-line  */
        $this->rehearsal = $this->route()->parameter('rehearsal');

        return $this->user()->can('reschedule', $this->rehearsal);
    }

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
            'price' => $rehearsalPrice(),
        ];
    }
}
