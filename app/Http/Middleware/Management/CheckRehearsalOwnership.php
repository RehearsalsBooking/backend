<?php

namespace App\Http\Middleware\Management;

use App\Models\Rehearsal;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Class CheckRehearsalOwnership
 *
 * Determines if given rehearsal is booked in organization that is managed by logged in user
 *
 * @package App\Http\Middleware\Management
 */
class CheckRehearsalOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /** @var Rehearsal $rehearsal */
        $rehearsal = $request->route()->parameter('rehearsal');

        /** @var User $user */
        $user = auth()->user();

        if (!$user->organizations->contains($rehearsal->organization)) {
            return response()->json('You cannot manage this rehearsal', Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
