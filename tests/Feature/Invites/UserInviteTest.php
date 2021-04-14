<?php

namespace Tests\Feature\Invites;

use App\Models\Invite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class UserInviteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function unauthorized_user_cannot_fetch_his_invites(): void
    {
        $this
            ->json('get', route('users.invites.index'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function user_can_fetch_his_invites(): void
    {
        $max = $this->createUser();
        $band = $this->createBand();
        $maxesInvite = $this->createInvite([
            'email' => $max->email,
            'band_id' => $band->id,
        ]);

        $john = $this->createUser();

        $this->createInvite([
            'email' => $john->email,
            'band_id' => $band->id,
        ]);

        $this->assertEquals(2, Invite::count());

        $this->actingAs($max);
        $response = $this->json('get', route('users.invites.index'));
        $response->assertOk();

        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($maxesInvite->id, $response->json('data.0.id'));
    }
}
