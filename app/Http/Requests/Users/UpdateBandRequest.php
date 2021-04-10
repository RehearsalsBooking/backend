<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBandRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string',
            'bio' => 'sometimes|string',
            'genres' => 'sometimes|array',
            'genres.*' => 'integer|exists:genres,id',
        ];
    }

    public function getUpdatedBandAttributes(): array
    {
        return $this->only(['name', 'bio']);
    }

    public function getBandGenres(): mixed
    {
        return $this->get('genres');
    }
}
