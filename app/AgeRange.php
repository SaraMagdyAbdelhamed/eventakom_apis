<?php
/**
 * Created by PhpStorm.
 * User: pentavalue
 * Date: 5/26/2018
 * Time: 11:30 AM
 */
namespace App;
use App\Libraries\Helpers;


use Illuminate\Database\Eloquent\Model;

class AgeRange extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'age_ranges';

    protected $fillable = ['name', 'is_default','from','to'];
    public $timestamps = false;

    // relations

    // 1 entity belongs to many entity_localizations
    public function event()
    {
        return $this->hasOne('App\event');
    }

    //localisations
    public function getNameAttribute($value)
    {
        $result = (app('translator')->getLocale()=='en') ? Helpers::localization('age_ranges','name',$this->id,1) : Helpers::localization('age_ranges','name',$this->id,2);
        return ($result=='Error')? $value : $result;
    }



}