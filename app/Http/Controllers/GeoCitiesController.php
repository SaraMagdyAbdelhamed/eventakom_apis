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

    public function getcitycountry()
    {
      $cities= GeoCity::all();
      $citycounty= array();
      foreach($cities as $key=>$city){

$citycounty[$key]= $city->name.','.$city->geo_country->name;

      }
        return response()->json($citycounty);
    }


    public function searchcitycountry(Request $request)
    {
     $keyword = $request->input('keyword');
    //dd($keyword);
     
      //return;

      if ($keyword!='') {
           // $citycounty = GeoCity::where("name", "LIKE","%$keyword%")
           //        ->orWhere($citycounty->geo_country->name, "LIKE", "%$keyword%");

    $citycounty = GeoCity::where('name','like','%'.$keyword.'%')
     ->orWhereHas('geo_country', function ($query) use ($keyword) {
         $query->where('name', 'like', '%'.$keyword.'%');
     })->get();
    // $citycounty = GeoCity::where('name','like','%'.urldecode($keyword).'%')->get();
    $result= array();
    foreach($citycounty as $key=>$city){

   $result[$key]= $city->name.','.$city->geo_country->name;

    }
    if (!empty($result)) {
        return Helpers::Get_Response(200,'success','','',$result);
    }else{
        return Helpers::Get_Response(400,'error',trans('there is no result related to your input'),'','');
    }

    }else{

       return Helpers::Get_Response(401,'error',trans('please inter any chararcter'),'','');

    }
            //return response()->json($result);

    }


}
