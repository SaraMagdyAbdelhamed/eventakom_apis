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
          /*id	username	password	first_name	last_name	email	tele_code	mobile	country_id	city_id	gender_id	photo	birthdate	is_active	created_by	updated_by	created_at	updated_at	device_token	mobile_os	is_social	access_token	social_token	lang_id	verification_code	is_verification_code_expired	last_login	api_token	longtuide	latitude*/
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
              // var_dump(current((array)$validator->errors()));
            return Helpers::Get_Response(403,'error','',$validator->errors(),(object)[]);
           }  
     //    $username = $request->input('first_name').''.$request->input('last_name');
     //    $user = User::create($request->all() + ['username' =>  $username]);
     //    $status= ['code'=>200,'message'=>'User created!','error_details'=>'','validation_error'=>''];
     //    $result= array(
     //     'status'=>$status,
     //     'content'=>$user,

     //    );
     // // print_r($user);
     // // return;
     //    return response()->json($result, 200);
if(array_key_exists('image',$request))
            {
         $request['photo']=Base64ToImageService::convert($request['photo'],'users_images/'); 
            }
          $input = $request;
          
         $input['password'] = Hash::make($input['password']);
         $input['is_active'] = 0;
         $input['username']=$request['first_name'].''.$request['last_name'];
         $input['code']=mt_rand(100000, 999999);        
         $input['verificaition_code'] = str_random(4);
         $input['is_verification_code_expired']=0;
         $user = User::create($input);  
         return Helpers::Get_Response(200,'success','',$validator->errors(),$user);
    }


}