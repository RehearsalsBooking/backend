<?php

namespace Tests\Feature\Rehearsals;

use App\Models\Rehearsal;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RehearsalBookingValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->createUser());
    }

    /**
     * @test
     * @dataProvider getDataWithInvalidFormat
     * @param $data
     * @param $keyWithError
     */
    public function it_responds_with_validation_error_when_user_provided_invalid_parameters($data, $keyWithError): void
    {
        $organization = $this->createOrganization([
            'opens_at' => '08:00',
            'closes_at' => '22:00',
        ]);

        $response = $this->json(
            'post',
            route('organizations.rehearsals.create', $organization->id),
            $data
        );

        $response->assertJsonValidationErrors($keyWithError);
    }

    /** @test */
    public function it_responds_with_404_when_user_provided_unknown_organization_in_uri(): void
    {
        $this->json('post', route('organizations.rehearsals.create', 10000))
            ->assertStatus(Response::HTTP_NOT_FOUND);
        $this->json('post', route('organizations.rehearsals.create', 'asd'))
            ->assertStatus(Response::HTTP_NOT_FOUND);

    }

    /** @test */
    public function it_responds_with_validation_error_when_user_provided_unknown_band_id(): void
    {
        $organization = $this->createOrganization([
            'opens_at' => '8:00',
            'closes_at' => '22:00'
        ]);

        $this->json('post', route('organizations.rehearsals.create', $organization->id), [
            'band_id' => 10000,
            'starts_at' => $this->getDateTimeAt(12, 00),
            'ends_at' => $this->getDateTimeAt(13, 00)
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('band_id');

    }

    /**
     * @test
     * @dataProvider getDataOutOfBoundariesOfOrganizationWorkingDay
     * @param $organizationWorkingHours
     * @param $data
     * @param $keyWithError
     */
    public function it_responds_with_validation_error_when_user_provided_time_when_organization_is_closed($organizationWorkingHours, $data, $keyWithError): void
    {
        $organization = $this->createOrganization($organizationWorkingHours);

        $response = $this->json(
            'post',
            route('organizations.rehearsals.create', $organization->id),
            $data
        );

        $response->assertJsonValidationErrors($keyWithError);
    }

    /** @test */
    public function it_responds_with_validation_error_when_user_selected_unavailable_time(): void
    {
        $organization = $this->createOrganization([
            'opens_at' => '06:00',
            'closes_at' => '22:00',
        ]);

        $otherOrganization = $this->createOrganization([
            'opens_at' => '06:00',
            'closes_at' => '22:00',
        ]);

        factory(Rehearsal::class)->create([
            'starts_at' => $this->getDateTimeAt(9, 0),
            'ends_at' => $this->getDateTimeAt(11, 0),
            'organization_id' => $organization->id
        ]);
        factory(Rehearsal::class)->create([
            'starts_at' => $this->getDateTimeAt(12, 0),
            'ends_at' => $this->getDateTimeAt(15, 0),
            'organization_id' => $organization->id
        ]);
        factory(Rehearsal::class)->create([
            'starts_at' => $this->getDateTimeAt(11, 0),
            'ends_at' => $this->getDateTimeAt(12, 0),
            'organization_id' => $otherOrganization->id
        ]);

        $this->json(
            'post',
            route('organizations.rehearsals.create', $organization->id),
            [
                'starts_at' => $this->getDateTimeAt(8, 00),
                'ends_at' => $this->getDateTimeAt(10, 00),
            ]
        )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->json(
            'post',
            route('organizations.rehearsals.create', $organization->id),
            [
                'starts_at' => $this->getDateTimeAt(9, 00),
                'ends_at' => $this->getDateTimeAt(10, 00),
            ]
        )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->json(
            'post',
            route('organizations.rehearsals.create', $organization->id),
            [
                'starts_at' => $this->getDateTimeAt(10, 00),
                'ends_at' => $this->getDateTimeAt(11, 00),
            ]
        )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->json(
            'post',
            route('organizations.rehearsals.create', $organization->id),
            [
                'starts_at' => $this->getDateTimeAt(10, 00),
                'ends_at' => $this->getDateTimeAt(12, 00),
            ]
        )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->json(
            'post',
            route('organizations.rehearsals.create', $organization->id),
            [
                'starts_at' => $this->getDateTimeAt(10, 00),
                'ends_at' => $this->getDateTimeAt(13, 00),
            ]
        )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->json(
            'post',
            route('organizations.rehearsals.create', $organization->id),
            [
                'starts_at' => $this->getDateTimeAt(8, 00),
                'ends_at' => $this->getDateTimeAt(12, 00),
            ]
        )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->json(
            'post',
            route('organizations.rehearsals.create', $organization->id),
            [
                'starts_at' => $this->getDateTimeAt(11, 00),
                'ends_at' => $this->getDateTimeAt(12, 00),
            ]
        )->assertStatus(Response::HTTP_CREATED);
    }

    public function getDataWithInvalidFormat(): array
    {
        $date = Carbon::now();

        return
            [
                [
                    [
                        'starts_at' => null,
                        'ends_at' => $date->toDateTimeString(),
                    ],
                    'starts_at'
                ],

                [
                    [
                        'ends_at' => $date->toDateTimeString(),
                    ],
                    'starts_at'
                ],

                [
                    [
                        'starts_at' => 123123,
                        'ends_at' => $date->toDateTimeString(),
                    ],
                    'starts_at'
                ],

                [
                    [
                        'starts_at' => '123123',
                        'ends_at' => $date->toDateTimeString(),
                    ],
                    'starts_at'
                ],

                [
                    [
                        'starts_at' => $date->toDateTimeString(),
                        'ends_at' => null,
                    ],
                    'ends_at'
                ],

                [
                    [
                        'starts_at' => $date->toDateTimeString(),
                    ],
                    'ends_at'
                ],

                [
                    [
                        'starts_at' => $date->toDateTimeString(),
                        'ends_at' => 123123,
                    ],
                    'ends_at'
                ],

                [
                    [
                        'starts_at' => $date->toDateTimeString(),
                        'ends_at' => '123123',
                    ],
                    'ends_at'
                ],

                [
                    [
                        'starts_at' => $date->toDateTimeString(),
                        'ends_at' => $date->copy()->subHour()->toDateTimeString(),
                    ],
                    'ends_at'
                ],

                [
                    [
                        'starts_at' => $date->subHour()->toDateTimeString(),
                        'ends_at' => $date->addHour()->toDateTimeString(),
                    ],
                    'starts_at'
                ],
            ];
    }

    public function getDataOutOfBoundariesOfOrganizationWorkingDay(): array
    {
        return
            [
                [
                    [
                        'opens_at' => '08:00',
                        'closes_at' => '22:00',
                    ],
                    [
                        'starts_at' => $this->getDateTimeAt(7, 30),
                        'ends_at' => $this->getDateTimeAt(11, 00),
                    ],
                    'starts_at',
                ],

                [
                    [
                        'opens_at' => '08:00',
                        'closes_at' => '22:00',
                    ],
                    [
                        'starts_at' => $this->getDateTimeAt(21, 00),
                        'ends_at' => $this->getDateTimeAt(23, 00),
                    ],
                    'ends_at',
                ],

                [
                    [
                        'opens_at' => '08:00',
                        'closes_at' => '22:00',
                    ],
                    [
                        'starts_at' => $this->getDateTimeAt(7, 00),
                        'ends_at' => $this->getDateTimeAt(23, 00),
                    ],
                    ['ends_at', 'starts_at'],
                ],
            ];
    }

}
