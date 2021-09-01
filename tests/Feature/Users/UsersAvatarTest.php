<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Storage;
use Tests\TestCase;

class UsersAvatarTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    /** @test */
    public function only_user_of_band_can_update_avatar(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->json(
            'post',
            route('users.avatar', [$this->user->id]),
            ['avatar' => $file]
        )->assertUnauthorized();
    }

    /** @test */
    public function avatars_can_be_only_images(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('avatar.pdf');

        $this->actingAs($this->user);
        $response = $this->json(
            'post',
            route('users.avatar', [$this->user->id]),
            ['avatar' => $file]
        );
        $response->assertJsonValidationErrors('avatar');

        $this->actingAs($this->user);
        $response = $this->json(
            'post',
            route('users.avatar', [$this->user->id]),
        );
        $response->assertJsonValidationErrors('avatar');
        $this->assertEmpty(Storage::disk('public')->allFiles());
        $this->assertNull($this->user->getFirstMedia('avatar'));
    }

    /** @test */
    public function it_updates_user_avatar(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->actingAs($this->user);
        $response = $this->json(
            'post',
            route('users.avatar', [$this->user->id]),
            ['avatar' => $file]
        );

        $response->assertOk();
        $avatarPath = "{$this->user->getFirstMedia('avatar')->id}/{$file->hashName()}";
        Storage::disk('public')->assertExists($avatarPath);
    }

    /** @test */
    public function it_provides_avatar_info(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('avatar.jpg');
        $this->user->addMedia($file)
            ->usingFileName($file->hashName())
            ->toMediaCollection('avatar');

        $response = $this->json('get', route('users.show', [$this->user->id]));
        $response->assertOk();
        $this->assertArrayHasKey('avatar', $response->json('data'));
        $this->assertArrayHasKey('original', $response->json('data.avatar'));
        $this->assertArrayHasKey('thumb', $response->json('data.avatar'));

        $this->assertEquals(
            $this->user->getFirstMedia('avatar')->getFullUrl(),
            $response->json('data.avatar.original')
        );
        $this->assertEquals(
            $this->user->getFirstMedia('avatar')->getFullUrl('thumb'),
            $response->json('data.avatar.thumb')
        );

        $response = $this->actingAs($this->user)->json('get', route('me'));
        $response->assertOk();
        $this->assertArrayHasKey('avatar', $response->json('data'));
        $this->assertArrayHasKey('original', $response->json('data.avatar'));
        $this->assertArrayHasKey('thumb', $response->json('data.avatar'));

        $this->assertEquals(
            $this->user->getFirstMedia('avatar')->getFullUrl(),
            $response->json('data.avatar.original')
        );
        $this->assertEquals(
            $this->user->getFirstMedia('avatar')->getFullUrl('thumb'),
            $response->json('data.avatar.thumb')
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser();
    }
}
