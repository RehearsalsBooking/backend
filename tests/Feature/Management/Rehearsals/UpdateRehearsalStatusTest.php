<?php

namespace Tests\Feature\Management\Rehearsals;

use App\Http\Resources\RehearsalDetailedResource;
use Illuminate\Http\Response;
use Tests\Feature\Management\ManagementTestCase;

class UpdateRehearsalStatusTest extends ManagementTestCase
{
    private string $endpoint = 'management.rehearsals.status.update';
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
        $ordinaryClient = $this->createUser();

        $managerOfAnotherOrganization = $this->createUser();
        $this->createOrganizationForUser($managerOfAnotherOrganization);
        $rehearsal = $this->createRehearsal(
            1,
            2,
            $this->organization,
        );

        $this->actingAs($ordinaryClient);
        $this->json($this->httpVerb, route($this->endpoint, $rehearsal->id), ['is_confirmed'=>true])
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->actingAs($managerOfAnotherOrganization);
        $this->json($this->httpVerb, route($this->endpoint, $rehearsal->id), ['is_confirmed'=>true])
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function it_responds_with_404_when_unknown_rehearsal_is_given(): void
    {
        $this->actingAs($this->manager);
        $this->json($this->httpVerb, route($this->endpoint, 1000))
            ->assertStatus(Response::HTTP_NOT_FOUND);
        $this->json($this->httpVerb, route($this->endpoint, 'some text'))
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
            $this->httpVerb,
            route($this->endpoint, $rehearsal->id),
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
                'is_confirmed',
            ],
            [
                [
                    'is_confirmed' => 11,
                ],
                'is_confirmed',
            ],
            [
                [
                    'is_confirmed' => null,
                ],
                'is_confirmed',
            ],
            [
                [
                    'confirmed' => true,
                ],
                'is_confirmed',
            ],
        ];
    }

    /** @test */
    public function manager_of_organization_can_update_status_of_rehearsal_at_his_organization(): void
    {
        $rehearsal = $this->createRehearsal(1, 2, $this->organization);

        $this->assertFalse($rehearsal->is_confirmed);

        $this->actingAs($this->manager);

        $response = $this->json(
            $this->httpVerb,
            route($this->endpoint, $rehearsal->id),
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
            $this->httpVerb,
            route($this->endpoint, $rehearsal->id),
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
