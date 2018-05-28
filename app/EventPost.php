<?php
/**
 * Created by PhpStorm.
 * User: pentavalue
 * Date: 5/28/2018
 * Time: 11:53 AM
 */
namespace App;

use Illuminate\Database\Eloquent\Model;

class EventPost extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'event_posts';

    protected $fillable = ['event_id', 'user_id','post'];
    protected $dates = ['created_at'];

    public $timestamps = true;

    // relations

    // 1 entity belongs to many entity_localizations
    public  function  event(){
        return $this->belongsTo('App\Event');
    }
    public function user(){
        return $this->belongsTo('App\User');
    }
    public  function replies(){
        return $this->hasMany('App\PostReply','event_post_id');
    }




}
