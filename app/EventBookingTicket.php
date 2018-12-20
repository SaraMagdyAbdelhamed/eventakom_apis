<?php
/**
 * Created by PhpStorm.
 * User: pentavalue
 * Date: 6/6/2018
 * Time: 9:36 AM
 */
namespace App;
use Illuminate\Database\Eloquent\Model;


class EventBookingTicket extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'event_booking_tickets';
    protected $fillable = ['event_id','booking_id','barcode','serial_number','is_used','pdf'];
    public $timestamps = false;

    /** Relations */
    public  function currency(){
        return $this->belongsTo('App\Currency','currency_id');
    }
    public function event(){
        return $this->belongsTo('App\Event');
    }

}