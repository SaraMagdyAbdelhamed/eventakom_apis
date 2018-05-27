<?php

namespace App;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\Libraries\Helpers;

class Event extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'events';
    protected $dates = ['created_at', 'updated_at'];
    protected $hidden = ['pivot'];


    protected $fillable = ['name', 'description','website','mobile','email','code','address','longtuide','latitude','venue','start_datetime','end_datetime','suggest_big_event','show_in_mobile','gender_id','age_range_id','is_paid','use_ticketing_system','is_active','event_status_id','rejection_reason','created_by','updated_by'];

    // relations


    //Many to many realation with interests
    public  function categories()
    {
        return $this->belongsToMany('App\Interest', 'event_categories','event_id','interest_id');
    }


    //Many to many realation with hashtags
    public  function hash_tags()
    {
        return $this->belongsToMany('App\HashTag', 'event_hash_tags','event_id','hash_tag_id');
    }

    public  function age_range(){
        return $this->belongsTo('App\AgeRange','age_range_id');
    }



    //Localizations

    public function getNameAttribute($value)
    {
        $result = (app('translator')->getLocale()=='en') ? Helpers::localization('events','name',$this->id,1) : Helpers::localization('events','name',$this->id,2);
        return ($result=='Error')? $value : $result;
    }
    public function getDescriptionAttribute($value)
    {
        $result = (app('translator')->getLocale()=='en') ? Helpers::localization('events','description',$this->id,1) : Helpers::localization('events','description',$this->id,2);
        return ($result=='Error')? $value : $result;
    }
    public function getVenueAttribute($value)
    {
        $result = (app('translator')->getLocale()=='en') ? Helpers::localization('events','venue',$this->id,1) : Helpers::localization('events','venue',$this->id,2);
        return ($result=='Error')? $value : $result;
    }






    public static function BigEvents(){
    	return static::query()->join('big_events','events.id','=','big_events.event_id')
    		   ->select('events.*','big_events.sort_order');
    		   

    }



    public function ScopeIsActive($query){
        return $query->where('is_active', '=', 1);

    }

    public function ScopeShowInMobile($query){
        return $query->where('show_in_mobile', '=', 1);
    }

    public function ScopeUpcomingEvents($query){
        return $query->where("end_datetime",'>=',date('Y-m-d H:i:s'));

    }

    public function ScopePastEvents($query){
        return $query->where("end_datetime",'<=',date('Y-m-d H:i:s'));

    }


    public function ScopeWithPaginate($query,$page,$limit){
        return $query->skip(($page-1)*$limit)->take($limit);
    }

    
}
