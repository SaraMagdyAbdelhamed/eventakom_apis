<?php

namespace App;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\Libraries\Helpers;

class GeoCountry extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    //Localizations


    public function getNameAttribute($value)
    {
        $result = (app('translator')->getLocale()=='en') ? Helpers::localization('geo_countries','name',$this->id,1) : Helpers::localization('geo_countries','name',$this->id,2);
        return ($result=='Error')? $value : $result;
    }

    public static function country_entity_ar(){
      return static::query()->join('entity_localizations','geo_countries.id','=','entity_localizations.item_id')
      ->where('entity_id','=',8)->where('field','=','name')
      ->select('geo_countries.*','entity_localizations.value');


    }
}
