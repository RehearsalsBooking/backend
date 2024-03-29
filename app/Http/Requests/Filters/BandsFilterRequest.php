<?php

namespace App\Http\Requests\Filters;

use Illuminate\Database\Eloquent\Builder;

class BandsFilterRequest extends FilterRequest
{
    protected function getRules(): array
    {
        return [
            'active_member_id' => 'sometimes|integer',
            'inactive_member_id' => 'sometimes|integer',
            'only_managed' => 'sometimes|boolean'
        ];
    }

    protected function active_member_id(int $memberId): void
    {
        $this->builder->whereHas(
            'memberships',
            fn(Builder $query) => $query->where('user_id', $memberId)
        );
    }

    protected function inactive_member_id(int $memberId): void
    {
        $this->builder->whereHas(
            'memberships',
            /** @phpstan-ignore-next-line */
            fn(Builder $query) => $query->onlyTrashed()->where('user_id', $memberId)
        );
    }

    protected function only_managed(bool $onlyManaged): void
    {
        if ($onlyManaged) {
            $this->builder->where('admin_id', auth()->id());
        }
    }
}
