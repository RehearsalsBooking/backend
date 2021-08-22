<?php

namespace App\Models;

use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

trait HasAvatar
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->acceptsMimeTypes(['image/jpeg', 'image/png'])
            ->singleFile()
            ->registerMediaConversions(function () {
                $this
                    ->addMediaConversion('thumb')
                    ->width(344)
                    ->height(194)
                    ->optimize();
            });
    }

    public function getAvatarUrls(): array
    {
        return [
            'original' => $this->getFirstMediaUrl('avatar'),
            'thumb' => $this->getFirstMediaUrl('avatar', 'thumb'),
        ];
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function updateAvatar(UploadedFile $file): void
    {
        $this->addMedia($file)
            ->usingFileName($file->hashName())
            ->toMediaCollection('avatar');
    }
}