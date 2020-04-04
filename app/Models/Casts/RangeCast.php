<?php


namespace App\Models\Casts;


use App\Models\Ranges\SerializesRange;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

abstract class RangeCast implements CastsAttributes
{
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
     * @param SerializesRange $range
     * @return string
     */
    protected function serializeRange(SerializesRange $range): string
    {
        return $range->__toString();
    }

    /**
     * @param $value
     * @return array
     */
    protected function parseStringRange($value): array
    {
        $matches = [];
        preg_match('/([\[(])(.*),(.*)([])])/', $value, $matches);
        return $matches;
    }
}
