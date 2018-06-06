<?php
/**
 * Created by PhpStorm.
 * User: pentavalue
 * Date: 6/5/2018
 * Time: 5:07 PM
 */
namespace App;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'shops';
    protected $fillable = ['name', 'photo','phone','website','info','is_active'];
    public $timestamps = false;




    /** Relations */
    public function branches(){
        return $this->hasMany('App\Branch','shop_id');
    }

    public function days(){
        return $this->belongsToMany('App\Day','shop_days','shop_id','day_id');
    }

    public function getNameAttribute($value)
    {
        $result = (app('translator')->getLocale()=='en') ? Helpers::localization('shops','name',$this->id,1) : Helpers::localization('shops','name',$this->id,2);
        return ($result=='Error')? $value : $result;
    }

    public function scopeIsActive($query){
        return $query->where("is_active",'1');
    }
    public function ScopeWithPaginate($query,$page,$limit){
        return $query->skip(($page-1)*$limit)->take($limit);
    }









}