<?php

namespace App\Http\Controllers;
use App\Currency;
use App\PostReply;
use App\User;
use App\Interest;
use App\Event;
use App\HashTag;
use App\Gender;
use App\TrendingKeyword;
use App\GeoCity;
use App\user_rule;
use App\AgeRange;
use App\EventPost;
use App\Price;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Libraries\Helpers;
use App\Libraries\TwitterSearchApi;
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

    /**
     * Return Event Details by event id and return recommended events realted to this event
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public  function event_details(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        $validator = Validator::make($request_data,
            [
                'event_id' => 'required'

            ]);
        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', trans('validation.required'), $validator->errors(), []);
        }
        //$user = User::where('api_token',$request->header('access-token'))->first()->id;
        $event = Event::query()
            ->where('id',$request_data['event_id'])
            ->with('prices.currency','categories','hash_tags','media','posts.replies')
            ->withCount('GoingUsers')
            ->get();

        // Get You May Also Like
        if($event->isEmpty()){
            return Helpers::Get_Response(403, 'error', 'not found', [], []);
        }
        $category_ids = Event::find($request_data['event_id'])->categories->pluck('pivot.interest_id');
        $random = array_key_exists('random_limit',$request_data) ? $request_data['random_limit'] :10;
        $count = Event::EventsInCategories($category_ids)->get()->count();
        if($count < 10){
            $result = Event::EventsInCategories($category_ids)->get()->random($count);

        }else{
            $result = Event::EventsInCategories($category_ids)->get()->random($random);

        }
        return Helpers::Get_Response(200, 'success', '', [], [['event'=>$event,'you_may_also_like'=>$result]]);

    }

    /**
     * add new event
     * @param Request $request
     * @return  \Illuminate\Http\JsonResponse
     */

    public function add_event(Request $request){
        //read the request
        $request_data = (array)json_decode($request->getContent(), true);

        $validator = Validator::make($request_data,
            [
                "name"             => "required|between:2,100",
                "tele_code"        => "required",
                "mobile"           => 'required|numeric',
                "description"      => "required|between:2,250",
                "venue"            => "required|between:2,100",
                'hashtags'         => "between:2,118",
                'age_range_id'     => 'required',
                "gender_id"        => "required",
                'start_datetime'   => 'required',
                'end_datetime'     => 'required',
                'longtuide'        => 'required',
                'latitude'         => 'required',
                'email'            => 'email|max:35',
                'website'          => 'between:10,50',
                'photos'           => 'array|max:5',
                'videos'           => 'array|max:2',
                'tickets'          => 'array',
                "categories"       => 'array'

            ]);
        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', trans('validation.required'), $validator->errors(), []);
        }




        $event_data = [
            'name'              => $request_data['name'],
            'description'       => $request_data['description'],
            'venue'             => $request_data['venue'],
            'gender_id'         => $request_data['gender_id'],
            'start_datetime'    => date('Y-m-d H:i:s',$request_data['start_datetime']),
            'end_datetime'      => date('Y-m-d H:i:s',$request_data['end_datetime']),
            'is_active'         => 0,
            'show_in_mobile'    => 1,
            'created_by'        => User::where('api_token','=',$request->header('access-token'))->first()->id,
            'age_range_id'      => $request_data['age_range_id'],
            'longtuide'         => $request_data['longtuide'],
            'latitude'          => $request_data['latitude'],
            'email'             => array_key_exists('email',$request_data) ? $request_data['email']: NULL,
            'website'           => array_key_exists('website',$request_data) ? $request_data['website']: NULL,
            'mobile'            => $request_data['mobile'],
            'event_status_id'   => 1,
            'is_backend'        => 0,
            "tele_code"         => $request_data["tele_code"],
            "is_paid"           => array_key_exists('is_paid',$request_data) ? $request_data['is_paid']: 0,
            "use_ticketing_system" => array_key_exists('use_ticketing_system',$request_data) ? $request_data['use_ticketing_system']: 0

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
         $event->categories()->sync($request_data['categories']);

        }

        // Save Tickets
        if(array_key_exists('tickets',$request_data)) {
            foreach ($request_data['tickets'] as $ticket) {

                $data = [
                  'name'                 => array_key_exists('name',$ticket) ? $ticket['name'] : NULL ,
                  'price'                => array_key_exists('price',$ticket) ? $ticket['price'] : NULL,
                  'available_tickets'    => array_key_exists('available_tickets',$ticket) ? $ticket['available_tickets'] : NULL,
                  'current_available_tickets' =>array_key_exists('available_tickets',$ticket) ? $ticket['available_tickets'] : NULL,
                  'currency_id'         => array_key_exists('currency_id',$ticket) ? $ticket['currency_id'] : NULL

                ];
                $event->prices()->create($data);
            }
        }

        //Save Images and Check for Sizes
        if(array_key_exists('photos',$request_data)){
            foreach ($request_data['photos'] as $photo){
                $photo_data =[
                    'link'  => Base64ToImageService::convert($photo, 'img/events/'),
                    'type'  => 1
                    ];
                $event->media()->create($photo_data);
            }
        }
        //Check for videos
        if(array_key_exists('videos',$request_data)){
            foreach ($request_data['videos'] as $video){
                $video_data =[
                    'link'  => $video,
                    'type'  => 2
                ];
                $event->media()->create($video_data);
            }

        }

        //Save Translation
        //check if arabic
        if(array_key_exists('lang_id',$request_data)){
            Helpers::Set_locale($request_data['lang_id']);
            if($request_data['lang_id'] == 2){ // Arabic data so i excpect data will be arabic in this case
                //save data in entity localisation
                // name - descirption -venue
                $save_translattion = DB::table('entity_localizations')->insert([
                    [
                        'entity_id'     => 4,
                        'field'         => 'name',
                        'item_id'       => $event->id,
                        'value'         => $request_data['name'],
                        'lang_id'       => 2,
                        'cleared_text'  => Helpers::CleanText($request_data['name'])

                    ],
                    [
                        'entity_id'     => 4,
                        'field'         => 'description',
                        'item_id'       => $event->id,
                        'value'         => $request_data['description'],
                        'lang_id'       => 2,
                        'cleared_text'  => Helpers::CleanText($request_data['description'])

                    ],
                    [
                        'entity_id'     => 4,
                        'field'         => 'venue',
                        'item_id'       => $event->id,
                        'value'         => $request_data['venue'],
                        'lang_id'       => 2,
                        'cleared_text'  => Helpers::CleanText($request_data['venue'])

                    ]

                ]);

            }

        }
        return Helpers::Get_Response(200, 'success', 'saved', [], [Event::latest()->with(['prices.currency','hash_tags','categories'])->first()]);
    }

    /**
     * Edit Events for event owner only and return unauthorized in case of wrong event owner
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public  function  edit_event(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        $user = User::where('api_token','=',$request->header('access-token'))->first();

        // validation
        $validator = Validator::make($request_data,
            [
                "event_id"         =>'required',
                "name"             => "between:2,100",
                "mobile"           => 'numeric',
                "description"      => "between:2,250",
                "venue"            => "between:2,100",
                'hashtags'         => "between:2,118",
                'email'            => 'email|max:35',
                'website'          => 'between:10,50',
                'photos'           => 'array|max:5',
                'videos'           => 'array|max:2',
                'tickets'          => 'array'

            ]);
        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', trans('validation.required'), $validator->errors(), []);
        }
        $user_id = User::where('api_token','=',$request->header('access-token'))->first()->id;

        // update main events info
        $event = Event::find($request_data['event_id']);
        if(!$event){
            return Helpers::Get_Response(403, 'error', 'not found', [], []);
        }

        if($event->created_by == $user_id){
            $event_data = [
                'name'              => array_key_exists('name',$request_data)? $request_data['name']: $event->name,
                'description'       => array_key_exists('description',$request_data)? $request_data['description']: $event->description,
                'venue'             => array_key_exists('venue',$request_data)? $request_data['venue']: $event->venue,
                'gender_id'         => array_key_exists('gender_id',$request_data)? $request_data['gender_id']: $event->gender_id,
                'start_datetime'    => array_key_exists('start_datetime',$request_data)? date('Y-m-d H:i:s',$request_data['start_datetime']): $event->start_datetime,
                'end_datetime'      => array_key_exists('end_datetime',$request_data)? date('Y-m-d H:i:s',$request_data['end_datetime']): $event->end_datetime,
                'updated_by'        => $user_id,
                'age_range_id'      => array_key_exists('age_gender_id',$request_data) ?$request_data['age_gender_id']:$event->age_range_id,
                'longtuide'         => array_key_exists('longtuide',$request_data) ?$request_data['longtuide']:$event->longtuide,
                'latitude'          => array_key_exists('latitude',$request_data) ?$request_data['latitude']:$event->latitude,
                'email'             => array_key_exists('email',$request_data) ? $request_data['email']: $event->email,
                'website'           => array_key_exists('website',$request_data) ? $request_data['website']: $event->website,
                'mobile'            => array_key_exists('mobile',$request_data) ? $request_data['mobile']: $event->mobile,
                'tele_code'         => array_key_exists('tele_code',$request_data) ? $request_data['tele_code']: $event->tele_code,
                'event_status_id'   => 1
            ];
            $event->update($event_data);
            if(array_key_exists('photos',$request_data)){
                foreach ($request_data['photos'] as $photo){
                    $photo_data =[
                        'link'  => Base64ToImageService::convert($photo, 'img/events/'),
                        'type'  => 1
                    ];
                    $event->media()->update($photo_data);
                }
            }
            //Check for videos
            if(array_key_exists('videos',$request_data)){
                foreach ($request_data['videos'] as $video){
                    $video_data =[
                        'link'  => $video,
                        'type'  => 2
                    ];
                    $event->media()->update($video_data);
                }
            }

            return Helpers::Get_Response(200, 'success', '', [], Event::query()->where('id',$request_data['event_id'])->with(['prices.currency','hash_tags','categories'])->first());
        }

        return Helpers::Get_Response(403,'faild',trans('messages.edit_event'),[],[]);




    }

    /**
     * Delete Event By event Owner only
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */


    public function delete_event(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        //validation
        $validator = Validator::make($request_data,
            [
                "event_id" => "required"

            ]);
        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', trans('validation.required'), $validator->errors(), []);
        }
        $user_id = User::where('api_token','=',$request->header('access-token'))->first()->id;

        // update main events info
        $event = Event::find($request_data['event_id']);
        if(!$event){
            return Helpers::Get_Response(403, 'error', 'not found', [], []);
        }

        // Check for Event Ownership
        if($event->created_by == $user_id){
            //delete relationships
            //detach categories
            $event->categories()->detach();
            $event->hash_tags()->detach();
            $event->prices()->delete();
            $event->media()->delete();


            //delete Event
            $event->delete();

            return Helpers::Get_Response(200, 'success', '', [], []);




        }

        return Helpers::Get_Response(403,'faild',trans('messages.delete_event'),[],[]);




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
        if($request->header('access-token')){
            $user = User::where('api_token',$request->header('access-token'))->first();
            // we want to get all events
            // related to this category - created by the login user
            $users_events = $interest->events()
                ->with('prices.currency','categories','hash_tags','media')
                ->CreatedByUser($user)
                ->ShowInMobile();
            $non_users_events = $interest->events()
                ->with('prices.currency','categories','hash_tags','media')
                ->NotCreatedByUser($user)
                ->ShowInMobile();
            switch ($type) {
                case 'upcoming':
                    $users_data = $users_events->UpcomingEvents();
                    $not_user_data = $non_users_events->UpcomingEvents();
                    break;
                default:
                    $users_data = $users_events->PastEvents();
                    $not_user_data = $non_users_events->PastEvents();
                    break;
            }
            $page = array_key_exists('page',$request_data) ? $request_data['page']:1;
            $limit = array_key_exists('limit',$request_data) ? $request_data['limit']:10;
            $result = array_merge($users_data->WithPaginate($page,$limit)->get()->toArray(),$not_user_data->WithPaginate($page,$limit)->get()->toArray());
        }else{
            $events = $interest->events()
                ->with('prices.currency','categories','hash_tags','media')
                ->IsActive()
                ->ShowInMobile();
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
        }
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
        $page = array_key_exists('page',$request_data) ? $request_data['page']:1;
        $limit = array_key_exists('limit',$request_data) ? $request_data['limit']:10;

        //Check if user Login
        if($request->header('access-token')){
            $user = User::where('api_token',$request->header('access-token'))->first();
            $user_events =Event::query()->with('prices.currency','hash_tags','categories','media')
                          ->SuggestedAsBigEvent()
                          ->CreatedByUser($user);
            $non_user_events = Event::query()->with('prices.currency','hash_tags','categories','media')
                ->SuggestedAsBigEvent()
                ->NotCreatedByUser($user);
            switch ($type) {
                case 'upcoming':
                    $user_data  = $user_events->UpcomingEvents();
                    $not_user_data = $non_user_events->UpcomingEvents();
                    $result = array_merge($user_data->WithPaginate($page,$limit)->get()->toArray(),$not_user_data->WithPaginate($page,$limit)->get()->toArray());
                    return Helpers::Get_Response(200, 'success', '', '',$result);

                    break;
                case 'slider':
                    $data = Event::BigEvents()->orderBy('sort_order','DESC')
                        ->with('prices.currency','categories','hash_tags','media')
                        ->IsActive()
                        ->ShowInMobile();
                    $result =$data->WithPaginate($page,$limit)->get();
                    return Helpers::Get_Response(200, 'success', '', '',$result);
                    break;
                default:
                    $user_data = $user_events->PastEvents();
                    $not_user_data = $non_user_events->PastEvents();
                    //$result = $not_user_data->union($user_data)->orderBy("id","DESC")->get();
                    $result = array_merge($user_data->WithPaginate($page,$limit)->get()->toArray(),$not_user_data->WithPaginate($page,$limit)->get()->toArray());
                    return Helpers::Get_Response(200, 'success', '', '',$result);
                    break;
            }

        }else{
            $events = Event::query()
                ->with('prices.currency','hash_tags','categories','media')
                ->IsActive()
                ->ShowInMobile()
                ->SuggestedAsBigEvent();
            switch ($type) {
                case 'upcoming':
                    $data = $events->UpcomingEvents();
                    break;
                case 'slider':
                    $data = Event::BigEvents()->orderBy('sort_order','DESC')
                        ->with('prices.currency','categories','hash_tags','media')
                        ->IsActive()
                        ->ShowInMobile();
                    break;
                default:
                    $data = $events->PastEvents();
                    break;
            }


            $result =$data->WithPaginate($page,$limit)->get();
            return Helpers::Get_Response(200, 'success', '', '',$result);



        }


        //Validate


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
     * this will return all events in this month
     * form today to the end of month and all events in the next month
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

        if($request->header('access-token')){
            $user = User::where("api_token",$request->header('access-token'))->first();
            //this Month Events
            $this_month_by_user = Event::query()
                ->with('prices.currency','categories','hash_tags','media')
                ->CreatedByUser($user)
                ->ShowInMobile()
                ->ThisMonthEvents()
                ->WithPaginate($page,$limit)
                ->orderBy('end_datetime','DESC')
                ->get();
            $this_month_not_by_user = Event::query()
                ->with('prices.currency','categories','hash_tags','media')
                ->NotCreatedByUser($user)
                ->ShowInMobile()
                ->ThisMonthEvents()
                ->WithPaginate($page,$limit)
                ->orderBy('end_datetime','DESC')
                ->get();
            $this_month = array_merge($this_month_by_user->toArray(),$this_month_not_by_user->toArray());

            //Next Events
            $next_month_by_user = Event::query()
                ->with('prices.currency','categories','hash_tags','media')
                ->CreatedByUser($user)
                ->ShowInMobile()
                ->NextMonthEvents()
                ->WithPaginate($page,$limit)
                ->orderBy('end_datetime','DESC')
                ->get();
            $next_month_not_by_user = Event::query()
                    ->with('prices.currency','categories','hash_tags','media')
                    ->NotCreatedByUser($user)
                    ->ShowInMobile()
                    ->NextMonthEvents()
                    ->WithPaginate($page,$limit)
                    ->orderBy('end_datetime','DESC')
                    ->get();
            $next_month = array_merge($next_month_by_user->toArray(),$next_month_not_by_user->toArray());
            $start_to_today_by_user = Event::query()
                ->with('prices.currency','categories','hash_tags','media')
                ->CreatedByUser($user)
                ->ShowInMobile()
                ->StartOfMothEvents()
                ->WithPaginate($page,$limit)
                ->orderBy('end_datetime','DESC')
                ->get();
            $start_to_today_not_by_user = Event::query()
                ->with('prices.currency','categories','hash_tags','media')
                ->NotCreatedByUser($user)
                ->ShowInMobile()
                ->StartOfMothEvents()
                ->WithPaginate($page,$limit)
                ->orderBy('end_datetime','DESC')
                ->get();
            $start_to_today = array_merge($start_to_today_by_user->toArray(),$start_to_today_not_by_user->toArray());

            $result = [
                'start_of_month_to_today'          => $start_to_today,
                'start_of_today_to_end'            => $this_month,
                'next_month'                       => $next_month

            ];
            return Helpers::Get_Response(200,'success','',[],$result);



        }else{
            $this_month = Event::query()
                ->with('prices.currency','categories','hash_tags','media')
                ->IsActive()
                ->ShowInMobile()
                ->ThisMonthEvents()
                ->WithPaginate($page,$limit)
                ->orderBy('end_datetime','DESC')
                ->get();
            $next_month = Event::query()
                ->with('prices.currency','categories','hash_tags','media')
                ->IsActive()
                ->ShowInMobile()
                ->NextMonthEvents()
                ->WithPaginate($page,$limit)
                ->orderBy('end_datetime','DESC')
                ->get();
            $start_to_today = Event::query()
                ->with('prices.currency','categories','hash_tags','media')
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

    /**
     * list all posts related to events
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */


    public function event_posts(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        $page = array_key_exists('page',$request_data) ? $request_data['page']:1;
        $limit = array_key_exists('limit',$request_data) ? $request_data['limit']:10;

        $event_posts = EventPost::query()
            ->orderBy('id','DESC')
            ->withCount('replies')
            ->with('user:id,photo')
            ->WithPaginate($page,$limit)
            ->get();

        return Helpers::Get_Response(200,'success','',[],$event_posts);

    }

    /**
     * Delete event post by the post ownere or event owner only
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete_event_post(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        $user_id = User:: where("api_token", "=", $request->header('access-token'))->first()->id;
        $event_post = EventPost::find($request_data['event_post_id']);
        if(!$event_post){
            return Helpers::Get_Response(401,'faild','Not found',[],[]);

        }
        // check if the logged user is the owner of this post
        if($event_post->user_id == $user_id || $event_post->event->created_by == $user_id){
            //perform delete and delete the replies
            $event_post->replies()->delete();
            $event_post->delete();
            //return success of delete
            return Helpers::Get_Response(200,'success','',[],[]);


        }else{
            // return that the user is unotherized
            return Helpers::Get_Response(403,'faild',trans('messages.delete_post'),[],[]);

        }

    }

    /**
     * Delete reply to event post
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public  function delete_reply(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        //validation
        $validator = Validator::make($request_data,
            [
                "reply_id" => "required"

            ]);
        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', trans('validation.required'), $validator->errors(), []);
        }
        $user_id = User:: where("api_token", "=", $request->header('access-token'))->first()->id;
        $reply = PostReply::find($request_data['reply_id']);
        if(!$reply){
            return Helpers::Get_Response(401,'faild','Not found',[],[]);
        }
        // check if the logged user is the owner of this post
        if($reply->created_by == $user_id || $reply->post->user_id == $user_id || $reply->post->event->created_by == $user_id){
            //perform delete and delete the replies
            $reply->delete();
            //return success of delete
            return Helpers::Get_Response(200,'success','',[],[]);
        }else{
            // return that the user is unotherized
            return Helpers::Get_Response(403,'faild',trans('messages.delete_post'),[],[]);
        }
    }

    /**
     * return recommended events related to user main interests or in all categories
     * @param Request $request
     * @param null $type or 'upcoming'
     * @return \Illuminate\Http\JsonResponse
     */

    public  function recommended_events(Request $request,$type=null){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        $page = array_key_exists('page',$request_data) ? $request_data['page']:1;
        $limit = array_key_exists('limit',$request_data) ? $request_data['limit']:10;


        switch ($type) {
            case 'upcoming':
                $data =  Event::query()->whereHas('categories',function ($query){
                    $user = User::where("api_token", "=", Request::capture()->header('access-token'))->first();
                    $user_interests = $user->interests->pluck('pivot.interest_id');
                    $query->whereIn("interest_id",$user_interests);
                })->with(['prices.currency','hash_tags','categories'])
                    ->IsActive()
                    ->ShowInMobile()
                    ->UpcomingEvents()
                    ->withPaginate($page,$limit)
                    ->get();

                if($data->isEmpty()){
                    $data =  Event::query()
                        ->has('categories')
                        ->with(['prices.currency','hash_tags','categories'])
                        ->IsActive()
                        ->ShowInMobile()
                        ->UpcomingEvents()
                        ->withPaginate($page,$limit)
                        ->get();
                }

                break;

            default:
                $data =  Event::query()->whereHas('categories',function ($query){
                    $user = User::where("api_token", "=", Request::capture()->header('access-token'))->first();
                    $user_interests = $user->interests->pluck('pivot.interest_id');
                    $query->whereIn("interest_id",$user_interests);
                })->with(['prices.currency','hash_tags','categories'])
                    ->IsActive()
                    ->ShowInMobile()
                    ->pastEvents()
                    ->withPaginate($page,$limit)
                    ->get();

                if($data->isEmpty()){
                    $data =  Event::query()
                        ->has('categories')
                        ->with(['prices.currency','hash_tags','categories'])
                        ->IsActive()
                        ->ShowInMobile()
                        ->PastEvents()
                        ->withPaginate($page,$limit)
                        ->get();
                }
                break;
        }

        return Helpers::Get_Response(200,'success','',[],$data);

    }

    /**
     * list all currencies
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function  all_currencies(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        return Helpers::Get_Response(200,'success','',[],Currency::all());


    }

    /**
     * list all genders
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function all_genders(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        return Helpers::Get_Response(200,'success','',[],Gender::all());

    }

    /**
     * list all event categories
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function  event_categories(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        $categories = Interest::query()->has('events')->get();
        return Helpers::Get_Response(200,'success','',[],$categories);
    }


    /**
     * add new Event in User Going
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function add_user_going(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        //validation
        $validator = Validator::make($request_data,
            [
                "event_id" => "required"

            ]);
        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', trans('validation.required'), $validator->errors(), []);
        }
        $user = User::where("api_token", "=", $request->header('access-token'))->first();
        if($user->GoingEvents()->where("event_id",$request_data['event_id'])->first()){
            $user->GoingEvents()->detach($request_data['event_id']);
            return Helpers::Get_Response(200,'deleted successfully','',[],[]);
        }
        $user->GoingEvents()->sync([$request_data['event_id']]);
        return Helpers::Get_Response(200,'success','',[],[]);

    }


    /**
     * Add entity and item in user favourites
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function add_user_favourites(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        $validator = Validator::make($request_data,
            [
                "event_id"   => "required",
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
                    ['item_id' , '=',$request_data['event_id']],
                    ['entity_id','=',4]
                    ]);
        if(!$check->get()->isEmpty()){
            $check->delete();
            return Helpers::Get_Response(200,'deleted','',[],[]);
        }
        $insert = DB::table('user_favorites')->insert([
            'name'      =>$request_data['name'],
            'user_id'   => $user->id,
            'item_id' => $request_data['event_id'],
            'entity_id'   => 4

        ]);
        if(!$insert){
            return Helpers::Get_Response(401,'failed','Error in saving',[],[]);

        }

        return Helpers::Get_Response(200,'success','',[],[]);

    }

    /**
     * Add Event in user calender
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function add_user_calenders(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        //validation
        $validator = Validator::make($request_data,
            [
                "event_id" => "required"

            ]);
        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', trans('validation.required'), $validator->errors(), []);
        }
        $user = User::where("api_token", "=", $request->header('access-token'))->first();
        $event = Event::find($request_data['event_id']);
        $user->CalenderEvents()->attach($request_data['event_id'],
            [
                'from_date' => $event->start_datetime ,
                'to_date'   =>$event->end_datetime
            ]
            );
        return Helpers::Get_Response(200,'success','',[],[]);

    }

    /**
     * List events in user calender
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function calender_events(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        $user = User::where("api_token", "=", $request->header('access-token'))->first();
        $events = $user->CalenderEvents()
                  ->with('prices.currency','categories','hash_tags','media')
                  ->orderBy('end_datetime','DESC')
                  ->get();
        return Helpers::Get_Response(200,'success','',[],$events);

    }

    /**
     * list Trending Keywords
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function trending_keywords(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        return Helpers::Get_Response(200,'success','',[],TrendingKeyword::all());

    }

    /**
     * list nearby events related to user location
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function nearby_events(Request $request){
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

        // PerFrom The Query
        $lat = $request_data['user_lat'];
        $lng = $request_data['user_lng'];
        $radius = array_key_exists('radius',$request_data) ? $request_data['radius']:100;
        $page = array_key_exists('page',$request_data) ? $request_data['page']:1;
        $limit = array_key_exists('limit',$request_data) ? $request_data['limit']:10;
        if($request->header('access-token')){
            $user = User::where("api_token",$request->header("access-token"))->first();
            $events_by_user = Event::query()->Distance($lat,$lng,$radius,"km")
                ->with('prices.currency','categories','hash_tags','media')
                ->CreatedByUser($user)
                ->ShowInMobile()
                ->WithPaginate($page,$limit)
                ->get();
            $events_not_by_user = Event::query()->Distance($lat,$lng,$radius,"km")
                ->with('prices.currency','categories','hash_tags','media')
                ->NotCreatedByUser($user)
                ->ShowInMobile()
                ->WithPaginate($page,$limit)
                ->get();
            $result = array_merge($events_by_user->toArray(),$events_not_by_user->toArray());
            return Helpers::Get_Response(200,'success','',[],[$result]);
        }else{
            $events = Event::query()->Distance($lat,$lng,$radius,"km")
                ->with('prices.currency','categories','hash_tags','media')
                ->IsActive()
                ->ShowInMobile()
                ->WithPaginate($page,$limit)
                ->get();
            return Helpers::Get_Response(200,'success','',[],[$events]);
        }

    }

    /**
     * search in all events according to specific keyword
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function search(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        //validation
        $validator = Validator::make($request_data,
            [
                "keyword" => "required",
            ]);
        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', trans('validation.required'), $validator->errors(), []);
        }
        $keyword = Helpers::CleanText($request_data['keyword']);
        $page = array_key_exists('page',$request_data) ? $request_data['page']:1;
        $limit = array_key_exists('limit',$request_data) ? $request_data['limit']:10;

       //check if user logged in
        if($request->header('access-token')){
            $user = User::where('api_token',$request->header('access-token'))->first();
            //search in entity_localizations if arabic
            if(array_key_exists('lang_id',$request_data) && $request_data['lang_id'] == 2){

                    $events_by_user = Event::event_entity_ar()
                        ->where('entity_localizations.value','like','%'.$keyword.'%')
                        ->with('prices.currency','categories','hash_tags','media')
                        ->CreatedByUser($user)
                        ->ShowInMobile()
                        ->WithPaginate($page,$limit)
                        ->get();
                    $events_not_by_user = Event::event_entity_ar()
                        ->where('entity_localizations.value','like','%'.$keyword.'%')
                        ->with('prices.currency','categories','hash_tags','media')
                        ->NotCreatedByUser($user)
                        ->ShowInMobile()
                        ->WithPaginate($page,$limit)
                        ->get();
                    $result = array_merge($events_by_user->toArray(),$events_not_by_user->toArray());
            }else{
                $events_by_user = Event::query()
                    ->where('name','like','%'.$keyword.'%')
                    ->with('prices.currency','categories','hash_tags','media')
                    ->CreatedByUser($user)
                    ->ShowInMobile()
                    ->WithPaginate($page,$limit)
                    ->get();
                $events_not_by_user = Event::query()
                    ->where('name','like','%'.$keyword.'%')
                    ->with('prices.currency','categories','hash_tags','media')
                    ->NotCreatedByUser($user)
                    ->ShowInMobile()
                    ->WithPaginate($page,$limit)
                    ->get();
                $result = array_merge($events_by_user->toArray(), $events_not_by_user->toArray());


            }

            //return result
            return Helpers::Get_Response(200,'success','',[],$result);

        }else{
            $events = Event::query()
                ->where('name','like','%'.$keyword.'%')
                ->with('prices.currency','categories','hash_tags','media')
                ->IsActive()
                ->ShowInMobile()
                ->WithPaginate($page,$limit)
                ->get();

            //search in entity_localizations if arabic
            if(array_key_exists('lang_id',$request_data)){
                if($request_data['lang_id'] == 2){
                    $events = Event::event_entity_ar()
                        ->where('entity_localizations.value','like','%'.$keyword.'%')
                        ->with('prices.currency','categories','hash_tags','media')
                        ->IsActive()
                        ->ShowInMobile()
                        ->WithPaginate($page,$limit)
                        ->get();
                }
            }
            //return result
            return Helpers::Get_Response(200,'success','',[],$events);

        }

    }

    /**
     * List logged user Events
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public  function  my_events(Request $request){

        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        $user = User::where("api_token", "=", $request->header('access-token'))->first();
        $events = $user->events()
                  ->with('prices.currency','categories','hash_tags','media')
                  ->get();
        return Helpers::Get_Response(200,'success','',[],$events);
    }

    /**
     * Add post to event
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public  function  add_post(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        //validation
        $validator = Validator::make($request_data,
            [
                "event_id" => "required",
                "post"     => "required"
            ]);
        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', trans('validation.required'), $validator->errors(), []);
        }

        $event = Event::find($request_data['event_id']);
        if(!$event){
            return Helpers::Get_Response(401,'faild','Not found',[],[]);

        }
        $user = User::where("api_token", "=", $request->header('access-token'))->first();
        $post = $event->posts()->create([
            "user_id" => $user->id,
            "post"    => $request_data['post']
        ]);

        return Helpers::Get_Response(200,'success','',[],[$post]);
    }


    public function add_post_reply(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        //validation
        $validator = Validator::make($request_data,
            [
                "event_post_id" => "required",
                "reply"     => "required"
            ]);
        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', trans('validation.required'), $validator->errors(), []);
        }
        // add post reply
        $event_post = EventPost::find($request_data['event_post_id']);
        if(!$event_post){
            return Helpers::Get_Response(401,'faild','Not found',[],[]);
        }
        $user = User::where("api_token", "=", $request->header('access-token'))->first();
        $post_reply = $event_post->replies()->create([
            'reply'      => $request_data['reply'],
            'created_by' => $user->id

        ]);
        return Helpers::Get_Response(200,'success','',[],[$post_reply]);

    }


    public function tweets_by_hashtags(Request $request){
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request_data['lang_id']);
        }
        //validation
        $validator = Validator::make($request_data,
            [
                "hashtag" => "required"
                
            ]);
        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', trans('validation.required'), $validator->errors(), []);
        }
        $limit = array_key_exists('limit',$request_data) ? $request_data['limit'] :10;
        $twitterSearch = new TwitterSearchApi();
       $tweets =  $twitterSearch->StartTwitterSearch($request_data['hashtag'],'mixed',$limit);
      return Helpers::Get_Response(200,'success','',[],$tweets->statuses);
    }


}
