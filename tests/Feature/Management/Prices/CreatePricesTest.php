<?php

namespace Tests\Feature\Management\Prices;

use App\Http\Resources\OrganizationPriceResource;
use Belamov\PostgresRange\Ranges\TimeRange;
use Illuminate\Http\Response;
use Tests\Feature\Management\ManagementTestCase;

class CreatePricesTest extends ManagementTestCase
{
    private string $endpoint = 'management.organizations.prices.create';
    private string $httpVerb = 'post';

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

        $this->actingAs($ordinaryClient);
        $this->json(
            $this->httpVerb,
            route($this->endpoint, $this->organization->id),
            [
                'day' => 6,
                'price' => 500,
                'starts_at' => '10:00',
                'ends_at' => '18:00',
            ]
        )
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->actingAs($managerOfAnotherOrganization);
        $this->json(
            $this->httpVerb,
            route($this->endpoint, $this->organization->id),
            [
                'day' => 6,
                'price' => 500,
                'starts_at' => '10:00',
                'ends_at' => '18:00',
            ]
        )
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
     * @dataProvider invalidDataForPriceCreateRequest
     * @param array $data
     * @param string|array $invalidKey
     */
    public function it_responds_with_422_when_manager_provided_invalid_data(array $data, $invalidKey): void
    {
        $this->assertEquals(5, $this->organization->prices()->count());
        $this->actingAs($this->manager);
        $response = $this->json(
            $this->httpVerb,
            route($this->endpoint, $this->organization->id),
            $data
        );

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors($invalidKey);

        $this->assertEquals(5, $this->organization->prices()->count());
    }

    /**
     * @return array
     */
    public function invalidDataForPriceCreateRequest(): array
    {
        return [
            [
                [
                    'day' => '8',
                ],
                'day',
            ],
            [
                [
                    'day' => '0',
                ],
                'day',
            ],
            [
                [
                    'day' => 'monday',
                ],
                'day',
            ],
            [
                [
                    'price' => 'big number',
                ],
                'price',
            ],
            [
                [
                    'price' => -100,
                ],
                'price',
            ],
            [
                [
                    'starts_at' => 'midnight',
                ],
                'starts_at',
            ],
            [
                [
                    'starts_at' => '000:00',
                ],
                'starts_at',
            ],
            [
                [
                    'starts_at' => '25:00',
                ],
                'starts_at',
            ],
            [
                [
                    'starts_at' => '14:65',
                ],
                'starts_at',
            ],
            [
                [
                    'starts_at' => '2005-08-09T18:31:42',
                ],
                'starts_at',
            ],
            [
                [
                    'ends_at' => 'midnight',
                ],
                'ends_at',
            ],
            [
                [
                    'ends_at' => '000:00',
                ],
                'ends_at',
            ],
            [
                [
                    'ends_at' => '25:00',
                ],
                'ends_at',
            ],
            [
                [
                    'ends_at' => '14:65',
                ],
                'ends_at',
            ],
            [
                [
                    'ends_at' => '2005-08-09T18:31:42',
                ],
                'ends_at',
            ],
            [
                [
                    'starts_at' => '10:00',
                    'ends_at' => '08:00',
                ],
                'ends_at',
            ],
            [
                [
                    'day' => '1',
                    'price' => 300,
                    'starts_at' => '14:00',
                    'ends_at' => '18:00',
                ],
                ['starts_at', 'ends_at', 'day'],
            ],
        ];
    }

    /** @test */
    public function manager_of_organization_can_add_price_entry_to_his_organization(): void
    {
        $this->assertEquals(5, $this->organization->prices()->count());

        $this->actingAs($this->manager);

        $response = $this->json(
            $this->httpVerb,
            route($this->endpoint, $this->organization->id),
            [
                'day' => 6,
                'price' => 500,
                'starts_at' => '10:00',
                'ends_at' => '24:00',
            ]
        );
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertCount(6, $response->json('data'));
        $this->assertEquals(6, $this->organization->prices()->count());
        $this->assertDatabaseHas('organization_prices', [
            'day' => 6,
            'price' => 500,
            'time' => new TimeRange('10:00', '24:00'),
            'organization_id' => $this->organization->id,
        ]);
        $this->assertEquals(
            OrganizationPriceResource::collection($this->organization->prices)->response()->getData(true),
            $response->json()
        );
    }
}
