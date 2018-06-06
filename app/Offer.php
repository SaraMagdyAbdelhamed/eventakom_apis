<?php
/**
 * Created by PhpStorm.
 * User: pentavalue
 * Date: 6/6/2018
 * Time: 10:31 AM
 */
namespace App;

use Illuminate\Database\Eloquent\Model;
class Offer extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'offers';
    protected $fillable = ['name', 'image','discount_percent','products_number','max_purchase_number','description','show_in_homepage','price','original_price','
    active'];
    public $dates = ['created_at','updated_at'];


    public function ScopeWithPaginate($query,$page,$limit){
        return $query->skip(($page-1)*$limit)->take($limit);
    }






}

