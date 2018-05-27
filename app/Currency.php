<?php
/**
 * Created by PhpStorm.
 * User: pentavalue
 * Date: 5/27/2018
 * Time: 11:18 AM
 */
namespace App;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'currencies';

    protected $fillable = ['name','symbol','rate','def','subdivision_name','sort_order'];
    public $timestamps = false;

    // relations

    // 1 entity belongs to many entity_localizations
    public function price() {
        return $this->hasOne('App\Price');
    }



}