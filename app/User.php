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

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use HasApiTokens, Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

   /*id username  password  first_name  last_name email tele_code mobile  country_id  city_id gender_id photo birthdate is_active created_by  updated_by  created_at  updated_at  device_token  mobile_os is_social access_token  social_token  lang_id mobile_verification_code is_mobile_verification_code_expired  last_login  api_token longtuide latitude*/
    protected $fillable = [
        'first_name', 'last_name','email', 'mobile','device_token','username','tele_code','country_id','city_id','device_token','mobile_os','is_social','is_active',
        'social_token','lang_id','is_mobile_verification_code_expired','last_login','longtuide','latitude','password','mobile_verification_code','access_token','api_token','verification_date','birthdate','gender_id','email_verification_code','timezone','photo','is_mobile_verified'
    ];

    protected $dates = ['created_at', 'updated_at'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password','verification_code ','access_token','pivot'
    ];

    public static $rules = [ 'first_name' => 'required|between:1,12',
            'last_name' => 'required|between:1,12',
            'email' => 'required|email|unique:users|max:35',
            'city_id' => 'required',
            'mobile' => 'required|numeric|unique:users',
            'password' => 'required|between:8,20',
            // 'photo' => 'image|max:1024', 
            //'device_token' => 'required',
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


}
