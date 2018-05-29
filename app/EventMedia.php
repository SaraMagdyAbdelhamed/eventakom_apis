<?php
/**
 * Created by PhpStorm.
 * User: pentavalue
 * Date: 5/29/2018
 * Time: 2:36 PM
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class EventMedia extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'event_media';

    protected $fillable = ['event_id','link','type'];
    public $timestamps = false;

    // relations

    // 1 entity belongs to many entity_localizations
    public function event() {
        return $this->belongsTo('App\Event');
    }



}