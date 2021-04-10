<?php

namespace Tests\Feature;

use App\Http\Resources\Users\GenreResource;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenresTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fetches_genres(): void
    {
        $genresCount = 3;
        $genres = Genre::factory()->count($genresCount)->create();
        $response = $this->json('get', route('genres.index'));
        $response->assertOk();
        $this->assertCount($genresCount, $response->json('data'));
        $this->assertEquals(
            GenreResource::collection($genres)->response()->getData(true),
            $response->json()
        );
    }

}
