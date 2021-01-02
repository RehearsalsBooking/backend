<?php

namespace App\Http\Requests\Management;

use App\Http\Requests\Filters\RehearsalsFilterRequest;
use App\Models\Organization\Organization;

/**
 * Class RehearsalsFilterRequest
 */
class RehearsalsFilterManagementRequest extends RehearsalsFilterRequest
{
    /**
     * @return Organization|null
     */
    public function organization(): ?Organization
    {
        /** @phpstan-ignore-next-line  */
        return Organization::find($this->request->get('organization_id'));
    }

    /**
     * @return string
     */
    protected function organizationRequirement(): string
    {
        return 'required';
    }
}
