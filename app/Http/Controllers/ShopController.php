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



}