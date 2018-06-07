<?php
/**
 * Created by PhpStorm.
 * User: pentavalue
 * Date: 6/6/2018
 * Time: 10:00 AM
 */
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Libraries\Helpers;
use App\Libraries\Base64ToImageService;
use App\User;
use App\Shop;
use App\Offer;
use App\Branch;
class ShopController extends Controller
{
    //

    /**
     * List All shops with days and branches
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list_shops(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }

        $page = array_key_exists('page',$request_data) ? $request_data['page']:1;
        $limit = array_key_exists('limit',$request_data) ? $request_data['limit']:10;

        $shops = Shop::query()
            ->with('branches.days','days')
            ->IsActive()
            ->orderBy('name','ASC')
            ->WithPaginate($page,$limit)
            ->get();
        return Helpers::Get_Response(200, 'success', '', [], $shops);
    }


    /**
     * list all offers
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list_offers(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }

        $page = array_key_exists('page',$request_data) ? $request_data['page']:1;
        $limit = array_key_exists('limit',$request_data) ? $request_data['limit']:10;

        $offers = Offer::query()
            ->orderBy('name','ASC')
            ->WithPaginate($page,$limit)
            ->get();
        return Helpers::Get_Response(200, 'success', '', [], $offers);

    }


    /**
     * list nearby shop branches according to user location within specific readius
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function nearby_branches(Request $request){

        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        //validation
        $validator = Validator::make($request_data,
            [
                "user_lat" => "required",
                "user_lng" => "required",
            ]);
        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', trans('validation.required'), $validator->errors(), []);
        }

        // Perform The Query
        $lat = $request_data['user_lat'];
        $lng = $request_data['user_lng'];
        $radius = array_key_exists('radius',$request_data) ? $request_data['radius']:100;
        $page = array_key_exists('page',$request_data) ? $request_data['page']:1;
        $limit = array_key_exists('limit',$request_data) ? $request_data['limit']:10;
        $branches = Branch::query()->Distance($lat,$lng,$radius,"km")
            ->with('days','shop')
            ->WithPaginate($page,$limit)
            ->get();
        return Helpers::Get_Response(200,'success','',[],$branches);

    }


}