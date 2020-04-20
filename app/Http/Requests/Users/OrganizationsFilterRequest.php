<?php

namespace App\Http\Requests\Users;

use App\Http\Requests\Filters\FilterRequest;

class OrganizationsFilterRequest extends FilterRequest
{
    /**
     * @return array
     */
    protected function getRules(): array
    {
        return [
            'from' => 'sometimes|date',
            'to' => 'sometimes|date|after:from',
            'name' => 'sometimes|string',
            'favorite' => 'sometimes|bool',
        ];
    }

    /**
     * @return array
     */
    protected function getFilters(): array
    {
        return array_merge(
            parent::getFilters(),
            [
                'available_time' => [
                    $this->request->get('from'),
                    $this->request->get('to'),
                ],
            ]
        );
    }

    /**
     * @param $boundaries
     */
    protected function available_time($boundaries): void
    {
        //TODO
//        [$from, $to] = $boundaries;
//
//        $range = new TimestampRange($from, $to, '[', ']');
//
//        $this->builder->whereNotRaw('time <@ ?::tsrange', [$range]);
    }

    /**
     * @param  string  $name
     */
    protected function name(string $name): void
    {
        $this->builder->where('name', 'like', "%$name%");
    }
}
