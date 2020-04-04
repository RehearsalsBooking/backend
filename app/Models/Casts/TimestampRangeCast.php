<?php


namespace App\Models\Casts;

use App\Models\Ranges\TimestampRange;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class TimestampRangeCast extends RangeCast
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
        $matches = $this->parseStringRange($value);

        return new TimestampRange($matches[2], $matches[3], $matches[1], $matches[4]);
    }

}
