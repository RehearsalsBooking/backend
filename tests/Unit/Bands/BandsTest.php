<?php

namespace Tests\Unit\Bands;

use App\Models\Band;
use App\Models\Rehearsal;
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

        $band = $this->createBandForUser($bandAdmin);

        $this->assertInstanceOf(User::class, $band->admin);
        $this->assertEquals(
            $bandAdmin->toArray(),
            $band->admin->toArray()
        );
    }

    /** @test */
    public function band_has_members(): void
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

    /** @test */
    public function band_has_rehearsals(): void
    {
        $band = $this->createBandForUser($this->createUser());

        $bandRehearsalsCount = 5;

        $bandRehearsals = factory(Rehearsal::class, $bandRehearsalsCount)->create([
            'band_id' => $band->id
        ]);

        $this->assertEquals(
            $bandRehearsalsCount,
            $band->rehearsals()->count()
        );

        $this->assertEquals(
            $bandRehearsals->toArray(),
            $band->rehearsals->toArray()
        );
    }
}
