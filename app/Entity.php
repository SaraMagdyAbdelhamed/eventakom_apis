<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
 
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

     // Relationships
    public function entity()
    {
        return $this->belongsTo("App\Entity","entity_id"); //important dont forget to add (ger_county_id) to geo_cities table
    }
}