<?php

namespace App\Http\Requests\Filters;

use Illuminate\Database\Eloquent\Builder;

abstract class RehearsalFilterRequest extends FilterRequest
{
    /**
     * @return array
     */
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

    /**
     * @return string
     */
    abstract protected function organizationRequirement(): string;

    /**
     * @param string $date
     */
    protected function from(string $date): void
    {
        $this->builder->where('starts_at', '>=', $date);
    }

    /**
     * @param int $organizationId
     */
    protected function organization_id(int $organizationId): void
    {
        $this->builder->where('organization_id', $organizationId);
    }

    /**
     * @param int $userId
     */
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

    /**
     * @param string $date
     */
    protected function to(string $date): void
    {
        $this->builder->where('starts_at', '<=', $date);
    }

    /**
     * @param int $bandId
     */
    protected function band_id(int $bandId): void
    {
        $this->builder->where('band_id', $bandId);
    }
}