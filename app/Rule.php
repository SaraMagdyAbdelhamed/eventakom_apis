<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Rule extends Model {

    protected $fillable = [];

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];

    // Relationships
    public function users()
    {
        return $this->belongsToMany('App\User');
    }

}
