<?php
/**
 * Created by PhpStorm.
 * User: pentavalue
 * Date: 6/6/2018
 * Time: 10:31 AM
 */
namespace App;
use App\Libraries\Helpers;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
class Offer extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'offers';
    protected $fillable = ['name','description','image_en','image_ar','is_active','created_by','updated_by'];
    public $dates = ['created_at','updated_at'];
    
    public function getImageEnAttribute($value){
        $base_url = 'http://eventakom.com/eventakom_dev/public/';
        $photo = ($value =='' || is_null($value)) ? '':$base_url.$value;
        return $photo;
    }
    public function getImageArAttribute($value){
        $base_url = 'http://eventakom.com/eventakom_dev/public/';
        $photo = ($value =='' || is_null($value)) ? '':$base_url.$value;
        return $photo;
    }


    public function scopeIsActive($query){
        return $query->where("is_active",'1');
    }
    public function ScopeWithPaginate($query,$page,$limit){
        return $query->skip(($page-1)*$limit)->take($limit);
    }


    public function getNameAttribute($value)
    {
        $result = (app('translator')->getLocale()=='en') ? Helpers::localization('offers','name',$this->id,1) : Helpers::localization('offers','name',$this->id,2);
        return ($result=='Error')? $value : $result;
    }







}

