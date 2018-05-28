<?php
/**
 * Created by PhpStorm.
 * User: pentavalue
 * Date: 5/28/2018
 * Time: 12:24 PM
 */
namespace App;

use Illuminate\Database\Eloquent\Model;

class PostReply extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'event_post_replies';
    protected $fillable = ['event_post_id', 'reply','created_by','updated_by'];
    protected $dates = ['created_at','updated_at'];

    public $timestamps = true;

    // relations

    // 1 entity belongs to many entity_localizations
    public  function  post(){
        return $this->belongsTo('App\EventPost');
    }




}