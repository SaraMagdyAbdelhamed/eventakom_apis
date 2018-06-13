<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Libraries\Helpers;
use App\Libraries\Base64ToImageService;
use App\User;
use App\FamousAttraction;
use App\FamousAttractionCategory;

class FamousAttractionsController extends Controller
{

    /**
     * list famous attractions 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list_famous_attractions(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        $page = array_key_exists('page',$request_data) ? $request_data['page']:1;
        $limit = array_key_exists('limit',$request_data) ? $request_data['limit']:10;

         $famous_attractions = FamousAttraction::query()
            ->with('categories','days','media')
            ->IsActive()
            ->orderBy('name','ASC')
            ->WithPaginate($page,$limit)
            ->get();
        return Helpers::Get_Response(200, 'success', '', [], $famous_attractions);

    }

    /**
     * list nearby famous attractions 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function nearby_famous_attractions(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        

        // Perform The Query
        $lat    = env('JEDDAH_LATITUDE');//get Default locaion of JEDDAH if GPS of user is off
        $lng    = env('JEDDAH_LONGITUDE');

        if(array_key_exists('user_lat',$request_data))
        {
            if($request_data['user_lat'] != ""){
                $lat = $request_data["user_lat"];
            }

        }
        if(array_key_exists('user_lng',$request_data))
        {
            if($request_data['user_lng'] != ""){
                $lng = $request_data["user_lng"];
            }

        }
        $radius = array_key_exists('radius'  ,$request_data) ? $request_data['radius']:1000;
        $page   = array_key_exists('page'    ,$request_data) ? $request_data['page']:1;
        $limit  = array_key_exists('limit'   ,$request_data) ? $request_data['limit']:10;
        $famous_attractions = FamousAttraction::query()->Distance($lat,$lng,$radius,"km")
            ->with("categories","days")
            ->IsActive()
            ->WithPaginate($page,$limit)
            ->get();
        return Helpers::Get_Response(200, 'success', '', [], $famous_attractions);

    }



    public function famous_attractions_categories(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        //pagination 
        $page   = array_key_exists('page'    ,$request_data) ? $request_data['page']:1;
        $limit  = array_key_exists('limit'   ,$request_data) ? $request_data['limit']:10;

        // perfrom query
        $fa_categories = FamousAttractionCategory::query()->WithPaginate($page,$limit)->get();
        return Helpers::Get_Response(200, 'success', '', [], $fa_categories);
    }


}