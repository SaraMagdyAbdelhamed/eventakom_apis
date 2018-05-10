<?php
namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Libraries\Helpers;
use App\Libraries\Base64ToImageService;
use Illuminate\Support\Facades\Hash;
use App\Libraries\TwilioSmsService;

class UsersController extends Controller
{

    public function getAllUsers()
    {
        return response()->json(User::all());
    }

     public function signup(Request $request)
    {
          
          $twilio_config = [
            'app_id' => 'AC2305889581179ad67b9d34540be8ecc1',
            'token'  => '2021c86af33bd8f3b69394a5059c34f0',
            'from'   => '+13238701693'
        ];

        $twilio = new TwilioSmsService($twilio_config);

 $request = (array)json_decode($request->getContent(), true);
        if(array_key_exists('lang_id',$request)) {
            Helpers::Set_locale($request['lang_id']);
        }
      $user = new User;
        $validator = Validator::make($request,$user::$rules);

          if ($validator->fails()) 
           {
            return Helpers::Get_Response(403,'error','',$validator->errors(),(object)[]);
           }  
  
if(array_key_exists('image',$request))
            {
         $request['photo']=Base64ToImageService::convert($request['photo'],'users_images/'); 
            }
          $input = $request;
         /*id	username	password	first_name	last_name	email	tele_code	mobile	country_id	city_id	gender_id	photo	birthdate	is_active	created_by	updated_by	created_at	updated_at	device_token	mobile_os	is_social	access_token	social_token	lang_id	verification_code	is_verification_code_expired	last_login	api_token	longtuide	latitude*/ 
         $input['password'] = Hash::make($input['password']);
         $input['is_active'] = 0;
         $input['username']=$request['first_name'].''.$request['last_name'];
         $input['code']=mt_rand(100000, 999999);        
         $input['verification_code'] = str_random(4);
         $input['is_verification_code_expired']=0;
         $status =$twilio->send($request['mobile'],$input['verification_code']);
         $mail=Helpers::mail($request['email'],$input['username'],$input['verification_code']);
         $user = User::create($input);  
         return Helpers::Get_Response(200,'success','',$validator->errors(),$user);
    }



     public function verify_verification_code(Request $request)
    {
      
      $request = (array)json_decode($request->getContent(), true);
      if(array_key_exists('lang_id',$request))
          {
            Helpers::Set_locale($request['lang_id']);
          }
       $validator = Validator::make($request,
        [
           "mobile" => "required|regex:/^\+?[^a-zA-Z]{5,}$/",
           "verification_code"=>"required",
           "lang_id" => "required|in:1,2"
           ]);
       if ($validator->fails()) 
           {
              // var_dump(current((array)$validator->errors()));
            return Helpers::Get_Response(403,'error','',$validator->errors(),(object)[]);
           }
      $user = User::where('mobile',$request['mobile'])->first();
// dd($user->name);
      if($user)
      {
        if($user->verification_code == $request['verification_code'])
      {
        
        $user->is_verification_code_expired=1;
        if($user->is_active==0)
        {
       
          $user->update(['is_active'=>1]);
        }
        
        
        $user->save();
      }
      else
      {
      
         
         return Helpers::Get_Response(400,'error',trans('Invalid verification code, please write the right one'),$validator->errors(),(object)[]);
       
        
      }
      }
      else
      {
        return Helpers::Get_Response(400,'error',trans('Mobile number is not registered'),$validator->errors(),(object)[]);
      }
      
      
      return Helpers::Get_Response(200,'success','',$validator->errors(),$user);
      
    }


    public function reset_password(Request $request)
    {
       
        $request = (array)json_decode($request->getContent(), true);
        if(array_key_exists('lang_id',$request))
          {
            Helpers::Set_locale($request['lang_id']);
          }
         $validator = Validator::make($request,
          [
             "mobile" => "required|regex:/^\+?[^a-zA-Z]{5,}$/",
             "new_password"=>"required|min:6|max:20",
             "confirm_password" => "required|min:6|max:20|same:new_password",
           ]);
         if ($validator->fails()) 
           {
              // var_dump(current((array)$validator->errors()));
            return Helpers::Get_Response(403,'error','',$validator->errors(),(object)[]);
           }
        $user = User::where('mobile',$request['mobile'])->first();
        if($user)
        {
        if($user->verificaition_code==$request['verificaition_code'] && $user->is_verification_code_expired==1)
        {
          $user->update(['password'=>Hash::make($request['confirm_password'])]);
          
        }
        else
        {
            // echo $user->verificaition_code;
            return Helpers::Get_Response(400,'error',trans('Invalid verification code, please write the right one'),$validator->errors(),$user);
          
        }
        }
        else
        {
          return Helpers::Get_Response(400,'error',trans('messages.mobile'),$validator->errors(),[]);
        }
       
          return Helpers::Get_Response(200,'success','',$validator->errors(),$user);
      
    }


}