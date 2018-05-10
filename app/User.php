<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name','email', 'mobile','device_token','username','tele_code','country_id','city_id','device_token','mobile_os','is_social',
        'social_token','lang_id','is_verification_code_expired','last_login','longtuide','latitude','password','verification_code','access_token','api_token'
    ];

    protected $dates = ['created_at', 'updated_at'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password','verification_code ','access_token','api_token',
    ];

    public static $rules = [ 'first_name' => 'required|between:1,12',
            'last_name' => 'required|between:1,12',
            'email' => 'required|email|unique:users|max:35',
            'conutry_code_id' => 'required',
            'mobile' => 'required|numeric',
            'password' => 'required|between:8,20',
            'photo' => 'image|max:1024', 
            //'device_token' => 'required',
            'mobile_os' => 'in:android,ios',
            'lang_id' => 'in:1,2'];

}
