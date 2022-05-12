<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;
use Illuminate\Http\Request;

class TrustHosts extends Middleware
{
    public function hosts(): array
    {
        try {
            \Log::info(Request::capture()->getHost());

        } catch (\Throwable $exception){
            \Log::error($exception);
        }
        return [
            'app.festic.ru',
            'demo.festic.ru',
            'https://app.festic.ru',
            'https://demo.festic.ru',
            'rehearsals.local',
            'localhost'
        ];
    }
}