<?php
/**
 * Created by PhpStorm.
 * User: pentavalue
 * Date: 6/6/2018
 * Time: 9:34 AM
 */
namespace App;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'shop_branches';
    protected $fillable = ['shop_id','branch'];
    public $timestamps = false;

    /** Relations */
    public function shop(){
        return $this->belongsTo('App\Shop');
    }
    public function days(){
        return $this->belongsToMany('App\Day','shop_branch_times','branch_id','day_id')
            ->withPivot('from','to');
    }








}