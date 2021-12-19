<?php

namespace App\Models;

use Belamov\PostgresRange\Ranges\TimestampRange;

interface RehearsalDataProvider
{
    public function id(): ?int;

    public function time(): TimestampRange;

    public function bandId(): ?int;

    public function bookedUserId(): ?int;

    public function roomId(): int;
}