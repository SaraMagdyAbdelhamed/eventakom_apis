<?php
/**
 * Created by PhpStorm.
 * User: pentavalue
 * Date: 6/6/2018
 * Time: 10:00 AM
 */
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            ->with('branches.days','days','media')
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
            ->IsActive()
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
        $radius = array_key_exists('radius',$request_data) ? $request_data['radius']:50;
        $page = array_key_exists('page',$request_data) ? $request_data['page']:1;
        $limit = array_key_exists('limit',$request_data) ? $request_data['limit']:10;
        $branches = Branch::query()->Distance($lat,$lng,$radius,"km")
            ->with('days','shop')
            ->WithPaginate($page,$limit)
            ->get();
        return Helpers::Get_Response(200,'success','',[],$branches);
    }

    /**
    * list shop details 
    * @param Request $request
    * @return \Illuminate\Http\JsonResponse
    */

    public function shop_details(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        $validator = Validator::make($request_data,
            [
                'shop_id' => 'required'

            ]);
        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', trans('validation.required'), $validator->errors(), []);
        }
        $shop_detail = Shop::query()
            ->where("id",$request_data["shop_id"])
            ->with('branches.days','days')
            ->get();
        return Helpers::Get_Response(200, 'success', '', [], $shop_detail);
    }

    /**
     * add shop to favourite
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
    */

    public function add_shop_favourite(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        $validator = Validator::make($request_data,
            [
                "shop_id"   => "required",
                "name"      => "required"
            ]);
        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', trans('validation.required'), $validator->errors(), []);
        }
        // insert in user_favourite Table
        $user = User::where("api_token", "=", $request->header('access-token'))->first();
        // check if its in user favouirte so remove it and return []
        $check = DB::table('user_favorites')
                ->where([
                    ['user_id' , '=' , $user->id],
                    ['item_id' , '=',$request_data['shop_id']],
                    ['entity_id','=',10]
                    ]);
        if(!$check->get()->isEmpty()){
            $check->delete();
            return Helpers::Get_Response(200,'deleted','',[],[]);
        }
        $insert = DB::table('user_favorites')->insert([
            'name'        => $request_data['name'],
            'user_id'     => $user->id,
            'item_id'     => $request_data['shop_id'],
            'entity_id'   => 10

        ]);
        if(!$insert){
            return Helpers::Get_Response(401,'failed','Error in saving',[],[]);

        }
        return Helpers::Get_Response(200,'success','',[],[]);
    }


}