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
        $lang_id = $request->input('lang_id');
        $locale =Helpers::Set_locale($lang_id);
        if(!empty($countries)){
//         	$countries = $countries;
//          //    foreach($countries as $country){
//          //          if( $lang_id == 1){
//          // $country->name =  $country->name;

//          //          }elseif( $lang_id == 2){
//          //        $countryname =  Helpers::localization('geo_countries', 'name', $country->id, $lang_id );
//          //        if($countryname == "Error"){$country->name =  $country->name;
//          //        }else{
//          //            $country->name = $countryname;
//          //        }
//          //    }
//          //    }

// foreach($countries as $country){

// $country->name= $country->getNameAttribute($country->name);

//       }
        }else{$countries = array();}
        return Helpers::Get_Response(200, 'success', '', '',$countries);
    }


}