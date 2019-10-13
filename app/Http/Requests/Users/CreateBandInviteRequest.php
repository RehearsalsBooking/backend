<?php

namespace App\Http\Requests\Users;

use App\Models\Band;
use Illuminate\Foundation\Http\FormRequest;

class CreateBandInviteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $band = Band::find($this->get('band_id'));

        // if we cant find band, let request pass authorization, it must fail in validation
        if (!$band) {
            return true;
        }

        // see band policy for rules
        return auth()->user()->can('invite-members', $band);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'band_id' => 'required|exists:bands,id',
            'user_id' => 'required|exists:users,id'
        ];
    }

    /**
     * @return array
     */
    public function inviteParams(): array
    {
        return [
            'user_id' => $this->get('user_id'),
            'band_id' => $this->get('band_id')
        ];
    }
}
