<?php

namespace Tests\Feature\Management\Rehearsals;

use App\Http\Resources\Management\RehearsalDetailedResource;
use Illuminate\Http\Response;
use Tests\Feature\Management\ManagementTestCase;

class UpdateRehearsalStatusTest extends ManagementTestCase
{
    private string $endpointForStatusUpdate = 'rehearsal-status-update';

    /** @test */
    public function unauthorized_user_cannot_access_endpoint(): void
    {
        $this->json('put', route($this->endpointForStatusUpdate, 1))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function it_responds_with_forbidden_error_when_endpoint_is_accessed_not_by_organization_owner(): void
    {
        $ordinaryClient = $this->createUser();

        $managerOfAnotherOrganization = $this->createUser();
        $this->createOrganizationForUser($managerOfAnotherOrganization);
        $rehearsal = $this->createRehearsal(
            1,
            2,
            $this->organization,
            null,
            false,
            $ordinaryClient
        );

        $this->actingAs($ordinaryClient);
        $this->json('put', route($this->endpointForStatusUpdate, $rehearsal->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->actingAs($managerOfAnotherOrganization);
        $this->json('put', route($this->endpointForStatusUpdate, $rehearsal->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function it_responds_with_404_when_unknown_rehearsal_is_given(): void
    {
        $this->actingAs($this->manager);
        $this->json('put', route($this->endpointForStatusUpdate, 1000))
            ->assertStatus(Response::HTTP_NOT_FOUND);
        $this->json('put', route($this->endpointForStatusUpdate, 'some text'))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /**
     * @test
     * @dataProvider invalidDataForStatusUpdateRequest
     * @param array $data
     * @param string $invalidKey
     */
    public function it_responds_with_unprocessable_error_when_user_provided_invalid_data(array $data, string $invalidKey): void
    {
        $rehearsal = $this->createRehearsal(1, 2, $this->organization);

        $this->actingAs($this->manager);
        $response = $this->json(
            'put',
            route($this->endpointForStatusUpdate, $rehearsal->id),
            $data
        );

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors($invalidKey);
    }

    /**
     * @return array
     */
    public function invalidDataForStatusUpdateRequest(): array
    {
        return [
            [
                [
                    'is_confirmed' => 'not boolean',
                ],
                'is_confirmed'
            ],
            [
                [
                    'is_confirmed' => 11,
                ],
                'is_confirmed'
            ],
            [
                [
                    'is_confirmed' => null,
                ],
                'is_confirmed'
            ],
            [
                [
                    'confirmed' => true,
                ],
                'is_confirmed'
            ],
        ];
    }

    /** @test */
    public function manager_of_organization_can_update_status_of_rehearsal_at_his_organization(): void
    {
        $this->withoutExceptionHandling();
        $rehearsal = $this->createRehearsal(1, 2, $this->organization);

        $this->assertFalse($rehearsal->is_confirmed);

        $this->actingAs($this->manager);

        $response = $this->json(
            'put',
            route($this->endpointForStatusUpdate, $rehearsal->id),
            ['is_confirmed' => true]
        );
        $response->assertStatus(Response::HTTP_OK);

        $rehearsal = $rehearsal->fresh();

        $this->assertTrue($rehearsal->is_confirmed);
        $this->assertEquals(
            (new RehearsalDetailedResource($rehearsal))->response()->getData(true),
            $response->json()
        );

        $response = $this->json(
            'put',
            route($this->endpointForStatusUpdate, $rehearsal->id),
            ['is_confirmed' => false]
        );
        $response->assertStatus(Response::HTTP_OK);

        $rehearsal = $rehearsal->fresh();

        $this->assertFalse($rehearsal->is_confirmed);
        $this->assertEquals(
            (new RehearsalDetailedResource($rehearsal))->response()->getData(true),
            $response->json()
        );
    }
}
