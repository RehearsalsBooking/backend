<?php

namespace Tests\Feature\Organizations;

use App\Http\Resources\Users\OrganizationResource;
use App\Models\Organization\Organization;
use Tests\TestCase;

class OrganizationsFiltrationTest extends TestCase
{
    /** @test */
    public function users_can_filter_organizations_by_name(): void
    {
        $organizationsNames = ['foo', 'bar', 'foobar', 'barfoo', 'xfoobarx'];
        $organizationsNamesWithFoo = ['foo', 'foobar', 'barfoo', 'xfoobarx'];

        foreach ($organizationsNames as $organizationName) {
            $this->createOrganization(['name' => $organizationName]);
        }

        $this->assertEquals(count($organizationsNames), Organization::count());

        $response = $this->json('get', route('organizations.list'), ['name' => 'foo']);

        $response->assertStatus(200);

        $data = $response->json('data');

        $this->assertCount(count($organizationsNamesWithFoo), $data);

        $fetchedOrganizationsNames = collect($data)->pluck('name')->toArray();
        sort($fetchedOrganizationsNames);
        sort($organizationsNamesWithFoo);
        $this->assertEquals(
            $fetchedOrganizationsNames,
            $organizationsNamesWithFoo
        );
    }

    /** @test */
    public function users_can_filter_organizations_by_favorite(): void
    {
        $favoritedOrganizations = $this->createOrganizations(3);
        $this->createOrganizations(3);

        $user = $this->createUser();

        $user->favoriteOrganizations()->sync($favoritedOrganizations);

        $response = $this->json('get', route('organizations.list'), ['favorite' => true]);

        $this->assertCount(6, $response->json('data'));

        $this->actingAs($user);

        $response = $this->json('get', route('organizations.list'), ['favorite' => true]);

        $response->assertOk();

        $data = $response->json('data');

        $this->assertCount($favoritedOrganizations->count(), $data);
        $fetchedOrganizationsIds = collect($data)->sortBy('id')->pluck('id')->toArray();
        $this->assertEquals(
            $fetchedOrganizationsIds,
            $favoritedOrganizations->sortBy('id')->pluck('id')->toArray()
        );
    }

    /** @test */
    public function users_can_filter_organizations_by_city(): void
    {
        $tyumen = $this->createCity();
        $moscow = $this->createCity();

        $orgInTyumen = $this->createOrganization(['city_id' => $tyumen->id]);
        $this->createOrganization(['city_id' => $moscow->id]);

        $this->assertEquals(2, Organization::count());

        $response = $this->json('get', route('organizations.list'), ['city_id' => $tyumen->id]);
        $response->assertOk();

        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($orgInTyumen->id, $response->json('data.0.id'));
    }

    /** @test */
    public function users_can_filter_organizations_by_available_time(): void
    {
        $roomWithRehearsal9to11 = $this->createOrganizationRoom();
        $roomWithRehearsal14to16 = $this->createOrganizationRoom();

        $this->createRehearsal(
            9,
            11,
            $roomWithRehearsal9to11
        );
        $this->createRehearsal(
            14,
            16,
            $roomWithRehearsal14to16
        );

        $response = $this->json('get', route('organizations.list'), [
            'from' => $this->getDateTimeAt(13, 00),
        ]);

        $response->assertOk();

        $data = $response->json();
        // 9----11
        //             14----16
        //         13------------
        $this->assertCount(1, $data['data']);
        $this->assertEquals(
            OrganizationResource::collection(collect([$roomWithRehearsal9to11->organization]))->response()->getData(true),
            $data
        );

        $response = $this->json('get', route('organizations.list'), [
            'from' => $this->getDateTimeAt(10, 00),
        ]);

        $response->assertOk();
        $data = $response->json();
        // 9----11
        //             14----16
        //   10-----------------
        $this->assertCount(0, $data['data']);

        $response = $this->json('get', route('organizations.list'), [
            'from' => $this->getDateTimeAt(17, 00),
        ]);

        $response->assertOk();
        $data = $response->json();
        // 9----11
        //             14----16
        //                       17------------
        $this->assertDatabaseCount(Organization::class, 2);
        $this->assertCount(2, $data['data']);

        $response = $this->json('get', route('organizations.list'), [
            'from' => $this->getDateTimeAt(16, 00),
        ]);

        $response->assertOk();
        $data = $response->json();
        // 9----11
        //             14----16
        //                   16------------
        $this->assertCount(2, $data['data']);
        $this->assertDatabaseCount(Organization::class, 2);


        $response = $this->json('get', route('organizations.list'), [
            'to' => $this->getDateTimeAt(12, 00),
        ]);

        $response->assertOk();
        $data = $response->json();
        // 9----11
        //             14----16
        // --------12
        $this->assertCount(1, $data['data']);
        $this->assertEquals($roomWithRehearsal14to16->organization->id, $data['data'][0]['id']);

        $response = $this->json('get', route('organizations.list'), [
            'to' => $this->getDateTimeAt(15, 00),
        ]);

        $response->assertOk();
        $data = $response->json();
        // 9----11
        //             14----16
        // ---------------15
        $this->assertCount(0, $data['data']);

        $response = $this->json('get', route('organizations.list'), [
            'to' => $this->getDateTimeAt(9, 00),
        ]);

        $response->assertOk();
        $data = $response->json();
        //          9----11
        //                      14----16
        // ---------9
        $this->assertCount(2, $data['data']);
        $this->assertDatabaseCount(Organization::class, 2);

        $response = $this->json('get', route('organizations.list'), [
            'from' => $this->getDateTimeAt(12, 00),
            'to' => $this->getDateTimeAt(14, 00),
        ]);

        $response->assertOk();
        $data = $response->json();
        // 9----11
        //             14----16
        //         12--14
        $this->assertCount(2, $data['data']);
        $this->assertDatabaseCount(Organization::class, 2);

        $response = $this->json('get', route('organizations.list'), [
            'from' => $this->getDateTimeAt(12, 00),
            'to' => $this->getDateTimeAt(17, 00),
        ]);

        $response->assertOk();
        $data = $response->json();
        // 9----11
        //             14----16
        //         12-----------17
        $this->assertCount(1, $data['data']);
        $this->assertEquals($roomWithRehearsal9to11->organization->id, $data['data'][0]['id']);


        $response = $this->json('get', route('organizations.list'), [
            'from' => $this->getDateTimeAt(10, 00),
            'to' => $this->getDateTimeAt(15, 00),
        ]);

        $response->assertOk();
        $data = $response->json();
        // 9----11
        //             14----16
        //   10-----------15
        $this->assertCount(0, $data['data']);
    }
}
