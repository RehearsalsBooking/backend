<?php

namespace App\Filters;

class RehearsalsFilterRequest extends FilterRequest
{
    public $filters = [
        'from' => 'sometimes|date',
        'to' => 'sometimes|date|after:from'
    ];

    /**
     * @param string $date
     */
    protected function from(string $date): void
    {
        $this->builder->where('starts_at', '>=', $date);
    }

    /**
     * @param string $date
     */
    protected function to(string $date): void
    {
        $this->builder->where('starts_at', '<=', $date);
    }
}
