<?php

namespace App\Http\Requests\Users;

use App\Models\Band;
use App\Rules\UserIsMemberOfBand;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBandRequest extends FormRequest
{
    public function rules(): array
    {
        /** @var Band $band */
        $band = $this->route('band');

        return [
            'name' => 'required|string',
            'admin_id' => [
                'bail',
                'sometimes',
                'integer',
                'exists:users,id',
                new UserIsMemberOfBand($band),
            ],
            'bio' => 'nullable|string',
            'genres' => 'sometimes|nullable|array',
            'genres.*' => 'integer|exists:genres,id',
        ];
    }

    public function getUpdatedBandAttributes(): array
    {
        return $this->only(['name', 'bio', 'admin_id']);
    }

    public function getBandGenres(): mixed
    {
        return $this->get('genres');
    }
}
