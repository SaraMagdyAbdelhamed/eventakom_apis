<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FamousAttractionMedia extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'famous_attraction_media';

    protected $fillable = ['famous_attraction_id','media','type'];
    public $timestamps = false;

    // relations

    // 1 entity belongs to many entity_localizations
    public function famouse_attractions() {
        return $this->belongsTo('App\FamousAttraction');
    }

    public function getMediaAttribute($value){
        if($this->type == 1){
            $base_url = env('PHOTO_PATH');
            $photo = $base_url.$value;
            return $photo;
        }
        return $value;

    }




}