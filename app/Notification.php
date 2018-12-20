<?php

namespace App;
use App\EventMedia;

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
      protected $appends = ['item_photo'];



      /*Relations*/
      public function type()
      {
      	return $this->belongsTo('App\NotificationType','notification_type_id');
      }

      public function notificationEvent()
      {
        return $this->belongsTo('App\Event','item_id');
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

    public function GetNotifcationMedia(){
      if(is_null($this->item_id) || ($this->item_id == '')){
        $result = '';
      }else{
        $media = EventMedia::where('event_id',$this->item_id)->where('type',1)->first();
        if($media)
        {
          $result = $media->link;
        }else{
          $result = '';
        }
      }
        return $result;
    }

    public function getItemPhotoAttribute(){
      return $this->GetNotifcationMedia();
    }

}
