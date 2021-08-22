<?php

namespace App\Http\Requests\Management;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class UpdateOrganizationAvatarRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'avatar' => 'required|file|mimes:jpg,bmp,png',
        ];
    }

    public function getAvatarFile(): UploadedFile
    {
        /** @phpstan-ignore-next-line */
        return $this->file('avatar');
    }
}
