<?php

namespace Tests\Feature\Auth;

use App\Http\Resources\Management\OrganizationResource;
use App\Http\Resources\Users\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private array $credentials = [
        'email' => 'some@email.com',
        'password' => 'some password',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUser([
            'password' => bcrypt($this->credentials['password']),
            'email' => $this->credentials['email'],
        ]);
    }

    /** @test */
    public function it_logins_users_with_correct_credentials(): void
    {
        $response = $this->post(route('login'), $this->credentials);

        $response->assertOk();

        $token = $response->json('token');

        $response = $this->get(route('me'), ['Authorization' => 'Bearer '.$token]);

        $response->assertOk();

        $this->assertEquals($this->user->id, $response->json('data.id'));
    }

    /** @test */
    public function it_doesnt_login_user_with_invalid_credentials(): void
    {
        $this->post(route('login'), [
            'email' => $this->credentials['email'],
            'password' => 'some wrong password',
        ])->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this->withoutExceptionHandling();
        $this->post(route('login'), [
            'email' => 'unknown@email.com',
            'password' => $this->credentials['password'],
        ])->assertStatus(Response::HTTP_UNAUTHORIZED);
        $this->assertGuest();
    }

    /** @test */
    public function logged_in_user_can_fetch_info_about_himself(): void
    {
        $this->actingAs($this->user);
        $response = $this->get(route('me'));

        $response->assertOk();

        $this->assertEquals(
            (new UserResource($this->user))->toResponse(null)->getData(true)['data'],
            $response->json('data')
        );
        $this->assertEmpty($response->json('data.organizations'));
    }

    /** @test */
    public function it_fetches_ids_of_organizations_which_user_can_manage(): void
    {
        $organization = $this->createOrganizationForUser($this->user);

        $this->assertCount(1, $this->user->fresh()->organizations);

        $this->actingAs($this->user);
        $response = $this->get(route('me'));
        $response->assertOk();
        $this->assertEquals(
            (OrganizationResource::collection(collect([$organization])))->response()->getData(true)['data'],
            $response->json('data.organizations')
        );
    }

    /** @test */
    public function user_can_logout(): void
    {
        $token = $this->user->createToken('some token')->plainTextToken;

        $response = $this->json('post', route('logout'), [], ['Authorization' => 'Bearer '.$token]);

        $response->assertNoContent();

        $this->assertEquals(0, $this->user->tokens()->count());
    }
}
