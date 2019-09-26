<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class CreateBandRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required'
        ];
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return [
            'name' => $this->get('name'),
            'admin_id' => auth()->id(),
        ];
    }
}
