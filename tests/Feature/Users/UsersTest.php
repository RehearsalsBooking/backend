<?php

namespace Tests\Feature\Users;

use App\Http\Resources\Users\UserResource;
use Tests\TestCase;

class UsersTest extends TestCase
{
    /** @test */
    public function user_gets_correct_info_about_himself(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $response = $this->json('get', route('me'));

        $response->assertOk();

        $this->assertEquals(
            (new UserResource($user))->toArray(null),
            $response->json('data')
        );
    }
}
