<?php

namespace App\Http\Requests\Users;

use App\Models\RehearsalDataProvider;
use Belamov\PostgresRange\Ranges\TimestampRange;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class CreateRehearsalRequest extends FormRequest implements RehearsalDataProvider
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
            'organization_room_id' => 'bail|required|numeric|exists:organization_rooms,id',
        ];
    }

    public function getAttributes(): array
    {
        return [
            'time' => $this->time(),
            'user_id' => $this->bookedUserId(),
            'is_paid' => false,
            'band_id' => $this->bandId(),
            'organization_room_id' => $this->roomId(),
        ];
    }

    public function id(): ?int
    {
        return null;
    }

    public function time(): TimestampRange
    {
        return new TimestampRange(
            Carbon::parse($this->get('starts_at'))->setSeconds(0)->toDateTimeString(),
            Carbon::parse($this->get('ends_at'))->setSeconds(0)->toDateTimeString(),
        );
    }

    public function bandId(): ?int
    {
        return $this->get('band_id');
    }

    public function bookedUserId(): ?int
    {
        return (int) auth()->id();
    }

    public function roomId(): int
    {
        return $this->get('organization_room_id');
    }
}
