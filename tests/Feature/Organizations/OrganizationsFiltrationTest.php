<?php

namespace Tests\Feature\Organizations;

use App\Models\Organization\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationsFiltrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function users_can_filter_organizations_by_name(): void
    {
        $organizationsNames = ['foo', 'bar', 'foobar', 'barfoo', 'xfoobarx'];
        $organizationsNamesWithFoo = ['foo', 'foobar', 'barfoo', 'xfoobarx'];

        foreach ($organizationsNames as $organizationName) {
            factory(Organization::class)->create(['name' => $organizationName]);
        }

        $this->assertEquals(count($organizationsNames), Organization::count());

        $response = $this->json('get', route('organizations.list'), ['name' => 'foo']);

        $response->assertStatus(200);

        $data = $response->json('data');

        $this->assertCount(count($organizationsNamesWithFoo), $data);
        $fetchedOrganizationsNames = collect($data)->pluck('name')->toArray();
        $this->assertEquals(
            $fetchedOrganizationsNames,
            $organizationsNamesWithFoo
        );
    }
}
