<?php

namespace App\Http\Requests\Users;

use App\Models\Organization\OrganizationRoom;
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
            'organization_room_id' => 'bail|required|numeric|exists:organization_rooms,id',
        ];
    }

    public function getAttributes(): array
    {
        return [
            'time' => new TimestampRange(
                Carbon::parse($this->get('starts_at'))->setSeconds(0)->toDateTimeString(),
                Carbon::parse($this->get('ends_at'))->setSeconds(0)->toDateTimeString(),
            ),
            'user_id' => auth()->id(),
            'is_paid' => false,
            'band_id' => $this->get('band_id'),
            'organization_room_id' => $this->get('organization_room_id'),
        ];
    }

    public function room(): OrganizationRoom
    {
        /** @phpstan-ignore-next-line  */
        return OrganizationRoom::find($this->get('organization_room_id'));
    }
}
