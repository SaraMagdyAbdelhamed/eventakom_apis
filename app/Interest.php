<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Interest extends Model {

    protected $fillable = [];

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];
    protected $hidden = ['pivot'];


     //Relationships
    public function users()
    {
        return $this->belongsToMany('App\User','user_interests');
    }






}
