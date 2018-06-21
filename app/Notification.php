<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{

   
      protected $primaryKey = 'id';
  	  protected $table = 'notifications';
  	  protected $fillable = [
        'msg','msg_ar','description',
        'description_ar','user_id','entity_id',
        'item_id','notification_type_id','is_read','is_sent',
        'schedule'
    ];
  	  protected $dates = ['created_at', 'updated_at','schedule'];
      public $timestamps = true;


      /*Relations*/
      public function type()
      {
      	return $this->belongsTo('App\NotificationType','notification_type_id');
      }

      // public function queue()
      // {
      // 	return $this->hasOne('App\NotificationPush');
      // }

    	public function items()
    {
        return $this->hasMany('App\NotificationItem','notification_id');
    }

        public function push()
    {
        return $this->hasMany('App\NotificationPush','notification_id');
    }


        public function user()
    {
        return $this->belongsTo('App\Users','user_id');
    }

}
