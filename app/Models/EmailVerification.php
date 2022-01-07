<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
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
    protected $guarded = [];

    public static function createCodeForEmail(mixed $email): string
    {
        $code = Str::random(5);

        self::updateOrCreate(
            [
                'email' => $email,
            ],
            [
                'code' => $code
            ]
        );

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
}
