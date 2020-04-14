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
            'from' => 'sometimes|date',
            'to' => 'sometimes|date',
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
}
