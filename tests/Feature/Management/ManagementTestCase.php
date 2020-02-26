<?php

namespace Tests\Feature\Management;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagementTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $manager;
    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = $this->createUser();
        $this->organization = $this->createOrganizationForUser($this->manager);
        $this->createOrganizationForUser($this->manager);
    }
}
