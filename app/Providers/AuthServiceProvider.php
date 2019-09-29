<?php

namespace App\Providers;

use App\Models\Band;
use App\Models\Rehearsal;
use App\Policies\Users\BandPolicy;
use App\Policies\Users\RehearsalPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Rehearsal::class => RehearsalPolicy::class,
        Band::class => BandPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
