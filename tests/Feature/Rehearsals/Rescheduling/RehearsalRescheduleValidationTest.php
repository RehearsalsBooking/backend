<?php

namespace Tests\Feature\Rehearsals\Rescheduling;

use App\Models\Rehearsal;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Tests\TestCase;

class RehearsalRescheduleValidationTest extends TestCase
{
    /**
     * @test
     * @dataProvider getDataWithInvalidFormat
     * @param $data
     * @param $keyWithError
     */
    public function it_responds_with_validation_error_when_user_provided_invalid_parameters($data, $keyWithError): void
    {
        $organization = $this->createOrganization();
        $room = $this->createOrganizationRoom($organization);

        $this->createPricesForOrganization($organization, '08:00', '16:00');
        $this->createPricesForOrganization($organization, '16:00', '22:00');

        $user = $this->createUser();

        $this->actingAs($user);

        $rehearsal = $this->createRehearsal(9, 11, $room, null, false, $user);

        $response = $this->json(
            'put',
            route('rehearsals.reschedule', $rehearsal->id),
            $data
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors($keyWithError);
    }

    /** @test */
    public function it_responds_with_404_when_user_provided_unknown_rehearsal_in_uri(): void
    {
        $this->json('put', route('rehearsals.reschedule', 10000))
            ->assertStatus(Response::HTTP_NOT_FOUND);
        $this->json('put', route('rehearsals.reschedule', 'asd'))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /**
     * @test
     * @dataProvider getDataOutOfBoundariesOfOrganizationWorkingDay
     * @param $data
     */
    public function it_responds_with_validation_error_when_user_provided_time_when_organization_is_closed($data): void
    {
        $organization = $this->createOrganization();
        $room = $this->createOrganizationRoom($organization);

        $this->createPricesForOrganization($organization, '08:00', '16:00');
        $this->createPricesForOrganization($organization, '16:00', '22:00');

        $user = $this->createUser();

        $this->actingAs($user);

        $rehearsal = $this->createRehearsal(9, 11, $room, null, false, $user);

        $response = $this->json(
            'put',
            route('rehearsals.reschedule', $rehearsal->id),
            $data
        );

        $response->assertUnprocessable();
    }

    /**
     * @test
     */
    public function it_responds_with_validation_error_when_user_changed_rehearsal_duration_to_more_than_24_hours(): void
    {
        $organization = $this->createOrganization();
        $room = $this->createOrganizationRoom($organization);

        $this->createPricesForOrganization($organization);

        $user = $this->createUser();

        $this->actingAs($user);

        $rehearsal = $this->createRehearsal(9, 11, $room, null, false, $user);

        $response = $this->json(
            'put',
            route('rehearsals.reschedule', $rehearsal->id),
            [
                'starts_at' => $rehearsal->time->from()->toDateTimeString(),
                'ends_at' => $rehearsal->time->from()->copy()->addHours(24)->toDateTimeString(),
            ]
        );
        $response->assertUnprocessable();
    }

    /**
     * @test
     */
    public function it_responds_with_validation_error_when_user_changed_rehearsal_duration_to_invalid_value(): void
    {
        $organization = $this->createOrganization();
        $room = $this->createOrganizationRoom($organization);

        $this->createPricesForOrganization($organization);

        $user = $this->createUser();

        $this->actingAs($user);

        $rehearsal = $this->createRehearsal(9, 11, $room, null, false, $user);

        $response = $this->json(
            'put',
            route('rehearsals.reschedule', $rehearsal->id),
            [
                'starts_at' => $rehearsal->time->from()->toDateTimeString(),
                'ends_at' => $rehearsal->time->from()->copy()->addHours(2)->addMinutes(13)->toDateTimeString(),
            ]
        );
        $response->assertUnprocessable();
    }

    /** @test */
    public function it_responds_with_validation_error_when_user_selected_unavailable_time(): void
    {
        $organization = $this->createOrganization();
        $room = $this->createOrganizationRoom($organization);

        $this->createPricesForOrganization($organization, '06:00', '16:00');
        $this->createPricesForOrganization($organization, '16:00', '22:00');

        $user = $this->createUser();

        $this->actingAs($user);

        $rehearsal = $this->createRehearsal(20, 21, $room, null, false, $user);

        $otherOrganization = $this->createOrganization();
        $otherRoom = $this->createOrganizationRoom($otherOrganization);

        $this->createPricesForOrganization($otherOrganization, '06:00', '16:00');
        $this->createPricesForOrganization($otherOrganization, '16:00', '22:00');

        Rehearsal::factory()->create([
            'time' => $this->getTimestampRange(
                $this->getDateTimeAt(9, 0),
                $this->getDateTimeAt(11, 0)
            ),
            'organization_room_id' => $room->id,
        ]);
        Rehearsal::factory()->create([
            'time' => $this->getTimestampRange(
                $this->getDateTimeAt(12, 0),
                $this->getDateTimeAt(15, 0)
            ),
            'organization_room_id' => $room->id,
        ]);
        Rehearsal::factory()->create([
            'time' => $this->getTimestampRange(
                $this->getDateTimeAt(11, 0),
                $this->getDateTimeAt(12, 0)
            ),
            'organization_room_id' => $otherRoom->id,
        ]);

        $unavailableTime = [
            [
                'starts_at' => $this->getDateTimeAt(8, 00),
                'ends_at' => $this->getDateTimeAt(10, 00),
            ],
            [
                'starts_at' => $this->getDateTimeAt(9, 00),
                'ends_at' => $this->getDateTimeAt(10, 00),
            ],
            [
                'starts_at' => $this->getDateTimeAt(10, 00),
                'ends_at' => $this->getDateTimeAt(11, 00),
            ],
            [
                'starts_at' => $this->getDateTimeAt(10, 00),
                'ends_at' => $this->getDateTimeAt(12, 00),
            ],
            [
                'starts_at' => $this->getDateTimeAt(10, 00),
                'ends_at' => $this->getDateTimeAt(13, 00),
            ],
            [
                'starts_at' => $this->getDateTimeAt(8, 00),
                'ends_at' => $this->getDateTimeAt(12, 00),
            ],
        ];

        foreach ($unavailableTime as $rehearsalTime) {
            $this->json(
                'put',
                route('rehearsals.reschedule', $rehearsal->id),
                [
                    'starts_at' => $rehearsalTime['starts_at'],
                    'ends_at' => $rehearsalTime['ends_at'],
                ]
            )->assertUnprocessable();
        }

        $this->json(
            'put',
            route('rehearsals.reschedule', $rehearsal->id),
            [
                'starts_at' => $this->getDateTimeAt(11, 00),
                'ends_at' => $this->getDateTimeAt(12, 00),
            ]
        )->assertOk();
    }

    /** @test */
    public function it_lets_user_reschedule_rehearsal_when_new_time_intersects_with_old_time(): void
    {
        $organization = $this->createOrganization();
        $room = $this->createOrganizationRoom($organization);

        $this->createPricesForOrganization($organization, '06:00', '16:00');
        $this->createPricesForOrganization($organization, '16:00', '22:00');

        $user = $this->createUser();

        $this->actingAs($user);

        $rehearsal = $this->createRehearsal(10, 12, $room, null, false, $user);

        $this->json(
            'put',
            route('rehearsals.reschedule', $rehearsal->id),
            [
                'starts_at' => $this->getDateTimeAt(11, 00),
                'ends_at' => $this->getDateTimeAt(13, 00),
            ]
        )->assertOk();
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
                    'starts_at',
                ],

                [
                    [
                        'ends_at' => $date->toDateTimeString(),
                    ],
                    'starts_at',
                ],

                [
                    [
                        'starts_at' => 123123,
                        'ends_at' => $date->toDateTimeString(),
                    ],
                    'starts_at',
                ],

                [
                    [
                        'starts_at' => '123123',
                        'ends_at' => $date->toDateTimeString(),
                    ],
                    'starts_at',
                ],

                [
                    [
                        'starts_at' => $date->toDateTimeString(),
                        'ends_at' => null,
                    ],
                    'ends_at',
                ],

                [
                    [
                        'starts_at' => $date->toDateTimeString(),
                    ],
                    'ends_at',
                ],

                [
                    [
                        'starts_at' => $date->toDateTimeString(),
                        'ends_at' => 123123,
                    ],
                    'ends_at',
                ],

                [
                    [
                        'starts_at' => $date->toDateTimeString(),
                        'ends_at' => '123123',
                    ],
                    'ends_at',
                ],

                [
                    [
                        'starts_at' => $date->toDateTimeString(),
                        'ends_at' => $date->copy()->subHour()->toDateTimeString(),
                    ],
                    'ends_at',
                ],

                [
                    [
                        'starts_at' => $date->subHour()->toDateTimeString(),
                        'ends_at' => $date->addHour()->toDateTimeString(),
                    ],
                    'starts_at',
                ],
            ];
    }

    public function getDataOutOfBoundariesOfOrganizationWorkingDay(): array
    {
        return
            [
                [
                    [
                        'starts_at' => $this->getDateTimeAt(7, 30),
                        'ends_at' => $this->getDateTimeAt(11, 00),
                    ],
                ],

                [
                    [
                        'starts_at' => $this->getDateTimeAt(21, 00),
                        'ends_at' => $this->getDateTimeAt(23, 00),
                    ],
                ],

                [
                    [
                        'starts_at' => $this->getDateTimeAt(7, 00),
                        'ends_at' => $this->getDateTimeAt(23, 00),
                    ],
                ],
            ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->createUser());
    }
}
