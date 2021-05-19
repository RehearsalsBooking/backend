<?php

namespace App\Http\Requests\Filters;

use App\Models\Invite;
use Illuminate\Validation\Rule;

class InvitesFilterRequest extends FilterRequest
{
    protected function getRules(): array
    {
        return [
            'status' => [
                'sometimes',
                'nullable',
                'array',
            ],
            'status.*' => [
                Rule::in([Invite::STATUS_ACCEPTED, Invite::STATUS_REJECTED, Invite::STATUS_PENDING]),
            ],
        ];
    }

    protected function status(array $statuses): void
    {
        $this->builder->whereIn('status', $statuses['*']);
    }
}
