<?php

namespace Tests\Feature\Bands\Invites;

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
class BandsMembersInviteValidationTest extends TestCase
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
    public function it_responds_with_404_when_user_provided_unknown_band_or_user_in_url(): void
    {
        $invite = $this->createInvite();

        $this->actingAs($this->bandAdmin);

        $this->json(
            'post',
            route('bands.invites.create', 1000)
        )->assertStatus(Response::HTTP_NOT_FOUND);

        $this->json(
            'post',
            route('bands.invites.create', $this->band->id),
            [
                'user_id' => 10000
            ]
        )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->json(
            'delete',
            route('bands.invites.delete', [10000, $invite->id])
        )->assertStatus(Response::HTTP_NOT_FOUND);

        $this->json(
            'delete',
            route('bands.invites.delete', [$this->band->id, 10000])
        )->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
