<?php

namespace Tests\Feature\Rehearsals\Booking;

use App\Models\RehearsalDataProvider;
use Belamov\PostgresRange\Ranges\TimestampRange;

class RehearsalData implements RehearsalDataProvider
{
    public function __construct(
        protected string $startsAt,
        protected string $endsAt,
        protected int $roomId,
        protected ?int $bookedUserId = null,
        protected ?int $bandId = null,
        protected ?int $reschedulingRehearsalId = null
    ) {
    }

    public function id(): ?int
    {
        return $this->reschedulingRehearsalId;
    }

    public function time(): TimestampRange
    {
        return new TimestampRange($this->startsAt, $this->endsAt);
    }

    public function bandId(): ?int
    {
        return $this->bandId;
    }

    public function bookedUserId(): ?int
    {
        return $this->bookedUserId;
    }

    public function roomId(): int
    {
        return $this->roomId;
    }
}