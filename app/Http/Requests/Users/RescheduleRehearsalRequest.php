<?php

namespace App\Http\Requests\Users;

use App\Models\Rehearsal;
use App\Models\RehearsalDataProvider;
use Belamov\PostgresRange\Ranges\TimestampRange;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class RescheduleRehearsalRequest extends FormRequest implements RehearsalDataProvider
{
    private Rehearsal $rehearsal;

    /**
     * @noinspection PhpFieldAssignmentTypeMismatchInspection
     * @noinspection NullPointerExceptionInspection
     */
    public function authorize(): bool
    {
        /** @phpstan-ignore-next-line */
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

    public function getRehearsalAttributes(): array
    {
        return [
            'time' => $this->time(),
            'user_id' => $this->bookedUserId(),
        ];
    }

    public function id(): ?int
    {
        return $this->rehearsal->id;
    }

    public function time(): TimestampRange
    {
        return new TimestampRange(
            Carbon::parse($this->get('starts_at'))->setSeconds(0)->toDateTimeString(),
            Carbon::parse($this->get('ends_at'))->setSeconds(0)->toDateTimeString()
        );
    }

    public function bandId(): ?int
    {
        return $this->rehearsal->band_id;
    }

    public function bookedUserId(): ?int
    {
        return (int) auth()->id();
    }

    public function roomId(): int
    {
        return $this->rehearsal->organization_room_id;
    }
}
