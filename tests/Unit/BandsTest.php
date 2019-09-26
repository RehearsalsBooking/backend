<?php

namespace Tests\Unit;

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
}
