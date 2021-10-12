<?php

namespace Tests\Feature;

use App\Http\Resources\CityResource;
use App\Http\Resources\Users\GenreResource;
use App\Models\City;
use Tests\TestCase;

class CitiesTest extends TestCase
{

    /** @test */
    public function it_fetches_cities(): void
    {
        $citiesCount = 3;
        $cities = City::factory()->count($citiesCount)->create();
        $response = $this->json('get', route('cities.index'));
        $response->assertOk();
        $this->assertCount($citiesCount, $response->json('data'));
        $this->assertEquals(
            CityResource::collection($cities)->response()->getData(true),
            $response->json()
        );
    }
}
