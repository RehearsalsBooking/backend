<?php

namespace App\Rules;

use App\Models\Band;
use Illuminate\Contracts\Validation\Rule;

class UserIsMemberOfBand implements Rule
{
    protected Band $band;

    public function __construct(Band $band)
    {
        $this->band = $band;
    }

    public function passes($attribute, $value): bool
    {
        return $this->band->members()->where('user_id', $value)->exists();
    }

    public function message(): string
    {
        return 'Новый админ должен быть участником группы';
    }
}
