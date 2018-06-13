<?php

namespace App;
use App\Libraries\Helpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;


class FamousAttractionCategory extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'fa_categories';
    protected $fillable = ['name','created_by','updated_by'];
    protected $dates = ['created_at', 'updated_at'];

    public function getNameAttribute($value)
    {
        $result = (app('translator')->getLocale()=='en') ? Helpers::localization('fa_categories','name',$this->id,1) : Helpers::localization('fa_categories','name',$this->id,2);
        return ($result=='Error')? $value : $result;
    }


    /** Relations */
    public function user(){
        return $this->belongsTo('App\User');
    }
    public function famous_attractions()
    {
        return $this->belongsToMany('App\FamousAttraction','famous_attraction_categories');
    }


    public function ScopeWithPaginate($query,$page,$limit){
        return $query->skip(($page-1)*$limit)->take($limit);
    }
    
 





}











