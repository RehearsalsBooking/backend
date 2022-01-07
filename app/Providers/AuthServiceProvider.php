<?php

namespace App\Providers;

use App\Models\Band;
use App\Models\Invite;
use App\Models\Organization\Organization;
use App\Models\Organization\OrganizationRoom;
use App\Models\Rehearsal;
use App\Policies\Management\OrganizationPolicy;
use App\Policies\Management\OrganizationRoomPolicy;
use App\Policies\Users\BandPolicy;
use App\Policies\Users\InvitePolicy;
use App\Policies\Users\RehearsalPolicy;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Rehearsal::class => RehearsalPolicy::class,
        Band::class => BandPolicy::class,
        Invite::class => InvitePolicy::class,
        Organization::class => OrganizationPolicy::class,
        OrganizationRoom::class => OrganizationRoomPolicy::class
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        ResetPassword::createUrlUsing(static fn(
            $user,
            string $token
        ) => 'https://app.festic.ru/reset-password?token='.$token);
    }
}
