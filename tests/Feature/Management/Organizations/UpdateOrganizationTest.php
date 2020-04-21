<?php

namespace Tests\Feature\Management\Organizations;

use App\Http\Resources\Management\OrganizationResource;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Management\ManagementTestCase;

class UpdateOrganizationTest extends ManagementTestCase
{
    private string $endpoint = 'management.organizations.update';
    private string $httpVerb = 'put';

    /** @test */
    public function unauthorized_user_cannot_access_endpoint(): void
    {
        $this->json($this->httpVerb, route($this->endpoint, 1))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function it_responds_with_forbidden_error_when_endpoint_is_accessed_not_by_organization_owner(): void
    {
        $data = ['name' => 'new name', 'address' => 'new address'];

        $ordinaryClient = $this->createUser();

        $managerOfAnotherOrganization = $this->createUser();
        $this->createOrganizationForUser($managerOfAnotherOrganization);

        $this->actingAs($ordinaryClient);
        $this->json($this->httpVerb, route($this->endpoint, $this->organization->id), $data)
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->actingAs($managerOfAnotherOrganization);
        $this->json($this->httpVerb, route($this->endpoint, $this->organization->id), $data)
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function it_responds_with_404_when_unknown_organization_is_given(): void
    {
        $this->actingAs($this->manager);
        $this->json($this->httpVerb, route($this->endpoint, 1000))
            ->assertStatus(Response::HTTP_NOT_FOUND);
        $this->json($this->httpVerb, route($this->endpoint, 'some text'))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /**
     * @test
     */
    public function it_responds_with_unprocessable_error_when_user_provided_invalid_data(): void
    {
        $this->actingAs($this->manager);

        $organizationInfoWithoutName = [
            'address' => 'new address',
            'coordinates' => 'new coordinates',
            'description' => 'new description',
        ];

        $response = $this->json(
            $this->httpVerb,
            route($this->endpoint, $this->organization->id),
            $organizationInfoWithoutName
        );

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('name');
    }

    /** @test */
    public function manager_of_organization_can_update_his_organization(): void
    {
        Storage::fake('public');

        $this->actingAs($this->manager);
        $avatar = UploadedFile::fake()->image('avatar.png');

        $newOrganizationInfo = [
            'name' => 'new name',
            'address' => 'new address',
            'coordinates' => 'new coordinates',
            'description' => 'new description',
        ];

        $response = $this->json(
            $this->httpVerb,
            route($this->endpoint, $this->organization->id),
            array_merge($newOrganizationInfo, ['avatar' => $avatar])
        );
        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas(
            'organizations',
            array_merge(['id' => $this->organization->id], $newOrganizationInfo)
        );
        Storage::disk('public')->assertExists('avatars/'.$avatar->hashName());
        $this->assertEquals(
            (new OrganizationResource($this->organization->fresh()))->response()->getData(true),
            $response->json()
        );
    }

    /** @test */
    public function when_manager_updates_avatar_old_avatar_file_is_deleted(): void
    {
        Storage::fake('public');

        $this->actingAs($this->manager);

        $avatar = UploadedFile::fake()->image('avatar.png');

        $newOrganizationInfo = [
            'name' => 'new name',
            'address' => 'new address',
            'avatar' => $avatar,
        ];

        $response = $this->json(
            $this->httpVerb,
            route($this->endpoint, $this->organization->id),
            $newOrganizationInfo
        );
        $response->assertStatus(Response::HTTP_OK);

        Storage::disk('public')->assertExists('avatars/'.$avatar->hashName());

        //updating avatar
        $newAvatar = UploadedFile::fake()->image('new_avatar.png');

        $newOrganizationInfo = [
            'name' => 'new name',
            'address' => 'new address',
            'avatar' => $newAvatar,
        ];

        $response = $this->json(
            $this->httpVerb,
            route($this->endpoint, $this->organization->id),
            $newOrganizationInfo
        );
        $response->assertStatus(Response::HTTP_OK);

        Storage::disk('public')->assertMissing('avatars/'.$avatar->hashName());
        Storage::disk('public')->assertExists('avatars/'.$newAvatar->hashName());
    }
}
