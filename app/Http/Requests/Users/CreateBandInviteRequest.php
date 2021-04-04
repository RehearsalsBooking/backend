<?php

namespace App\Http\Requests\Users;

use App\Models\Band;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class CreateBandInviteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $band = Band::find($this->get('band_id'));

        // if we cant find band, let request pass authorization, it must fail in validation
        if ($band === null) {
            return true;
        }

        // see band policy for rules

        /** @var User $user */
        $user = auth()->user();

        return $user->can('manage', $band);
    }

    public function rules(): array
    {
        return [
            'band_id' => 'required|exists:bands,id',
            'user_id' => 'required|exists:users,id',
            'role' => 'sometimes|string',
        ];
    }

    public function inviteParams(): array
    {
        return [
            'user_id' => $this->get('user_id'),
            'band_id' => $this->get('band_id'),
            'role' => $this->get('role'),
        ];
    }
}
