<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FixedPage extends Model
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
    public function updated_by()
    {
        return $this->belongsTo("App\User","updated_by"); 
    }
}
