<?php

namespace Tests\Feature\Organizations;

use App\Http\Resources\Users\OrganizationResource;
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
        $fetchedOrganizationsIds = collect($data)->pluck('id')->toArray();
        $this->assertEquals(
            $fetchedOrganizationsIds,
            $favoritedOrganizations->pluck('id')->toArray()
        );
    }

    /** @test */
    public function users_can_filter_organizations_by_available_time(): void
    {
        $this->withoutExceptionHandling();
        $organizationWithRehearsal9to11 = $this->createOrganization();
        $organizationWithRehearsal14to16 = $this->createOrganization();

        $this->createRehearsal(
            9,
            11,
            $organizationWithRehearsal9to11
        );
        $this->createRehearsal(
            14,
            16,
            $organizationWithRehearsal14to16
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
            OrganizationResource::collection(collect([$organizationWithRehearsal9to11]))->response()->getData(true),
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
        $this->assertCount(2, $data['data']);
        $this->assertEquals(
            OrganizationResource::collection(collect([
                $organizationWithRehearsal9to11, $organizationWithRehearsal14to16,
            ]))->response()->getData(true),
            $data
        );

        $response = $this->json('get', route('organizations.list'), [
            'from' => $this->getDateTimeAt(16, 00),
        ]);

        $response->assertOk();
        $data = $response->json();
        // 9----11
        //             14----16
        //                   16------------
        $this->assertCount(2, $data['data']);
        $this->assertEquals(
            OrganizationResource::collection(collect([
                $organizationWithRehearsal9to11, $organizationWithRehearsal14to16,
            ]))->response()->getData(true),
            $data
        );

        $response = $this->json('get', route('organizations.list'), [
            'to' => $this->getDateTimeAt(12, 00),
        ]);

        $response->assertOk();
        $data = $response->json();
        // 9----11
        //             14----16
        // --------12
        $this->assertCount(1, $data['data']);
        $this->assertEquals(
            OrganizationResource::collection(collect([$organizationWithRehearsal14to16]))->response()->getData(true),
            $data
        );

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
        $this->assertEquals(
            OrganizationResource::collection(collect([
                $organizationWithRehearsal9to11, $organizationWithRehearsal14to16,
            ]))->response()->getData(true),
            $data
        );

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
        $this->assertEquals(
            OrganizationResource::collection(collect([
                $organizationWithRehearsal9to11, $organizationWithRehearsal14to16,
            ]))->response()->getData(true),
            $data
        );

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
        $this->assertEquals(
            OrganizationResource::collection(collect([
                $organizationWithRehearsal9to11,
            ]))->response()->getData(true),
            $data
        );

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
