<?php

namespace App\Http\Requests\Filters;

use Illuminate\Database\Eloquent\Builder;

class BandsFilterRequest extends FilterRequest
{
    /**
     * @return array
     */
    protected function getRules(): array
    {
        return [
            'member_id' => 'sometimes|integer',
        ];
    }

    /**
     * @param  int  $memberId
     */
    protected function member_id(int $memberId): void
    {
        $this->builder->whereHas(
            'members',
            fn(Builder $query) => $query->where('user_id', $memberId)
        );
    }
}
