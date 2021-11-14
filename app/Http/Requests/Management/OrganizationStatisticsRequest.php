<?php

namespace App\Http\Requests\Management;

use Belamov\PostgresRange\Ranges\DateRange;
use Illuminate\Foundation\Http\FormRequest;

class OrganizationStatisticsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'room_id' => 'nullable|integer|exists:organization_rooms,id'
        ];
    }

    /**
     * @return DateRange|null
     */
    public function interval(): ?DateRange
    {
        if ($this->has('from') || $this->has('to')) {
            return new DateRange($this->get('from'), $this->get('to'));
        }

        return null;
    }

    public function roomId(): ?int
    {
        if ($this->has('room_id')) {
            return (int) $this->get('room_id');
        }

        return null;
    }
}
