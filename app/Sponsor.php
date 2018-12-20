<?php
/**
 * Created by PhpStorm.
 * User: pentavalue
 * Date: 6/6/2018
 * Time: 9:36 AM
 */
namespace App;
use Illuminate\Database\Eloquent\Model;


class Sponsor extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'sponsors';


    public function getLogoEnAttribute($value)
    {
            $base_url = 'http://eventakom.com/eventakom_dev/public/';
            $photo = $base_url.$value;
            return $photo;

    }

    public function getLogoArAttribute($value)
    {
            $base_url = 'http://eventakom.com/eventakom_dev/public/';
            $photo = $base_url.$value;
            return $photo;
    }


}