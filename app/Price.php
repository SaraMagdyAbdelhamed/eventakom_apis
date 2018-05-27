<?php
/**
 * Created by PhpStorm.
 * User: pentavalue
 * Date: 5/27/2018
 * Time: 11:18 AM
 */
namespace App;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'event_tickets';

    protected $fillable = ['event_id','name', 'price','available_tickets','current_available_tickets','currency_id'];
    public $timestamps = false;

    // relations

    // 1 entity belongs to many entity_localizations
    public function event() {
        return $this->belongsTo('App\Event');
    }

    public function currency(){
        return $this->belongsTo('App\Currency','currency_id');
    }

}