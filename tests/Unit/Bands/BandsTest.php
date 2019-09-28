<?php

namespace Tests\Unit\Bands;

use App\Models\Band;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BandsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function band_has_admin(): void
    {
        $bandAdmin = $this->createUser();

        /** @var Band $band */
        $band = factory(Band::class)->create([
            'admin_id' => $bandAdmin->id
        ]);

        $this->assertInstanceOf(User::class, $band->admin);
        $this->assertEquals(
            $bandAdmin->toArray(),
            $band->admin->toArray()
        );
    }

    /** @test */
    public function band_has_multiple_members(): void
    {
        $drummer = $this->createUser();
        $guitarist = $this->createUser();
        $vocalist = $this->createUser();

        /** @var Band $rockBand */
        $rockBand = factory(Band::class)->create();

        /** @var Band $rapBand */
        $rapBand = factory(Band::class)->create();

        $rockBandMembers = collect([$drummer, $guitarist, $vocalist]);
        $rapBandMembers = collect([$drummer, $vocalist]);

        $rockBand->members()->attach($rockBandMembers->pluck('id')->toArray());
        $rapBand->members()->attach($rapBandMembers->pluck('id')->toArray());

        $this->assertEquals(
            $rockBandMembers->pluck('id'),
            $rockBand->members->pluck('id')
        );

        $this->assertEquals(
            $rapBandMembers->pluck('id'),
            $rapBand->members->pluck('id')
        );
    }
}
