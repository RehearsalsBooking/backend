<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\Users\GenreResource;
use App\Models\Genre;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GenresController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return GenreResource::collection(Genre::all());
    }

}
