<?php

namespace App\Exceptions\User;

use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class TimeIsUnavailableForUsersException extends Exception
{
    /** @var Collection<int, User> */
    protected Collection $users;

    /**
     * @param  Collection<int, User>  $users
     */
    public function __construct(Collection $users)
    {
        parent::__construct();
        $this->users = $users;
    }

    public function render(): JsonResponse
    {
        return response()->json(
            'Пользователи '.$this->users->implode('user_name').' не доступны в это время',
            Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
