<?php

namespace App\Http\Requests\Filters;

class RehearsalsFilterClientRequest extends RehearsalsFilterRequest
{
    /**
     * @return string
     */
    protected function organizationRequirement(): string
    {
        return 'sometimes';
    }
}
