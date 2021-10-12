<?php

namespace Tests\Feature\Users;

use App\Http\Resources\Users\UserResource;
use Tests\TestCase;

class UsersShowTest extends TestCase
{
    /** @test */
    public function it_fetches_user_info(): void
    {
        $user = $this->createUser();

        $response = $this->json('get', route('users.show', [$user]));

        $response->assertOk();

        $this->assertEquals(
            (new UserResource($user))->toArray(null),
            $response->json('data')
        );
    }

    /** @test */
    public function it_responds_with_404_when_unknown_user_id_is_provided(): void
    {
        $unknownUserId = 1000;
        $this->assertDatabaseMissing('users', ['id' => $unknownUserId]);

        $this->json('get', route('users.show', [$unknownUserId]))->assertNotFound();
    }
}
