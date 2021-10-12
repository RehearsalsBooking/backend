<?php

namespace App\Http\Controllers;

use App\Http\Resources\CityResource;
use App\Models\City;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CitiesController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return CityResource::collection(City::all());
    }

}
