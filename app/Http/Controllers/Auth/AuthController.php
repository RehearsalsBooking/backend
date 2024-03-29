<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\User\InvalidValidationCodeForEmail;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmailVerificationRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegistrationRequest;
use App\Http\Resources\Users\LoggedUserResource;
use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

use function app;
use function auth;
use function response;
use function session;

class AuthController extends Controller
{
    public function me(): LoggedUserResource
    {
        return new LoggedUserResource(auth()->user());
    }

    public function logout(): JsonResponse
    {
        auth('web')->logout();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function test(): JsonResponse|LoggedUserResource
    {
        if (app()->environment('production')) {
            return response()->json([], 404);
        }

        $user = User::firstOrCreate(['email' => 'demo@festic.ru'], ['name' => 'test user']);

        auth('web')->login($user);

        return response()->json(new LoggedUserResource($user), Response::HTTP_OK);
    }

    /**
     * @throws ValidationException
     * @throws InvalidValidationCodeForEmail
     * @throws Throwable
     */
    public function registration(RegistrationRequest $request): JsonResponse
    {
        EmailVerification::validate($request->getEmailConfirmationCode());

        $newUser = DB::transaction(function () use ($request) {
            EmailVerification::validated($request->getEmailConfirmationCode());
            return User::create($request->getUserAttributes());
        });

        auth('web')->login($newUser);

        return response()->json(new LoggedUserResource($newUser), Response::HTTP_CREATED);
    }

    public function emailVerification(EmailVerificationRequest $request): JsonResponse
    {
        EmailVerification::createCodeForEmail($request->getEmail());

        return response()->json(null, Response::HTTP_CREATED);
    }

    /**
     * @throws ValidationException
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (auth('web')->attempt(
            $request->getCredentials(),
            $request->doRemember())
        ) {
            session()->regenerate();

            return response()->json(new LoggedUserResource(auth()->user()), Response::HTTP_OK);
        }

        throw ValidationException::withMessages(['login' => 'Неправильные логин или пароль']);
    }
}
