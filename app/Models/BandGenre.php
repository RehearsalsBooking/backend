<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\BandGenre
 *
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Band[] $bands
 * @property-read int|null $bands_count
 * @method static Builder|BandGenre newModelQuery()
 * @method static Builder|BandGenre newQuery()
 * @method static Builder|BandGenre query()
 * @method static Builder|BandGenre whereCreatedAt($value)
 * @method static Builder|BandGenre whereId($value)
 * @method static Builder|BandGenre whereName($value)
 * @method static Builder|BandGenre whereUpdatedAt($value)
 * @mixin Eloquent
 */
class BandGenre extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    public function bands(): BelongsToMany
    {
        return $this->belongsToMany(
            Band::class,
            'bands_genres',
            'genre_id',
            'band_id'
        );
    }

}
