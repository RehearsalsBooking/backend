<?php

namespace Tests\Feature\Rehearsals\Booking;

use App\Models\Organization\Organization;
use App\Models\Organization\OrganizationRoom;
use App\Models\Rehearsal;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait ValidatesRehearsalTime.
 *
 * This mess was created in an attempt to avoid test duplication.
 * I need to validate rehearsal time when rehearsal is creating and when
 * just calculating rehearsal price. And validation logic is absolutely the same
 * for both endpoints
 */
trait ValidatesRehearsalTime
{
    public function performTestWhenOrganizationIsClosed(
        string $method,
        string $endpoint,
        OrganizationRoom $room,
        array $additionalParameters = []
    ): void {
        $this->createPricesForOrganization($room->organization, '08:00', '16:00');
        $this->createPricesForOrganization($room->organization, '16:00', '22:00');

        $paramsWhenOrganizationIsClosed = [
            array_merge(
                [
                    'starts_at' => $this->getDateTimeAt(6, 00),
                    'ends_at' => $this->getDateTimeAt(8, 00),
                ],
                $additionalParameters
            ),

            array_merge(
                [
                    'starts_at' => $this->getDateTimeAt(7, 30),
                    'ends_at' => $this->getDateTimeAt(11, 00),
                ],
                $additionalParameters
            ),

            array_merge(
                [
                    'starts_at' => $this->getDateTimeAt(21, 00),
                    'ends_at' => $this->getDateTimeAt(23, 00),
                ],
                $additionalParameters
            ),

            array_merge(
                [
                    'starts_at' => $this->getDateTimeAt(7, 00),
                    'ends_at' => $this->getDateTimeAt(23, 00),
                ],
                $additionalParameters
            ),

            array_merge(
                [
                    'starts_at' => $this->getDateTimeAt(23, 00),
                    'ends_at' => $this->getDateTimeAt(24, 00),
                ],
                $additionalParameters
            ),
        ];

        foreach ($paramsWhenOrganizationIsClosed as $params) {
            $this->json(
                $method,
                $endpoint,
                $params
            )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function performTestsWhenUserProvidedIncorrectRehearsalDuration(
        string $method,
        string $endpoint,
        OrganizationRoom $room,
        array $additionalParameters = []
    ): void {
        $this->createPricesForOrganization($room->organization);

        $invalidRehearsalDuration = [
            array_merge(
                [
                    'starts_at' => $this->getDateTimeAt(6, 00),
                    'ends_at' => $this->getDateTimeAt(6, 15),
                ],
                $additionalParameters
            ),

            array_merge(
                [
                    'starts_at' => $this->getDateTimeAt(7, 30),
                    'ends_at' => $this->getDateTimeAt(8, 24),
                ],
                $additionalParameters
            ),

            array_merge(
                [
                    'starts_at' => $this->getDateTimeAt(21, 8),
                    'ends_at' => $this->getDateTimeAt(23, 00),
                ],
                $additionalParameters
            ),

            array_merge(
                [
                    'starts_at' => $this->getDateTimeAt(7, 13),
                    'ends_at' => $this->getDateTimeAt(23, 24),
                ],
                $additionalParameters
            ),
        ];

        foreach ($invalidRehearsalDuration as $params) {
            $this->json(
                $method,
                $endpoint,
                $params
            )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function performTestsWhenUserProvidesRehearsalTimeLongerThan24Hours(
        string $method,
        string $endpoint,
        OrganizationRoom $room,
        array $additionalParameters = []
    ): void {
        $this->createPricesForOrganization($room->organization);

        $rehearsalStart = Carbon::now()->addDay()->setHour(6)->setMinute(0)->setSeconds(0);
        $rehearsalEnd = $rehearsalStart->copy()->addHours(24);

        $tooLongRehearsals = [
            array_merge(
                [
                    'starts_at' => $rehearsalStart->toDateTimeString(),
                    'ends_at' => $rehearsalEnd->toDateTimeString(),
                ],
                $additionalParameters
            ),
        ];

        foreach ($tooLongRehearsals as $params) {
            $this->json(
                $method,
                $endpoint,
                $params
            )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function performTestsWhenUserSelectedUnavailableTime(
        string $method,
        string $endpoint,
        OrganizationRoom $room,
        array $additionalParameters = []
    ): void {
        $this->createPricesForOrganization($room->organization, '06:00', '16:00');
        $this->createPricesForOrganization($room->organization, '16:00', '22:00');

        $otherOrganization = $this->createOrganization();
        $otherRoom = $this->createOrganizationRoom($otherOrganization);

        $this->createPricesForOrganization($otherOrganization, '06:00', '16:00');
        $this->createPricesForOrganization($otherOrganization, '16:00', '22:00');

        Rehearsal::factory()->create([
            'time' => $this->getTimestampRange(
                $this->getDateTimeAt(9, 0),
                $this->getDateTimeAt(11, 0),
            ),
            'organization_id' => $organization->id,
        ]);
        Rehearsal::factory()->create([
            'time' => $this->getTimestampRange(
                $this->getDateTimeAt(12, 0),
                $this->getDateTimeAt(15, 0)
            ),
            'organization_id' => $organization->id,
        ]);
        Rehearsal::factory()->create([
            'time' => $this->getTimestampRange(
                $this->getDateTimeAt(11, 0),
                $this->getDateTimeAt(12, 0)
            ),
            'organization_id' => $otherOrganization->id,
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
                $method,
                $endpoint,
                array_merge(
                    [
                        'starts_at' => $rehearsalTime['starts_at'],
                        'ends_at' => $rehearsalTime['ends_at'],
                    ],
                    $additionalParameters
                )
            )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        }
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

                [
                    [
                        'starts_at' => $date->subHours(2)->toDateTimeString(),
                        'ends_at' => $date->subHour()->toDateTimeString(),
                    ],
                    'starts_at',
                ],
            ];
    }
}
