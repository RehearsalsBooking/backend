<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\User\PasswordResetException;
use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordRecoverRequest;
use App\Http\Requests\SendPasswordRecoverLinkRequest;
use Exception;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * @throws Exception
     */
    public function sendRecoverPasswordLink(
        SendPasswordRecoverLinkRequest $request,
        PasswordBroker $passwordBroker
    ): JsonResponse {
        $status = $passwordBroker->sendResetLink(
            ['email' => $request->getEmail()]
        );

        return match ($status) {
            PasswordBroker::RESET_LINK_SENT => response()->json(
                null,
                Response::HTTP_CREATED
            ),
            PasswordBroker::RESET_THROTTLED => response()->json(
                'Письмо для восстановления пароля уже отправлено. Проверьте папку спама или повторите попытку чуть позже',
                Response::HTTP_UNPROCESSABLE_ENTITY
            ),
            PasswordBroker::INVALID_USER => response()->json(
                'Пользователь с такой почтой не зарегистрирован',
                Response::HTTP_UNPROCESSABLE_ENTITY
            ),
            default => throw new PasswordResetException('Unexpected password reset status: '.$status)
        };
    }

    /**
     * @throws Exception
     */
    public function recoverPassword(
        PasswordRecoverRequest $request,
        PasswordBroker $passwordBroker,
    ): JsonResponse {
        $status = $passwordBroker->reset(
            $request->getCredentials(),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        return match ($status) {
            PasswordBroker::INVALID_USER => response()->json(
                'Пользователь с такой почтой не зарегистрирован',
                Response::HTTP_UNPROCESSABLE_ENTITY
            ),
            PasswordBroker::INVALID_TOKEN => response()->json(
                'Неверный токен',
                Response::HTTP_UNPROCESSABLE_ENTITY
            ),
            PasswordBroker::PASSWORD_RESET => response()->json(
                null,
                Response::HTTP_OK
            ),
            default => throw new PasswordResetException('Unexpected password reset status: '.$status)
        };
    }
}
