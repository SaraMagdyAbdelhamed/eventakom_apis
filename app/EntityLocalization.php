<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EntityLocalization extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'entity_localizations';

    protected $fillable = ['entity_id', 'field', 'item_id', 'value', 'lang_id','cleared_text'];
    public $timestamps = false;


    // relations

    // many entity_localizations belongs to 1 entity
    public function entity() {
        return $this->belongsTo('App\Entity', 'entity_id');
    }
}
