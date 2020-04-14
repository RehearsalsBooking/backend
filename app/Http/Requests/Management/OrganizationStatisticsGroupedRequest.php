<?php

namespace App\Http\Requests\Management;

use Illuminate\Validation\Rule;

class OrganizationStatisticsGroupedRequest extends OrganizationStatisticsRequest
{
    protected array $availableIntervals = ['day', 'month', 'year'];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'interval' => [
                'required',
                Rule::in($this->availableIntervals),
            ],
        ]);
    }

    /**
     * @return string
     */
    public function groupInterval(): string
    {
        return $this->get('interval');
    }
}
