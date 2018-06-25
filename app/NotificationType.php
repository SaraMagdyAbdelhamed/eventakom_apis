<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotificationType extends Model
{
    //
      protected $primaryKey = 'id';
  	  protected $table = 'notification_types';
  	  protected $fillable = [
        'name','msg','msg_ar','description','description_ar','is_push'
    	];
      public $timestamp = false;

      /*Relations*/

      public function  notification(){
       return $this->belongsTo('App\Notification','notification_id');
      }
}
