<?php


namespace App\Models\Casts;

use App\Models\TimestampRange;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class TsRangeCast implements CastsAttributes
{
    /**
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return TimestampRange
     */
    public function get($model, $key, $value, $attributes): TimestampRange
    {
        preg_match('/([\[\(])(.*)\,(.*)([\]\)])/', $value, $matches);

        return new TimestampRange($matches[2], $matches[3], $matches[1], $matches[4]);
    }

    /**
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return array
     */
    public function set($model, $key, $value, $attributes): array
    {
        return [
            $key => $this->serializeRange($value)
        ];
    }

    /**
     * @param TimestampRange $range
     * @return string
     */
    private function serializeRange(TimestampRange $range): string
    {
        return $range->__toString();
    }
}
