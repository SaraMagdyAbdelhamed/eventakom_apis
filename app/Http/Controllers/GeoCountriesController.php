<?php
namespace App\Http\Controllers;

use App\GeoCountry;
use Illuminate\Http\Request;
use App\Libraries\Helpers;
use Illuminate\Support\Facades\Validator;
class GeoCountriesController extends Controller
{

    public function getAllCountries(Request $request)
    {
    
        $countries = GeoCountry::all();

        //arabic 

    //      $countries = DB::table('geo_countries')
    // ->join('entities', 'entities.id', '=', 'shares.user_id')
    // ->join('follows', 'follows.user_id', '=', 'users.id')
    // ->where('follows.follower_id', '=', 3)
    // ->get();


        if(!empty($countries)){
        	$countries = $countries ;
        }else{$countries = array();}
        return Helpers::Get_Response(200, 'success', '', '',$countries);
    }


}