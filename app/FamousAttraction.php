<?php

namespace App;
use App\Libraries\Helpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;


class FamousAttraction extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'famous_attractions';
    protected $fillable = ['name','address','longtuide','latitude','phone','info','is_active'];
    public $timestamps = false;

    public function getNameAttribute($value)
    {
        $result = (app('translator')->getLocale()=='en') ? Helpers::localization('famous_attractions','name',$this->id,1) : Helpers::localization('famous_attractions','name',$this->id,2);
        return ($result=='Error')? $value : $result;
    }



    /** Relations */
    public  function categories()
    {
        return $this->belongsToMany('App\FamousAttractionCategory', 'famous_attraction_categories','famous_attraction_id','category_id');
    }
    public function days(){
        return $this->belongsToMany('App\Day','famous_attraction_days','famous_attraction_id','day_id')
            ->withPivot('from','to');
    }
     public  function media(){
        return $this->hasMany('App\FamousAttractionMedia','famous_attraction_id');
    }



    public function scopeIsActive($query){
        return $query->where("is_active",'1');
    }
    public function ScopeWithPaginate($query,$page,$limit){
        return $query->skip(($page-1)*$limit)->take($limit);
    }
    public function scopeDistance($query, $lat, $lng, $radius = 100, $unit = "km")
    {
        $unit = ($unit === "km") ? 6378.10 : 3963.17;
        $lat = (float) $lat;
        $lng = (float) $lng;
        $radius = (double) $radius;
        return $query->having('distance','<=',$radius)
            ->select(DB::raw("*,
                            ($unit * ACOS(COS(RADIANS($lat))
                                * COS(RADIANS(latitude))
                                * COS(RADIANS($lng) - RADIANS(longtuide))
                                + SIN(RADIANS($lat))
                                * SIN(RADIANS(latitude)))) AS distance")
            )->orderBy('distance','asc');
    }




}











