<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class user_rule extends Model {
	protected $table='users_rules';
	
    protected $fillable = ['user_id','rule_id'];

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];
    public $timestamps = false;
    // Relationships

}
