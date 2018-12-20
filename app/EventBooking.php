<?php
/**
 * Created by PhpStorm.
 * User: pentavalue
 * Date: 6/6/2018
 * Time: 9:36 AM
 */
namespace App;
use Illuminate\Database\Eloquent\Model;


class EventBooking extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'event_bookings';
    protected $fillable = ['event_id','event_ticket_id','name','number_of_tickets','created_by'];
    public $dates = ['created_at'];
    
    public function setUpdatedAt($value){ ; }



    /** Relations */
    public  function currency(){
        return $this->belongsTo('App\Currency','currency_id');
    }
    public function event(){
        return $this->belongsTo('App\Event');
    }
    public function owner(){
      return $this->belongsTo('App\Event');
    }

    public function user(){
        return $this->belongsTo('App\User','user_id');
      }
    





}