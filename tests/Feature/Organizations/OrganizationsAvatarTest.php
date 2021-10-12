<?php

namespace Tests\Feature\Organizations;

use App\Models\Organization\Organization;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Storage;
use Tests\TestCase;

class OrganizationsAvatarTest extends TestCase
{
    private User $manager;
    private Organization $organization;

    /** @test */
    public function only_manager_of_organization_can_update_avatar(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->json(
            'post',
            route('management.organizations.avatar', [$this->organization->id]),
            ['avatar' => $file]
        )->assertUnauthorized();

        $this->actingAs($this->createUser())->json(
            'post',
            route('management.organizations.avatar', [$this->organization->id]),
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
            route('management.organizations.avatar', [$this->organization->id]),
            ['avatar' => $file]
        );
        $response->assertJsonValidationErrors('avatar');

        $this->actingAs($this->manager);
        $response = $this->json(
            'post',
            route('management.organizations.avatar', [$this->organization->id]),
        );
        $response->assertJsonValidationErrors('avatar');
        $this->assertEmpty(Storage::disk('public')->allFiles());
        $this->assertNull($this->organization->getFirstMedia('avatar'));
    }

    /** @test */
    public function it_updates_organization_avatar(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->actingAs($this->manager);
        $response = $this->json(
            'post',
            route('management.organizations.avatar', [$this->organization->id]),
            ['avatar' => $file]
        );

        $response->assertOk();
        $avatarPath = "{$this->organization->getFirstMedia('avatar')->id}/{$file->hashName()}";
        Storage::disk('public')->assertExists($avatarPath);
    }

    /** @test */
    public function it_provides_avatar_info(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('avatar.jpg');
        $this->organization->addMedia($file)
            ->usingFileName($file->hashName())
            ->toMediaCollection('avatar');

        $response = $this->json('get', route('organizations.show', [$this->organization->id]));
        $response->assertOk();
        $this->assertArrayHasKey('avatar', $response->json('data'));
        $this->assertArrayHasKey('original', $response->json('data.avatar'));
        $this->assertArrayHasKey('thumb', $response->json('data.avatar'));

        $this->assertEquals(
            $this->organization->getFirstMedia('avatar')->getFullUrl(),
            $response->json('data.avatar.original')
        );
        $this->assertEquals(
            $this->organization->getFirstMedia('avatar')->getFullUrl('thumb'),
            $response->json('data.avatar.thumb')
        );

        $response = $this->json('get', route('organizations.list', [$this->organization->id]));
        $response->assertOk();
        $this->assertArrayHasKey('avatar', $response->json('data.0'));
        $this->assertArrayHasKey('original', $response->json('data.0.avatar'));
        $this->assertArrayHasKey('thumb', $response->json('data.0.avatar'));

        $this->assertEquals(
            $this->organization->getFirstMedia('avatar')->getFullUrl(),
            $response->json('data.0.avatar.original')
        );
        $this->assertEquals(
            $this->organization->getFirstMedia('avatar')->getFullUrl('thumb'),
            $response->json('data.0.avatar.thumb')
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = $this->createUser();
        $this->organization = $this->createOrganizationForUser($this->manager);
    }

}
