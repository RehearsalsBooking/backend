<?php

namespace Tests\Feature\Management\Prices;

use App\Http\Resources\RoomPriceResource;
use Belamov\PostgresRange\Ranges\TimeRange;
use Tests\Feature\Management\ManagementTestCase;

class CreatePricesTest extends ManagementTestCase
{
    private string $endpoint = 'management.rooms.prices.create';
    private string $httpVerb = 'post';

    /** @test */
    public function unauthorized_user_cannot_access_endpoint(): void
    {
        $this->json($this->httpVerb, route($this->endpoint, 1))
            ->assertUnauthorized();
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
            route($this->endpoint, $this->organizationRoom->id),
            [
                'day' => 6,
                'price' => 500,
                'starts_at' => '10:00',
                'ends_at' => '18:00',
            ]
        )
            ->assertForbidden();

        $this->actingAs($managerOfAnotherOrganization);
        $this->json(
            $this->httpVerb,
            route($this->endpoint, $this->organizationRoom->id),
            [
                'day' => 6,
                'price' => 500,
                'starts_at' => '10:00',
                'ends_at' => '18:00',
            ]
        )
            ->assertForbidden();
    }

    /** @test */
    public function it_responds_with_404_when_unknown_organization_is_given(): void
    {
        $this->actingAs($this->manager);
        $this->json($this->httpVerb, route($this->endpoint, 1000))
            ->assertNotFound();
        $this->json($this->httpVerb, route($this->endpoint, 'some text'))
            ->assertNotFound();
    }

    /**
     * @test
     * @dataProvider invalidDataForPriceCreateRequest
     * @param  array  $data
     * @param  string|array  $invalidKey
     */
    public function it_responds_with_422_when_manager_provided_invalid_data(array $data, string|array $invalidKey): void
    {
        $this->assertEquals(5, $this->organizationRoom->prices()->count());
        $this->actingAs($this->manager);
        $response = $this->json(
            $this->httpVerb,
            route($this->endpoint, $this->organizationRoom->id),
            $data
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors($invalidKey);

        $this->assertArrayHasKey('message', $response->json());

        $this->assertEquals(5, $this->organizationRoom->prices()->count());
    }

    /**
     * @return array
     */
    public function invalidDataForPriceCreateRequest(): array
    {
        return [
            [
                [
                    'day' => '7',
                ],
                'day',
            ],
            [
                [
                    'day' => '-1',
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
    public function manager_of_organization_can_add_price_entry_to_his_room(): void
    {
        $this->assertEquals(5, $this->organizationRoom->prices()->count());

        $this->actingAs($this->manager);

        $response = $this->json(
            $this->httpVerb,
            route($this->endpoint, $this->organizationRoom->id),
            [
                'day' => 6,
                'price' => 500,
                'starts_at' => '10:00',
                'ends_at' => '24:00',
            ]
        );
        $response->assertCreated();

        $this->assertCount(6, $response->json('data'));
        $this->assertEquals(6, $this->organizationRoom->prices()->count());
        $this->assertDatabaseHas('organization_room_prices', [
            'day' => 6,
            'price' => 500,
            'time' => new TimeRange('10:00', '24:00'),
            'organization_room_id' => $this->organizationRoom->id,
        ]);
        $this->assertEquals(
            RoomPriceResource::collection($this->organizationRoom->prices)->response()->getData(true),
            $response->json()
        );
    }
}
