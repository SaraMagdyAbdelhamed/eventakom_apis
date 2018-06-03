<?php
/**
 * Created by PhpStorm.
 * User: pentavalue
 * Date: 5/30/2018
 * Time: 12:20 PM
 */
namespace App;
use App\Libraries\Helpers;
use Illuminate\Database\Eloquent\Model;

class Gender extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'genders';

    protected $fillable = ['name'];
    public $timestamps = false;


    public function getNameAttribute($value)
    {
        $result = (app('translator')->getLocale()=='en') ? Helpers::localization('genders','name',$this->id,1) : Helpers::localization('genders','name',$this->id,2);
        return ($result=='Error')? $value : $result;
    }

    // relations

    public function event() {
        return $this->hasOne('App\Event');
    }



}