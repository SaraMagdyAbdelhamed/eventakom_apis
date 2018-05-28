<?php
namespace App\Http\Controllers;

use App\GeoCity;
use Illuminate\Http\Request;
use App\Libraries\Helpers;
class GeoCitiesController extends Controller
{

    public function getAllCities()
    {
        return response()->json(GeoCity::all());
    }

    public function getcitycountry(Request $request)
    {
    $lang_id = $request->input('lang_id');
    $locale =Helpers::Set_locale($lang_id);
    //dd($lang_id);
      $cities= GeoCity::all();
      $citycounty= array();
      foreach($cities as $key=>$city){

// $citycounty[$key]= $city->name.','.$city->geo_country->name;
$citycounty[$key]= $city->getNameAttribute($city->name).','.$city->geo_country->getNameAttribute($city->geo_country->name);

      }
        return response()->json($citycounty);
    }


    public function searchcitycountry(Request $request)
    {
     $lang_id = $request->input('lang_id');
     $keyword = $request->input('keyword');
    //dd($keyword);
      $citycounty= array();
    $locale =Helpers::Set_locale($lang_id);
      //return;

      if ($keyword!='') {
           // $citycounty = GeoCity::where("name", "LIKE","%$keyword%")
           //        ->orWhere($citycounty->geo_country->name, "LIKE", "%$keyword%");
   if($lang_id ==1){
    $citycounty = GeoCity::where('name','like','%'.$keyword.'%')
     ->orWhereHas('geo_country', function ($query) use ($keyword) {
         $query->where('name', 'like', '%'.$keyword.'%');
     })->get();
     // dd($citycounty);
     foreach($citycounty as $city){

     $city->name= $city->getNameAttribute($city->name).','.$city->geo_country->getNameAttribute($city->geo_country->name);
// dd($result);
     }
   }elseif($lang_id ==2){
  //$citycounty = GeoCity::city_entity_ar()->where('entity_localizations.value','like','%'.$keyword.'%')->get(); city works
  $citycounty = GeoCity::city_entity_ar()->where('entity_localizations.value','like','%'.$keyword.'%')
   ->orWhereHas('geo_country', function ($query) use ($keyword) {
     $query->join('entity_localizations','geo_countries.id','=','entity_localizations.item_id')
     ->where('entity_id','=',8)->where('field','=','name')
     ->select('geo_countries.*','entity_localizations.value')->where('entity_localizations.value','like','%'.$keyword.'%');
   })->get();
   foreach($citycounty as $city){
   
   $city->value= $city->getNameAttribute($city->name).','.$city->geo_country->getNameAttribute($city->geo_country->name);


   }

   }
    // $citycounty = GeoCity::where('name','like','%'.urldecode($keyword).'%')->get();


    if (!empty($citycounty[0])) {
        return Helpers::Get_Response(200,'success','','',$citycounty);
    }else{
        return Helpers::Get_Response(400,'error',trans('there is no result related to your input'),'',[]);
    }

    }else{

       return Helpers::Get_Response(401,'error',trans('please inter any chararcter'),'',[]);

    }
            //return response()->json($result);

    }


}
