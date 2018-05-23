<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'entities';

    protected $fillable = ['name', 'table_name'];
    public $timestamps = false;

    // relations

    // 1 entity belongs to many entity_localizations
    public function localizations() {
        return $this->hasMany('App\EntityLocalization', 'entity_id');
    }
}
