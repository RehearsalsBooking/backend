<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class UsersUpdateTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    /** @test */
    public function user_can_change_his_contact_info(): void
    {
        $newContactInfo = [
            'name' => 'new name',
            'public_email' => 'new@mail.com',
            'phone' => 'new phone',
            'link' => 'new link'
        ];

        $this->actingAs($this->user);

        $response = $this->json('put', route('users.update'), $newContactInfo);
        $response->assertOk();

        $this->assertDatabaseHas('users', array_merge(['id' => $this->user->id], $newContactInfo));
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

    /**
     * @return array|array[]
     */
    public function invalidUserData(): array
    {
        return [
            [
                [
                    'public_email' => 'some@email.com'
                ],
                'name'
            ],
            [
                [
                    'name' => 'new name',
                    'public_email' => 'incorrect mail'
                ],
                'public_email'
            ],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
    }
}
