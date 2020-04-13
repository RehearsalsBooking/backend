<?php

namespace App\Http\Requests\Management;

use App\Http\Requests\Filters\RehearsalFilterRequest;
use App\Models\Organization;

/**
 * Class RehearsalsFilterRequest
 * {@inheritdoc}
 */
class RehearsalsFilterManagementRequest extends RehearsalFilterRequest
{
    /**
     * @return Organization|null
     */
    public function organization(): ?Organization
    {
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
