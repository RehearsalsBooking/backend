<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Http\Response;
use Tests\TestCase;

class UsersUpdateTest extends TestCase
{
    private User $user;

    /** @test */
    public function user_can_change_his_contact_info(): void
    {
        $newContactInfo = [
            'name' => 'new name',
            'link' => 'new link',
        ];

        $this->actingAs($this->user);

        $response = $this->json('put', route('users.update'), $newContactInfo);
        $response->assertOk();

        $this->assertDatabaseHas('users', array_merge(['id' => $this->user->id], $newContactInfo));
    }

    /** @test */
    public function user_cannot_change_his_email(): void
    {
        $newContactInfo = [
            'name' => 'new name',
            'link' => 'new link',
        ];

        $currentEmail = $this->user->email;

        $this->actingAs($this->user);

        $response = $this->json('put', route('users.update'), array_merge($newContactInfo, [
            'email' => 'new@email.com',
        ]));
        $response->assertOk();

        $this->assertDatabaseHas('users', array_merge(['id' => $this->user->id], $newContactInfo));
        $this->assertEquals($currentEmail, $this->user->fresh()->email);
    }

    /** @test */
    public function unauthorized_user_cannot_update_info(): void
    {
        $this->json('put', route('users.update'))->assertUnauthorized();
    }

    /** @test
     * @dataProvider invalidUserData
     * @param $invalidData
     * @param $errorKey
     */
    public function it_responses_with_422_when_user_provided_invalid_data($invalidData, $errorKey): void
    {
        $this->actingAs($this->user);

        $response = $this->json('put', route('users.update'), $invalidData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors($errorKey);
    }

    public function invalidUserData(): array
    {
        return [
            [
                [
                ],
                'name',
            ],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUser();
    }
}
