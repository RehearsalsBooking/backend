<?php

namespace App\Models;

use App\Mail\EmailVerificationCode;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * App\Models\EmailVerification
 *
 * @property int $id
 * @property string $email
 * @property string $code
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|EmailVerification newModelQuery()
 * @method static Builder|EmailVerification newQuery()
 * @method static Builder|EmailVerification query()
 * @method static Builder|EmailVerification whereCode($value)
 * @method static Builder|EmailVerification whereCreatedAt($value)
 * @method static Builder|EmailVerification whereEmail($value)
 * @method static Builder|EmailVerification whereId($value)
 * @method static Builder|EmailVerification whereUpdatedAt($value)
 * @mixin Eloquent
 */
class EmailVerification extends Model
{
    public const EXPIRATION_MINUTES = 30;

    protected $guarded = [];

    public static function createCodeForEmail(mixed $email): string
    {
        /** @var EmailVerification|null $existingVerification */
        $existingVerification = self::where('email', $email)->first();

        if ($existingVerification && !$existingVerification->isExpired()) {
            return $existingVerification->code;
        }

        $code = Str::random(5);

        self::updateOrCreate(
            ['email' => $email],
            ['code' => $code]
        );

        Mail::to($email)->send(new EmailVerificationCode($code));

        return $code;
    }

    /**
     * @throws ValidationException
     */
    public static function validate(array $emailVerificationCodeData): void
    {
        if (self::where($emailVerificationCodeData)->doesntExist()) {
            throw ValidationException::withMessages(['code' => 'Неправильный код из письма']);
        }
    }

    public static function validated(array $emailVerificationCodeData): void
    {
        self::where($emailVerificationCodeData)->delete();
    }

    private function isExpired(): bool
    {
        return optional($this->created_at)->addMinutes(self::EXPIRATION_MINUTES)->isPast();
    }
}
