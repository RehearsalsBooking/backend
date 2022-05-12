<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

class TrustHosts extends Middleware
{
    public function hosts(): array
    {
        return [
            'app.festic.ru',
            'demo.festic.ru',
            'api.festic.ru',
            'rehearsals.local',
            'localhost'
        ];
    }
}