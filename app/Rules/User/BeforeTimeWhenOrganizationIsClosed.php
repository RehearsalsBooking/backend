<?php

namespace App\Rules\User;

use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class BeforeTimeWhenOrganizationIsClosed implements Rule
{
    /**
     * @var Organization
     */
    private $organization;

    /**
     * Create a new rule instance.
     *
     * @param Organization $organization
     */
    public function __construct(Organization $organization)
    {
        $this->organization = $organization;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        if (!$this->organization->closes_at) {
            return true;
        }

        $date = Carbon::make($value);
        return strtotime(optional($date)->format('H:i')) <= strtotime($this->organization->closes_at);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Rehearsal must be over before organization is closed';
    }
}
