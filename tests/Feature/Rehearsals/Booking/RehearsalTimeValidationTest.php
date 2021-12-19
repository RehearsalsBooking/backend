<?php

namespace Tests\Feature\Rehearsals\Booking;

use App\Exceptions\User\InvalidRehearsalDurationException;
use App\Exceptions\User\TimeIsUnavailableForUsersException;
use App\Exceptions\User\TimeIsUnavailableInRoomException;
use App\Exceptions\User\TooLongRehearsalException;
use App\Exceptions\User\UserHasAnotherRehearsalAtThatTimeException;
use App\Models\Rehearsal;
use App\Models\RehearsalTimeValidator;
use Carbon\Carbon;
use Tests\TestCase;
use Throwable;

class RehearsalTimeValidationTest extends TestCase
{
    /** @test */
    public function it_throws_exception_when_user_provided_incorrect_rehearsal_duration(): void
    {
        $organization = $this->createOrganization();
        $room = $this->createOrganizationRoom($organization);
        $this->createPricesForOrganization($organization);

        $invalidRehearsalDuration = [
            [
                'starts_at' => $this->getDateTimeAt(6, 00),
                'ends_at' => $this->getDateTimeAt(6, Rehearsal::MEASUREMENT_OF_REHEARSAL_DURATION_IN_MINUTES - 1),
            ],
            [
                'starts_at' => $this->getDateTimeAt(7, 30),
                'ends_at' => $this->getDateTimeAt(8, Rehearsal::MEASUREMENT_OF_REHEARSAL_DURATION_IN_MINUTES - 1),
            ],
            [
                'starts_at' => $this->getDateTimeAt(21, Rehearsal::MEASUREMENT_OF_REHEARSAL_DURATION_IN_MINUTES - 1),
                'ends_at' => $this->getDateTimeAt(23, 00),
            ],
            [
                'starts_at' => $this->getDateTimeAt(7, Rehearsal::MEASUREMENT_OF_REHEARSAL_DURATION_IN_MINUTES - 1),
                'ends_at' => $this->getDateTimeAt(23, Rehearsal::MEASUREMENT_OF_REHEARSAL_DURATION_IN_MINUTES - 2),
            ],
        ];

        foreach ($invalidRehearsalDuration as $params) {
            $rehearsalData = new RehearsalData(
                $params['starts_at'],
                $params['ends_at'],
                $room->id
            );
            $validator = new RehearsalTimeValidator();
            try {
                $validator->validate($rehearsalData);
            } catch (Throwable $exception) {
                $this->assertInstanceOf(InvalidRehearsalDurationException::class, $exception);
            }
        }
    }

    /** @test */
    public function it_throws_exception_if_duration_of_rehearsal_is_more_than_maximum_allowed_rehearsal_duration(): void
    {
        $organization = $this->createOrganization();
        $room = $this->createOrganizationRoom($organization);
        $this->createPricesForOrganization($organization);

        $rehearsalStart = Carbon::now()->addDay()->setHour(6)->setMinute(0)->setSeconds(0);
        $rehearsalEnd = $rehearsalStart->copy()->addHours(24);

        $rehearsalData = new RehearsalData(
            $rehearsalStart->toDateTimeString(),
            $rehearsalEnd->toDateTimeString(),
            $room->id
        );
        $validator = new RehearsalTimeValidator();
        try {
            $validator->validate($rehearsalData);
        } catch (Throwable $exception) {
            $this->assertInstanceOf(TooLongRehearsalException::class, $exception);
        }
    }

    /** @test */
    public function test_it_throws_error_when_time_is_unavailable(): void
    {
        $organization = $this->createOrganization();
        $room = $this->createOrganizationRoom($organization);
        $this->createPricesForOrganization($organization, '06:00', '16:00');
        $this->createPricesForOrganization($organization, '16:00', '22:00');

        $otherOrganization = $this->createOrganization();
        $otherRoom = $this->createOrganizationRoom($otherOrganization);

        $this->createPricesForOrganization($otherOrganization, '06:00', '16:00');
        $this->createPricesForOrganization($otherOrganization, '16:00', '22:00');

        $this->createRehearsal(9, 11, $room);
        $this->createRehearsal(12, 15, $room);
        $this->createRehearsal(11, 12, $otherRoom);

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
            $rehearsalData = new RehearsalData(
                $rehearsalTime['starts_at'],
                $rehearsalTime['ends_at'],
                $room->id
            );
            $validator = new RehearsalTimeValidator();
            try {
                $validator->validate($rehearsalData);
            } catch (Throwable $exception) {
                $this->assertInstanceOf(TimeIsUnavailableInRoomException::class, $exception);
            }
        }
    }

    /** @test */
    public function it_throws_exception_when_user_has_another_rehearsal_at_provided_time(): void
    {
        $availableRoom = $this->createOrganizationRoom();
        $this->createPricesForOrganization($availableRoom->organization);

        $user = $this->createUser();

        Rehearsal::factory()->create([
            'time' => $this->getTimestampRange(
                $this->getDateTimeAt(9, 0),
                $this->getDateTimeAt(11, 0),
            ),
            'user_id' => $user->id,
        ]);

        $unavailableTime = [
            [
                'starts_at' => $this->getDateTimeAt(8, 00),
                'ends_at' => $this->getDateTimeAt(10, 00),
            ],
            [
                'starts_at' => $this->getDateTimeAt(10, 00),
                'ends_at' => $this->getDateTimeAt(12, 00),
            ],
            [
                'starts_at' => $this->getDateTimeAt(8, 00),
                'ends_at' => $this->getDateTimeAt(12, 00),
            ],
            [
                'starts_at' => $this->getDateTimeAt(9, 30),
                'ends_at' => $this->getDateTimeAt(10, 30),
            ],
        ];


        foreach ($unavailableTime as $rehearsalTime) {
            $rehearsalData = new RehearsalData(
                $rehearsalTime['starts_at'],
                $rehearsalTime['ends_at'],
                $availableRoom->id,
                $user->id
            );
            $validator = new RehearsalTimeValidator();
            try {
                $validator->validate($rehearsalData);
            } catch (Throwable $exception) {
                $this->assertInstanceOf(UserHasAnotherRehearsalAtThatTimeException::class, $exception);
            }
        }
    }

    /** @test */
    public function it_throws_exception_when_band_members_are_unavailable_at_given_rehearsal_time(): void
    {
        $availableRoom = $this->createOrganizationRoom();
        $this->createPricesForOrganization($availableRoom->organization);

        $user = $this->createUser();
        $band = $this->createBandForUser($user);
        $bandMember = $this->createUser();
        $band->addMember($bandMember->id);
        Rehearsal::factory()->create([
            'time' => $this->getTimestampRange(
                $this->getDateTimeAt(9, 0),
                $this->getDateTimeAt(11, 0),
            ),
            'user_id' => $bandMember->id,
        ]);

        $unavailableTime = [
            [
                'starts_at' => $this->getDateTimeAt(8, 00),
                'ends_at' => $this->getDateTimeAt(10, 00),
            ],
            [
                'starts_at' => $this->getDateTimeAt(10, 00),
                'ends_at' => $this->getDateTimeAt(12, 00),
            ],
            [
                'starts_at' => $this->getDateTimeAt(8, 00),
                'ends_at' => $this->getDateTimeAt(12, 00),
            ],
            [
                'starts_at' => $this->getDateTimeAt(9, 30),
                'ends_at' => $this->getDateTimeAt(10, 30),
            ],
        ];

        foreach ($unavailableTime as $rehearsalTime) {
            $rehearsalData = new RehearsalData(
                $rehearsalTime['starts_at'],
                $rehearsalTime['ends_at'],
                $availableRoom->id,
                $user->id,
                $band->id
            );
            $validator = new RehearsalTimeValidator();
            try {
                $validator->validate($rehearsalData);
            } catch (Throwable $exception) {
                $this->assertInstanceOf(TimeIsUnavailableForUsersException::class, $exception);
            }
        }
    }
}
