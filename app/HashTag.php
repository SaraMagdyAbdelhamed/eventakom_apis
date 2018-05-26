<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HashTag extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'hash_tags';
    protected $hidden = ['pivot'];
    protected $fillable=['name'];
    public $timestamps = false;

    //protected $fillable = ['name', 'table_name'];

    // relations
     public function events()
    {
        return $this->belongsToMany('App\Event','event_hash_tag');
    }

    
}
