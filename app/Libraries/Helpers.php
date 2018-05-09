<?php

//created_by: Ash

namespace App\Libraries;
use App\Entities;
use Illuminate\Support\Facades\Mail;

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
        app('translator')->setLocale('ar');
      }
     else  if($locale == 2)
      {
        app('translator')->setLocale('en');
      }
  }

  public static function  localizations($table_name , $field , $item_id)
    {
      $value_localized = Entities::where('name',$table_name)->with([
        'localizations' => function($query) use($field, $item_id)
        {
            $query->where('field', $field)->where('item_id', $item_id);
        }
      ])->get();
      foreach ($value_localized as  $value) {
        
        foreach ($value->localizations as $value1) {
            return $value1->value;       
        }
      }
     
        
    }


       public static function mail($email ,$code ,$verification_code){
        Mail::raw('Welcome To avocatoapp   Your code is ('.$code.' ) And Your Verification code is ('.$verification_code.')', function($msg) use($email){ 
            $msg->to([$email])->subject('SecureBridge'); 
            $msg->from(['pentavalue.securebridge@gmail.com']); 

          });
    }

    public static function mail_contact($body){
        Mail::raw('Welcome To avocatoapp   New Feedback'.$body, function($msg){ 
            $msg->to(['info@avocatoapp.net'])->subject('SecureBridge'); 
            $msg->from(['pentavalue.securebridge@gmail.com']); 

          });
    }

    
}