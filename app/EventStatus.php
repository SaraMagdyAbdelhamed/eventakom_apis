<?php
/**
 * Created by PhpStorm.
 * User: pentavalue
 * Date: 5/27/2018
 * Time: 9:34 AM
 */
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\Libraries\Helpers;

class EventStatus extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'event_statuses';

    protected $fillable = ['name'];
    public $timestamps = false;

    // relations

    // 1 entity belongs to many entity_localizations
    public function event() {
        return $this->hasOne('App\event');
    }
}