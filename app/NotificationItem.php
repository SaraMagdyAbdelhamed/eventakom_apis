<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotificationItem extends Model
{
     protected $primaryKey = 'id';
    protected $table = 'notification_items';
	public $timestamps = false;

	    public function notification()
    {
        return $this->belongsTo('App\Notification','notification_id');
    }

}
