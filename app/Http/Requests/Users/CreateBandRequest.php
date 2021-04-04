<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class CreateBandRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'bio' => 'sometimes|string',
            'genres' => 'sometimes|array',
            'genres.*' => 'integer|exists:band_genres,id',
        ];
    }

    public function getAttributes(): array
    {
        return [
            'name' => $this->get('name'),
            'bio' => $this->get('bio'),
            'admin_id' => auth()->id(),
        ];
    }

    public function getBandGenres()
    {
        return $this->get('genres');
    }
}
