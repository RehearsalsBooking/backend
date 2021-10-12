<?php

namespace Tests\Feature\Bands;

use App\Models\Band;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Storage;
use Tests\TestCase;

class BandsAvatarTest extends TestCase
{
    private User $manager;
    private Band $band;

    /** @test */
    public function only_manager_of_band_can_update_avatar(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->json(
            'post',
            route('bands.avatar', [$this->band->id]),
            ['avatar' => $file]
        )->assertUnauthorized();

        $this->actingAs($this->createUser())->json(
            'post',
            route('bands.avatar', [$this->band->id]),
            ['avatar' => $file]
        )->assertForbidden();
    }

    /** @test */
    public function avatars_can_be_only_images(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('avatar.pdf');

        $this->actingAs($this->manager);
        $response = $this->json(
            'post',
            route('bands.avatar', [$this->band->id]),
            ['avatar' => $file]
        );
        $response->assertJsonValidationErrors('avatar');

        $this->actingAs($this->manager);
        $response = $this->json(
            'post',
            route('bands.avatar', [$this->band->id]),
        );
        $response->assertJsonValidationErrors('avatar');
        $this->assertEmpty(Storage::disk('public')->allFiles());
        $this->assertNull($this->band->getFirstMedia('avatar'));
    }

    /** @test */
    public function it_updates_band_avatar(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->actingAs($this->manager);
        $response = $this->json(
            'post',
            route('bands.avatar', [$this->band->id]),
            ['avatar' => $file]
        );

        $response->assertOk();
        $avatarPath = "{$this->band->getFirstMedia('avatar')->id}/{$file->hashName()}";
        Storage::disk('public')->assertExists($avatarPath);
    }

    /** @test */
    public function it_provides_avatar_info(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('avatar.jpg');
        $this->band->addMedia($file)
            ->usingFileName($file->hashName())
            ->toMediaCollection('avatar');

        $response = $this->json('get', route('bands.show', [$this->band->id]));
        $response->assertOk();
        $this->assertArrayHasKey('avatar', $response->json('data'));
        $this->assertArrayHasKey('original', $response->json('data.avatar'));
        $this->assertArrayHasKey('thumb', $response->json('data.avatar'));

        $this->assertEquals(
            $this->band->getFirstMedia('avatar')->getFullUrl(),
            $response->json('data.avatar.original')
        );
        $this->assertEquals(
            $this->band->getFirstMedia('avatar')->getFullUrl('thumb'),
            $response->json('data.avatar.thumb')
        );

        $response = $this->json('get', route('bands.list', [$this->band->id]));
        $response->assertOk();
        $this->assertArrayHasKey('avatar', $response->json('data.0'));
        $this->assertArrayHasKey('original', $response->json('data.0.avatar'));
        $this->assertArrayHasKey('thumb', $response->json('data.0.avatar'));

        $this->assertEquals(
            $this->band->getFirstMedia('avatar')->getFullUrl(),
            $response->json('data.0.avatar.original')
        );
        $this->assertEquals(
            $this->band->getFirstMedia('avatar')->getFullUrl('thumb'),
            $response->json('data.0.avatar.thumb')
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = $this->createUser();
        $this->band = $this->createBandForUser($this->manager);
    }
}
