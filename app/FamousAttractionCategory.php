<?php

namespace App;
use App\Libraries\Helpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;


class FamousAttractionCategory extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'fa_categories';
    protected $fillable = ['name','created_by','updated_by'];
    protected $dates = ['created_at', 'updated_at'];

    


    /** Relations */
    public function user(){
        return $this->belongsTo('App\User');
    }
    public function famous_attractions()
    {
        return $this->belongsToMany('App\FamousAttraction','famous_attraction_categories');
    }
    
 





}











