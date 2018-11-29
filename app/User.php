<?php

namespace App;
use Laravel\Passport\HasApiTokens;
// use Illuminate\Notifications\Notifiable;
 use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\Hash;
use carbon\carbon;
use App\Libraries\Helpers;
use App\GeoCity;
use App\GeoCountry;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use HasApiTokens, Authenticatable, Authorizable;

    public $appends =['country_name','city_name'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

   /*id username  password  first_name  last_name email tele_code mobile  country_id  city_id gender_id photo birthdate is_active created_by  updated_by  created_at  updated_at  device_token  mobile_os is_social access_token  social_token  lang_id mobile_verification_code is_mobile_verification_code_expired  last_login  api_token longtuide latitude*/
    protected $fillable = [
        'first_name', 'last_name','email', 'mobile','device_token','username','tele_code','country_id','city_id','device_token','mobile_os','is_social','is_active',
        'social_token','lang_id','is_mobile_verification_code_expired','last_login','longitude','latitude','password','mobile_verification_code','access_token','api_token','verification_date','birthdate','gender_id','email_verification_code','timezone','photo','is_mobile_verified', 'is_email_verified'
    ];

    protected $dates = ['created_at', 'updated_at'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password','verification_code ','access_token','pivot','city_id','country_id'
    ];

    public static $rules = [ 'first_name' => 'between:1,12',
            'email' => 'email|unique:users|max:35',
            // 'city_id' => 'required',
            'mobile' => 'required|numeric|unique:users',
            'tele_code'=>'required',
            'password' => 'required|between:8,20',
            'mobile_os' => 'in:android,ios',
            'lang_id' => 'in:1,2'];

    // Relationships
    public function rules()
    {
        return $this->belongsToMany('App\Rule', 'user_rules', 'user_id', 'rule_id');
    }

    public function user_details()
    {
        return $this->hasOne('App\user_details');
    }

    public  function interests()
    {
        return $this->belongsToMany('App\Interest', 'user_interests');
    }

    public function posts(){
        return $this->hasMany('App\EventPost','user_id');
    }

    public function GoingEvents()
    {
        return $this->belongsToMany('App\Event', 'user_going');
    }
    public function CalenderEvents(){
        return $this->belongsToMany('App\Event', 'user_calendars')->withPivot('from_date','to_date');

    }

    public function  events(){
        return $this->hasMany('App\Event','created_by');

    }

    public function favorite_events()
    {
        return $this->belongsToMany('App\Event', 'user_favorites','user_id','item_id')->where('entity_id',4);

    }
    public function  post_replies(){
        return $this->hasMany('App\PostReply','created_by');

    }
     public function  fa_categories(){
        return $this->hasMany('App\FamousAttractionCategory','created_by');
    }
    
    public function  event_booking(){
        return $this->hasMany('App\EventBooking','created_by');
    }

    public function notifications(){
        return $this->hasMany('App\Notification','user_id');
    }


 
    public function getPhotoAttribute($value)
    {
        $base_url = 'http://eventakom.com/eventakom_dev/public/';
        $photo = $base_url.$value;
        return $photo;
    }


    public function getCountryNameAttribute()
    {
        if(!is_null($this->country_id))
        {
            $country = GeoCountry::find($this->country_id);
            if($country)
            {
                return $country->name;
            }else{
                return 'Not Added';
            }
        }

        return 'Not Added';

    }
    public function getCityNameAttribute()
    {
         if(!is_null($this->city_id))
        {
            $city = GeoCity::find($this->city_id);
            if($city)
            {
                return $city->name;
            }else{
                return 'Not Added';
            }
        }

        return 'Not Added';
       

    }

     public function setBirthDateAttribute($value)
    {
        if(Helpers::isValidTimestamp($value))
        {
        $this->attributes['birthdate'] = gmdate("Y-m-d\TH:i:s\Z",$value);
        }else{

          return Helpers::Get_Response(403, 'error', trans('Invalid date format'), [], []);   
        }
    }

    public static function AdminUsers(){
       return  static::whereHas('rules', function ($q) {
            $q->inWhere('rule_id', [1,3,4,5]);
        })->get();
    }



}
