<?php

namespace Tests\Feature\Bands;

use App\Http\Resources\Users\UserResource;
use App\Models\Band;
use App\Models\User;
use Illuminate\Http\Response;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class BandsMembersInviteTest
 * @package Tests\Feature\Bands
 * @property User $bandAdmin
 * @property Band $band
 */
class BandsMembersUpdateTest extends TestCase
{
    use RefreshDatabase;

    private $bandAdmin;
    private $band;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bandAdmin = $this->createUser();
        $this->band = $this->createBandForUser($this->bandAdmin);

    }

    /** @test */
    public function admin_of_band_can_update_members_of_his_band(): void
    {
        $this->actingAs($this->bandAdmin);

        $firstMember = $this->createUser();

        $this->assertEquals(0, $this->band->members()->count());

        // add one member to band
        $response = $this->json(
            'put',
            route('bands.members.update', $this->band->id),
            [
                'users' => [$firstMember->id]
            ]
        );

        $response->assertOk();
        $this->assertEquals(1, $this->band->fresh()->members()->count());
        $this->assertEquals(
            collect([$firstMember])->pluck('id'),
            $this->band->fresh()->members->pluck('id')
        );
        $this->assertEquals(
            UserResource::collection(collect([$firstMember]))->response()->getData(true),
            $response->json()
        );

        //add two additional users
        $additionalMembersWithFirst = collect([
            $firstMember,
            $this->createUser(),
            $this->createUser()
        ]);

        $response = $this->json(
            'put',
            route('bands.members.update', $this->band->id),
            [
                'users' => $additionalMembersWithFirst->pluck('id')
            ]
        );

        $response->assertOk();

        $this->assertEquals(3, $this->band->fresh()->members()->count());
        $this->assertEquals(
            $additionalMembersWithFirst->pluck('id'),
            $this->band->fresh()->members->pluck('id')
        );
        $this->assertEquals(
            UserResource::collection($additionalMembersWithFirst)->response()->getData(true),
            $response->json()
        );

        // remove first user from band
        $membersWithoutFirstUser = $additionalMembersWithFirst->reject(static function ($member) use ($firstMember) {
            return $member->id === $firstMember->id;
        });

        $response = $this->json(
            'put',
            route('bands.members.update', $this->band->id),
            [
                'users' => $membersWithoutFirstUser->pluck('id')
            ]
        );

        $response->assertOk();

        $this->assertEquals(2, $this->band->fresh()->members()->count());
        $this->assertEquals(
            $membersWithoutFirstUser->pluck('id'),
            $this->band->fresh()->members->pluck('id')
        );
        $this->assertEquals(
            UserResource::collection($membersWithoutFirstUser)->response()->getData(true),
            $response->json()
        );

        // remove all users from band
        $response = $this->json(
            'put',
            route('bands.members.update', $this->band->id),
            [
                'users' => []
            ]
        );

        $response->assertOk();

        $this->assertEquals(0, $this->band->fresh()->members()->count());
    }

    /** @test */
    public function only_admin_of_a_band_can_update_bands_members(): void
    {
        $notBandsAdmin = $this->createUser();

        $this->actingAs($notBandsAdmin);

        $this->json('put', route('bands.members.update', $this->band->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function unauthorized_user_cant_update_bands_members(): void
    {
        $this->json('put', route('bands.members.update', $this->band->id))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);

    }

    /** @test */
    public function it_responds_with_validation_error_if_admin_provided_unknown_users(): void
    {
        $this->actingAs($this->bandAdmin);
        $existingUserId = $this->createUser()->id;

        $this->json(
            'put',
            route('bands.members.update', $this->band->id),
            [
                'users' => [$existingUserId, 20000]
            ]
        )
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('users.1');

        $this->json(
            'put',
            route('bands.members.update', $this->band->id),
            [
                'users' => [$existingUserId, 'max']
            ]
        )
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('users.1');
    }

    /** @test */
    public function it_responds_with_validation_error_if_admin_provided_incorrect_data_format(): void
    {
        $this->actingAs($this->bandAdmin);

        $this->json(
            'put',
            route('bands.members.update', $this->band->id),
            [
                'users' => '1,2,3'
            ]
        )
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('users');

        $this->json(
            'put',
            route('bands.members.update', $this->band->id),
            [
                [1, 2, 3]
            ]
        )
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('users');
    }
}
