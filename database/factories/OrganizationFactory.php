<?php

namespace Database\Factories;

use App\Models\Organization\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'address' => $this->faker->address,
            'gear' => $this->faker->paragraph,
            'coordinates' => $this->getOrganizationCoordinates(),
            'is_active' => true,
            'avatar' => 'https://picsum.photos/300/200',
            'owner_id' => User::factory(),
        ];
    }

    /**
     * restrict random coordinates of organizations to be
     * in 5km radius of (57.144179, 65.553132) (somewhere in my city)
     * https://stackoverflow.com/questions/3067530/how-can-i-get-minimum-and-maximum-latitude-and-longitude-using-current-location
     *
     * @return string
     */
    private function getOrganizationCoordinates(): string
    {
        $latitude = 57.144179;
        $longitude = 65.553132;
        $radiusInKM = 5.00;
        $minLat = $latitude - ($radiusInKM / 111.12);
        $maxLat = $latitude + ($radiusInKM / 111.12);
        $minLon = $longitude - ($radiusInKM) / abs(cos($longitude / 180.0 * M_PI) * 111.12);
        $maxLon = $longitude + ($radiusInKM) / abs(cos($longitude / 180.0 * M_PI) * 111.12);

        /** @noinspection PhpUndefinedMethodInspection */
        return "({$this->faker->latitude($minLat, $maxLat)},{$this->faker->longitude($minLon, $maxLon)})";
    }
}