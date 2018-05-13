<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GeoCity extends Model
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
    public function geo_country()
    {
        return $this->belongsTo("App\GeoCountry","country_id"); //important dont forget to add (ger_county_id) to geo_cities table
    }
}
