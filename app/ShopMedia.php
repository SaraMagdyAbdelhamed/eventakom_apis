<?php
/**
 * Created by PhpStorm.
 * User: pentavalue
 * Date: 5/29/2018
 * Time: 2:36 PM
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShopMedia extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'shop_media';

    protected $fillable = ['shop_id','link','type'];
    public $timestamps = false;

    // relations

    // 1 entity belongs to many entity_localizations
    public function shop() {
        return $this->belongsTo('App\Shop');
    }

    public function getLinkAttribute($value){
        if($this->type == 1){
             $base_url = env('PHOTO_PATH');
            $photo = $base_url.$value;
            return $photo;

        }
        return $value;
    }


}