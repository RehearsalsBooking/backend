<?php

namespace Tests\Feature\Management;

use App\Models\Organization\Organization;
use App\Models\Organization\OrganizationRoom;
use App\Models\Organization\OrganizationRoomPrice;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

/**
 * Class ManagementTestCase.
 *
 * @property User $manager
 * @property Organization $organization
 */
class ManagementTestCase extends TestCase
{
    protected User $manager;
    protected Organization $organization;
    protected OrganizationRoom $organizationRoom;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = $this->createUser();
        $this->organization = $this->createOrganizationForUser($this->manager);
        $this->organizationRoom = $this->createOrganizationRoom($this->organization);
        $this->createPricesForOrganization($this->organization);
        $this->createOrganizationForUser($this->manager);

        $this->organization->prices()->whereIn('day', [5, 6])->delete();
    }
}
