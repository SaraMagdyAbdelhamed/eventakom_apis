<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Libraries\Helpers;

class Interest extends Model {

    protected $fillable = [];

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];
    protected $hidden = ['pivot'];

    public function getNameAttribute($value)
    {
        $result= (app('translator')->getLocale()=='en') ? Helpers::localization('interests','name',$this->id,1) : Helpers::localization('interests','name',$this->id,2);
        return ($result=='Error')? $value : $result;
    }
     //Relationships
    public function users()
    {
        return $this->belongsToMany('App\User','user_interests');
    }






}
