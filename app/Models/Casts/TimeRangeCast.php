<?php


namespace App\Models\Casts;

use App\Models\Ranges\TimeRange;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class TimeRangeCast extends RangeCast
{
    /**
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return TimeRange
     */
    public function get($model, $key, $value, $attributes): TimeRange
    {
        $matches = $this->parseStringRange($value);

        return new TimeRange($matches[2], $matches[3], $matches[1], $matches[4]);
    }

}
