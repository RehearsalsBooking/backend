<?php

namespace Tests\Unit;

use App\Exceptions\User\InvalidRehearsalDurationException;
use App\Exceptions\User\PriceCalculationException;
use App\Exceptions\User\TimeIsUnavailableForUsersException;
use App\Exceptions\User\TimeIsUnavailableInRoomException;
use App\Exceptions\User\UserHasAnotherRehearsalAtThatTimeException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Tests\TestCase;

class DomainExceptionsTest extends TestCase
{
    /**
     * @test
     * @dataProvider getDomainExceptions
     */
    public function it_renders_domain_exceptions_as_422_response($exception): void
    {
        $this->assertTrue(method_exists($exception, 'render'));
        $this->assertInstanceOf(JsonResponse::class, $exception->render());
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $exception->render()->getStatusCode());
    }

    public function getDomainExceptions(): array
    {
        return [
            [new InvalidRehearsalDurationException()],
            [new PriceCalculationException()],
            [new TimeIsUnavailableInRoomException()],
            [new UserHasAnotherRehearsalAtThatTimeException()],
            [new TimeIsUnavailableForUsersException(collect())],
        ];
    }
}
