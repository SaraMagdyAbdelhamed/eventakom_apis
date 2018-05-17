<?php
namespace App\Http\Controllers;

use App\GeoCountry;
use Illuminate\Http\Request;

class GeoCountriesController extends Controller
{

    public function getAllCountries(Request $request)
    {
    
        return response()->json(GeoCountry::all());
    }


}