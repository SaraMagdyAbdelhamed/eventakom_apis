<?php
/**
 * Created by PhpStorm.
 * User: pentavalue
 * Date: 5/31/2018
 * Time: 10:56 AM
 */
namespace App;
use App\Libraries\Helpers;


use Illuminate\Database\Eloquent\Model;

class TrendingKeyword extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'trending_keywords';

    protected $fillable = ['name','created_by','updated_by'];

    // relations

    // 1 entity belongs to many entity_localizations


    //localization
    public function getNameAttribute($value)
    {
        $result = (app('translator')->getLocale()=='en') ? Helpers::localization('trending_keywords','name',$this->id,1) : Helpers::localization('trending_keywords','name',$this->id,2);
        return ($result=='Error')? $value : $result;
    }




}