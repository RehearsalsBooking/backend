<?php

namespace Tests\Feature\Invites;

use App\Models\Band;
use App\Models\User;
use Illuminate\Http\Response;
use Tests\TestCase;

class InviteValidationTest extends TestCase
{
    private User $bandAdmin;
    private Band $band;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bandAdmin = $this->createUser();
        $this->band = $this->createBandForUser($this->bandAdmin);
    }

    /** @test */
    public function it_responds_with_404_when_user_provided_unknown_band_or_user_in_url(): void
    {
        $this->actingAs($this->bandAdmin);
        $user = $this->createUser();

        $this->json(
            'post',
            route('bands.invites.create', [$this->band]),
            [
                'band_id' => 1000,
                'user_id' => $user->id,
            ]
        )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->json(
            'post',
            route('bands.invites.create', [$this->band]),
            [
                'band_id' => $this->band->id,
                'user_id' => 10000,
            ]
        )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->json(
            'delete',
            route('bands.invites.delete', [$this->band, 1000])
        )->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
