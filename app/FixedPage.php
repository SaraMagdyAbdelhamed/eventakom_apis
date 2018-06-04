<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Libraries\Helpers;

class FixedPage extends Model
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
    // Relationships
    public function updated_by()
    {
        return $this->belongsTo("App\User","updated_by"); 
    }

    //  public function getbodyAttribute($value)
    // {
    //     $result = (app('translator')->getLocale()=='en') ? Helpers::localization('fixes_pages','body',$this->id,1) : Helpers::localization('fixes_pages','body',$this->id,2);
    //      $result = html_entity_decode($result);
    //      $result = strip_tags($result);
    //      $result = str_replace('&nbsp;', '', $result);
    //     return ($result=='Error')? $value : $result;
    // }
}
