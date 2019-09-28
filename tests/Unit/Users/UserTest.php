<?php

namespace Tests\Unit\Users;

use App\Models\Band;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_have_organizations(): void
    {
        $user = $this->createUser();

        $this->createOrganization([
            'owner_id' => $user->id
        ]);

        $this->assertInstanceOf(Collection::class, $user->organizations);
        $this->assertInstanceOf(Organization::class, $user->organizations->first());
    }

    /** @test */
    public function user_can_be_admin_in_multiple_bands(): void
    {
        $user = $this->createUser();

        $numberOfUsersBands = 5;

        $usersBands = factory(Band::class, $numberOfUsersBands)->create([
            'admin_id' => $user->id
        ]);

        $this->assertEquals($numberOfUsersBands, $user->createdBands()->count());
        $this->assertEquals(
            $usersBands->toArray(),
            $user->createdBands->toArray()
        );
    }

    /** @test */
    public function users_can_participate_in_multiple_bands(): void
    {
        $drummer = $this->createUser();
        $guitarist = $this->createUser();

        /** @var Band $rockBand */
        $rockBand = factory(Band::class)->create();

        /** @var Band $rapBand */
        $rapBand = factory(Band::class)->create();

        /** @var Band $rapBand */
        $popBand = factory(Band::class)->create();

        $drummersBands = collect([$rockBand, $rapBand, $popBand]);
        $guitaristsBands = collect([$rockBand, $popBand]);

        $drummer->bands()->attach($drummersBands->pluck('id'));
        $guitarist->bands()->attach($guitaristsBands->pluck('id'));

        $this->assertEquals(
            $drummersBands->pluck('id'),
            $drummer->bands->pluck('id')
        );

        $this->assertEquals(
            $guitaristsBands->pluck('id'),
            $guitarist->bands->pluck('id')
        );
    }
}
