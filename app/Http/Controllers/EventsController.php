<?php

namespace App\Http\Controllers;
use App\User;
use App\Interest;
use App\Event;
use App\HashTag;
use App\GeoCity;
use App\user_rule;
use App\AgeRange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Libraries\Helpers;
use App\Libraries\Base64ToImageService;
use Illuminate\Support\Facades\Hash;
use App\Libraries\TwilioSmsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use \Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
/**
 * Class EventsController
 * @package App\Http\Controllers
 */
class EventsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public  function event_details(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        $validator = Validator::make($request_data,
            [
                'event_id' =>'required'

            ]);
        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', trans('validation.required'), $validator->errors(), []);
        }
        $event = Event::query()
            ->where('id',$request_data['event_id'])
            ->With('prices.currency')
            ->with('categories')
            ->with('hash_tags')
            ->get();
        // Get You May Also Like
        $category_ids = Event::find($request_data['event_id'])->categories->pluck('pivot.interest_id');
        $d = DB::table('events')
            ->leftJoin('event_categories','events.id','=','event_categories.event_id')
            ->whereIn('event_categories.interest_id',$category_ids)
            ->select('events.*')->distinct()
            ->get();

        return Helpers::Get_Response(200, 'success', 'saved', [], ['event'=>$event,'you_may_also_like'=>$d]);




    }

    /**
     * add new event
     * @param Request $request
     * @return  \Illuminate\Http\JsonResponse
     */


    public function add_event(Request $request){


        //read the request
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        $validator = Validator::make($request_data,
            [
                "name"             => "required|between:2,100",
                "description"      => "required|between:2,250",
                "venue"            => "required|between:2,100",
                'hashtags'         =>"between:2,250",
                "gender_id"        => "required",
                'start_datetime'   => 'required',
                'end_datetime'     => 'required',
                'longtuide'        => 'required',
                'latitude'         => 'required'

            ]);
        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', trans('validation.required'), $validator->errors(), []);
        }

        $event_data = [
            'name'          =>$request_data['name'],
            'description'   =>$request_data['description'],
            'venue'         =>$request_data['venue'],
            'gender_id'     =>$request_data['gender_id'],
            'start_datetime'=>date('Y-m-d H:i:s',$request_data['start_datetime']),
            'end_datetime'  =>date('Y-m-d H:i:s',$request_data['end_datetime']),
            'is_active'     =>0,
            'show_in_mobile'=>0,
            'created_by'    =>User::where('api_token','=',$request->header('access-token'))->first()->id,
            'age_range_id'  =>array_key_exists('age_gender_id',$request_data) ?$request_data['age_gender_id']:NULL,
            'longtuide'     => $request_data['longtuide'],
            'latitude'      => $request_data['latitude']

        ];

        //save the event
        $event = Event::create($event_data);
        if(!$event){
            return Helpers::Get_Response(403, 'error', 'not saved',[], []);


        }

        //read the hashtags and save it ;
        if(array_key_exists('hashtags',$request_data)){
            $hashtags = explode(',',$request_data['hashtags']);
            foreach ($hashtags as $hashtag){
                $hash_tag = HashTag::firstOrCreate(['name'=>$hashtag]);
                $event->hash_tags()->save($hash_tag);

            }

        }

        //read event categories and save it
        if(array_key_exists('categories',$request_data)){
            $categories = explode(',',$request_data['categories']);
            foreach ($categories as $category){
                $event_category = Interest::firstOrCreate(['name'=>$category]);
                $event->categories()->save($event_category);

            }

        }



        return Helpers::Get_Response(200, 'success', 'saved', [], Event::latest()->with('hash_tags','categories')->first());




    }



    /**
     * list all  upcoming and past events
     * @param Request $request
     * @param null $type
     * @return \Illuminate\Http\JsonResponse
     */
    public function list_events(Request $request,$type=NULL){
        // read the request
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }

        //Validate
        $validator = Validator::make($request_data,
            [
                "interest_id" => "required"
               
            ]);
        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', trans('validation.required'), $validator->errors(), []);
        }

        $interest = Interest::find($request_data['interest_id']);
        if(!$interest){
            return Helpers::Get_Response(403, 'error', trans('messages.interest_not_found'),[], []);
        }
        $events = $interest->events()->with('prices.currency')->with('categories')->with('hash_tags')->IsActive()->ShowInMobile();
        switch ($type) {
            case 'upcoming':
                $data = $events->UpcomingEvents();
                break;
            
            default:
                $data = $events->PastEvents();
                break;
        }

        $page = array_key_exists('page',$request_data) ? $request_data['page']:1;
        $limit = array_key_exists('limit',$request_data) ? $request_data['limit']:10;


        $result =$data->WithPaginate($page,$limit)->get();
      
        
        return Helpers::Get_Response(200, 'success', '', '',$result);

    }


    /**
     * list all past and upcoming big events
     * @param Request $request
     * @param null $type
     * @return \Illuminate\Http\JsonResponse
     */

    public function big_events(Request $request,$type=NULL){
        // read the request
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }

        //Validate
        $validator = Validator::make($request_data,
            [
                "interest_id" => "required"

            ]);
        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', trans('validation.required'), $validator->errors(), []);
        }

        $events = Event::BigEvents()->orderBy('sort_order')->with('prices.currency')->with('hash_tags')->with('categories')->IsActive()->ShowInMobile();
        switch ($type) {
            case 'upcoming':
                $data = $events->UpcomingEvents();
                break;

            default:
                $data = $events->PastEvents();
                break;
        }

        $page = array_key_exists('page',$request_data) ? $request_data['page']:1;
        $limit = array_key_exists('limit',$request_data) ? $request_data['limit']:10;


        $result =$data->WithPaginate($page,$limit)->get();



        return Helpers::Get_Response(200, 'success', '', '',$result);

    }

    /**
     * list all age ranges
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */


    public function age_ranges(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        return Helpers::Get_Response(200,'success','',[],AgeRange::all());


    }

    /**
     * this will reutrn all events in this month form today to the end of month and all events in the next month
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public  function current_month_events(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        $page = array_key_exists('page',$request_data) ? $request_data['page']:1;
        $limit = array_key_exists('limit',$request_data) ? $request_data['limit']:10;


        $this_month = Event::query()
            ->with('prices.currency')
            ->with('categories')
            ->with('hash_tags')
            ->IsActive()
            ->ShowInMobile()
            ->ThisMonthEvents()
            ->WithPaginate($page,$limit)
            ->orderBy('end_datetime','DESC')
            ->get();
        $next_month = Event::query()
            ->with('prices.currency')
            ->with('categories')
            ->with('hash_tags')
            ->IsActive()
            ->ShowInMobile()
            ->NextMonthEvents()
            ->WithPaginate($page,$limit)
            ->orderBy('end_datetime','DESC')
            ->get();
        $start_to_today = Event::query()
            ->with('prices.currency')
            ->with('categories')
            ->with('hash_tags')
            ->IsActive()
            ->ShowInMobile()
            ->StartOfMothEvents()
            ->WithPaginate($page,$limit)
            ->orderBy('end_datetime','DESC')
            ->get();

        $result = [
            'start_of_month_to_today'          => $start_to_today,
            'start_of_today_to_end'            => $this_month,
            'next_month'                       => $next_month


        ];
        return Helpers::Get_Response(200,'success','',[],$result);

    }

}
