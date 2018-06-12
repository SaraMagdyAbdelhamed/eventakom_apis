<?php
/**
 * Created by PhpStorm.
 * User: pentavalue
 * Date: 6/6/2018
 * Time: 9:36 AM
 */
namespace App;
use Illuminate\Database\Eloquent\Model;


class Day extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'days';
    protected $fillable = ['name'];
    public $timestamps = false;

    /** Relations */
    public function shop(){
        return $this->belongsToMany('App\Shop');
    }

    public function branch(){
        return $this->belongsToMany('App\Branch','shop_branch_times','branch_id','shop_id')
            ->withPivot('from','to');
    }
    public function famous_attraction(){
         return $this->belongsToMany('App\Day','famous_attraction_days','famous_attraction_id','day_id')
            ->withPivot('from','to');
    }





}