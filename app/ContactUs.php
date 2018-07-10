<?php
/**
 * Created by PhpStorm.
 * User: pentavalue
 * Date: 6/6/2018
 * Time: 9:34 AM
 */
namespace App;
use App\Libraries\Helpers;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class ContactUs extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'contact_us';
    protected $fillable = ['user_id','email','subject','message'];
    protected $dates = ['created_at'];
    public function setUpdatedAt($value){ ; }

 









}