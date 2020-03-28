<?php

namespace App\Http\Requests\Users;

use App\Http\Requests\Filters\RehearsalFilterRequest;

class RehearsalsFilterClientRequest extends RehearsalFilterRequest
{
    /**
     * @return string
     */
    protected function organizationRequirement(): string
    {
        return 'sometimes';
    }
}
