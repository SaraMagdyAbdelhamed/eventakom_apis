<?php

//created_by: Ash

namespace App\Libraries;
use App\Entity;
use Illuminate\Support\Facades\Mail;
use DB;
use App\GeoCountry;
class Helpers
{


  public static  function Get_Response($code , $message , $error_details , $validation_errors , $content) {
    $validation = [];
    $i = 0;
    $validation_errors = current((array) $validation_errors);
    if(is_array($validation_errors) && sizeof($validation_errors) != 0) {
        foreach($validation_errors as $key=>$value) {
            $validation[$i]['field']=$key;
            $validation[$i]['message']=$value;
            $i++;
        }
    }
    return response()->json(['status'=>['code'=>$code,'message'=>$message,'error_details'=>$error_details,'validation_errors'=>$validation],'content'=>$content],200,[],JSON_UNESCAPED_UNICODE);


  }

  public static function Set_locale($locale)
  {
    if($locale == 1)
      {
        app('translator')->setLocale('en');
      }
     else  if($locale == 2)
      {
        app('translator')->setLocale('ar');
      }
  }

   /**
     *  Return translated entity
     *  @param  $table_name     field in `entities` table.      ex: 'fixed_pages'
     *  @param  $field_name     field in `entity_localizations` table.      ex: 'body'
     *  @param  $item_id        field in `entity_localizations` table.      ex: 1
     *  @param  $lang_id        field in `entity_localizations` table.      ex: 2
     *
     *  Example:    Helper::localization('fixed_pages', 'name', '1', '2')
     *  expected result     'عن الشركة'
    */
    public static function localization($table_name, $field_name, $item_id, $lang_id) {
        $localization = Entity::where('table_name', $table_name)->with(['localizations' => function($q) use ($field_name, $item_id, $lang_id){
            $q->where('field', $field_name)->where('item_id', $item_id)->where('lang_id', $lang_id); }
        ])->first();


        $result = isset($localization->localizations[0]) ? $localization->localizations[0]->value : "Error";
        return $result;
    }


       public static function mail($email ,$code ,$verification_code){
          Mail::raw('Welcome To Eventakom  Your Mobile Verification code is ('.$verification_code.')', function($msg) use($email){
              $msg->to([$email])->subject('Eventakom');
              $msg->from(['pentavalue.eventakom@gmail.com']);

            });
      }

      public static function mail_verify($email ,$code ,$verification_code){
        Mail::raw('Welcome To Eventakom  ..Please verify your Email by visiting this link: http://eventakom.com/api/verify_email?email='.$email.'&email_verification_code='.$verification_code, function($msg) use($email){
            $msg->to([$email])->subject('Eventakom');
            $msg->from(['pentavalue.eventakom@gmail.com']);
            // dd( $msg->getSwiftMessage()); 
          });
    }


     public static function mail_verify_withview($view,$email ,$email_verification_code){
       Mail::send($view, ['email' => $email, 'email_verification_code'=>$email_verification_code ], function($msg) use($email){
            $msg->to([$email])->subject('Eventakom');
            $msg->from(['pentavalue.eventakom@gmail.com']);
        });

    }

    
     public static function mail_contact($content , $received_to = null){
  $setting_email =  DB::table('system_settings')->select('value')->where('name','contact_us')->first();
  $setting_email = $setting_email->value;
//  dd($setting_email);
     Mail::send($content['view'],[ 'name' =>$content['name'],'email' =>$content['email'],'subject'=>$content['subject'],'body'=>$content['message'] , 'to'=>$setting_email ], function($msg) use($content,$received_to,$setting_email){
      if($received_to == 'admin'){
            $msg->to([$setting_email])->subject('Eventakom (Contact Us)');
          }elseif($received_to == 'user'){
            $msg->to([$content['email']])->subject('Eventakom (Contact Us)');
          }
            $msg->from(['pentavalue.eventakom@gmail.com']);
         
        });

    }

  
      public static function isValidTimestamp($timestamp)
    {
       return ((string) (int) $timestamp === $timestamp)
           && ($timestamp <= PHP_INT_MAX)
           && ($timestamp >= ~PHP_INT_MAX);
    }

    /**
     * use this function in order to remove al hamazat and al tashkeel to
     * perform search more accurate in arabic search
     * @param $text
     * @return mixed
     */
    public static function CleanText($text){
        $arr = ['أ' => 'ا',
            'إ' => 'ا',
            'آ' => 'ا',
            "ة" => 'ه',
            "ّ" => '',
            "َّ" => '',
            "ُّ" => '',
            "ٌّ" => '',
            "ًّ" => '',
            "ِّ" => '',
            "ٍّ" => '',
            "ْ" => '',
            "َ" => '',
            "ً" => '',
            "ُ" => '',
            "ِ" => '',
            "ٍ" => '',
            "ٰ" => '',
            "ٌ" => '',
            "ۖ" => '',
            "ۗ" => '',
            "ـ" => ''
        ];
        foreach ($arr as $key => $val) {
            $cleaned_text = str_replace($key, $val, $text);
            $text = $cleaned_text;
        }
        return $text;
    }

      public static function CleanStriptagText($text){
                $text = html_entity_decode($text);
                $text = strip_tags($text);
                $text = str_replace('&nbsp;', '', $text);
                $text = trim(preg_replace('/\s+/', ' ', $text));
                $text = Helpers::CleanText($text);
                return $text;
      }

    public  static function AssginCityAndCountry($lat,$long)
    {

          $url  = "https://maps.googleapis.com/maps/api/geocode/json?key=AIzaSyCknR0jhKTIB33f2CLFhBzgp0mj2Tn2q5k&latlng=".$lat.",".$long."&sensor=false";
          $json = @file_get_contents($url);
          header('Content-Type: application/json;charset=utf-8');

          // echo $json;die;
          $data = json_decode($json);
          $status = $data->status;
          $address = '';
          if($status == "OK")
          {

               $address = $data->results[0]->formatted_address;
              // echo "<br>";
               $iso_code =$data->results[0]->address_components[1]->short_name;
               $country_name = $data->results[0]->address_components[1]->long_name;
              //check country
              $country = GeoCountry::where('iso_code','%LIKE%',$country)->first();
              if(is_null($country))
              {
                  $country  = new GeoCountry;
                  $country->name = $country_name;
                  $country->iso_code = $iso_code;
                  $country->save();
              }

              return $country->id;

          }

    }



}
