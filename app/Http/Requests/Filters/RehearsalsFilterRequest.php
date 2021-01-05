<?php

namespace App\Http\Requests\Filters;

use Belamov\PostgresRange\Ranges\TimestampRange;
use Illuminate\Database\Eloquent\Builder;

abstract class RehearsalsFilterRequest extends FilterRequest
{
    protected function getRules(): array
    {
        return [
            'from' => 'sometimes|date',
            'to' => 'sometimes|date|after:from',
            'organization_id' => "{$this->organizationRequirement()}|numeric|exists:organizations,id",
            'user_id' => 'sometimes|numeric|exists:users,id',
            'band_id' => 'sometimes|numeric|exists:bands,id',
        ];
    }

    abstract protected function organizationRequirement(): string;

    protected function getFilters(): array
    {
        $filters = parent::getFilters();

        if ($this->filteringByTime()) {
            return array_merge(
                $filters,
                [
                    'time' => [
                        $this->request->get('from'),
                        $this->request->get('to'),
                    ],
                ]
            );
        }

        return $filters;
    }

    protected function filteringByTime(): bool
    {
        return $this->request->has('from') || $this->request->has('to');
    }

    protected function time(array $boundaries): void
    {
        [$from, $to] = $boundaries;

        $range = new TimestampRange($from, $to, '[', ']');

        $this->builder->whereRaw('time <@ ?::tsrange', [$range]);
    }

    protected function organization_id(int $organizationId): void
    {
        $this->builder->where('organization_id', $organizationId);
    }

    protected function user_id(int $userId): void
    {
        // TODO: replace whereHas to whereIn(band_id and subquery) for better performance?
        // select *
        // from "rehearsals"
        // where "user_id" = ?
        //   or exists(select *
        //             from "bands"
        //             where "rehearsals"."band_id" = "bands"."id"
        //               and exists(select *
        //                          from "users"
        //                                   inner join "band_user" on "users"."id" = "band_user"."user_id"
        //                          where "bands"."id" = "band_user"."band_id"
        //                            and "id" = ?)
        //               and "bands"."deleted_at" is null)
        $this->builder
            ->where('user_id', $userId)
            ->orwhereHas(
                'band.members',
                fn (Builder $query) => $query->where('id', $userId)
            );
    }

    protected function band_id(int $bandId): void
    {
        $this->builder->where('band_id', $bandId);
    }
}