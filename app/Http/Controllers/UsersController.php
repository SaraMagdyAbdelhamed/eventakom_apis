<?php

namespace App\Http\Controllers;

use App\User;
use App\Interest;
use App\FixedPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Libraries\Helpers;
use App\Libraries\Base64ToImageService;
use Illuminate\Support\Facades\Hash;
use App\Libraries\TwilioSmsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use \Illuminate\Support\Facades\Lang;


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
            'token' => '2021c86af33bd8f3b69394a5059c34f0',
            'from' => '+13238701693'
        ];

        $twilio = new TwilioSmsService($twilio_config);

        $request = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request)) {
            Helpers::Set_locale($request['lang_id']);
        }
        $user = new User;
        $validator = Validator::make($request, $user::$rules);

        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', '', $validator->errors(), (object)[]);
        }

        if (array_key_exists('image', $request)) {
            $request['photo'] = Base64ToImageService::convert($request['photo'], 'users_images/');
        }
        $input = $request;
        /*id	username	password	first_name	last_name	email	tele_code	mobile	country_id	city_id	gender_id	photo	birthdate	is_active	created_by	updated_by	created_at	updated_at	device_token	mobile_os	is_social	access_token	social_token	lang_id	mobile_verification_code	is_mobile_verification_code_expired	last_login	api_token	longtuide	latitude*/
        $input['password'] = Hash::make($input['password']);
        $input['is_active'] = 0;
        $input['username'] = $request['first_name'] . '' . $request['last_name'];
        $input['code'] = mt_rand(100000, 999999);
        $input['mobile_verification_code'] = str_random(4);
        $input['is_mobile_verification_code_expired'] = 0;
        
        $user = User::create($input);
        if ($user) {
          $sms_mobile = $request['tele_code'] . '' . $request['mobile'];
          $sms_body = trans('your verification code is : ') . $input['mobile_verification_code'];
          $status = $twilio->send($sms_mobile, $sms_body);
            // $mail=Helpers::mail($request['email'],$input['username'],$input['mobile_verification_code']);
        }
        return Helpers::Get_Response(200, 'success', '', $validator->errors(), $user);
    }


    public function resend_verification_code(Request $request)
    {
        $request = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request)) {
            Helpers::Set_locale($request['lang_id']);
        }

        $validator = Validator::make($request, [
            "mobile" => "required|numeric",
            "lang_id" => "required|in:1,2"

        ]);
        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', '', $validator->errors(), (object)[]);
        } else {
            $twilio_config = [
                'app_id' => 'AC2305889581179ad67b9d34540be8ecc1',
                'token' => '2021c86af33bd8f3b69394a5059c34f0',
                'from' => '+13238701693'
            ];

            $twilio = new TwilioSmsService($twilio_config);

            $user = User::where('mobile', $request['mobile'])->first();
            $mobile_verification_code = str_random(4);
            $sms_mobile = $user->tele_code. '' . $user->mobile;
            $sms_body = trans('your verification code is : ') . $mobile_verification_code;
            $user_date = date('Y-m-d', strtotime($user->verification_date));
            if ($user->is_mobile_verification_code_expired != 1 && $user->verification_count < 5) {

                //send verification code via Email , sms
                //increase verification count by 1
                $user->verification_date = Carbon::now()->format('Y-m-d');
                //$mobile_verification_code =str_random(4);
                $user->mobile_verification_code = $mobile_verification_code;
                $user->verification_count = $user->verification_count + 1;
                if ($user->save()) {
                    //send verification code via Email , sms
                    $status = $twilio->send($sms_mobile, $sms_body);
                    // print_r($status);
                    // return;
                    // $mail=Helpers::mail($user->email,$user->username,$mobile_verification_code);
                }
                return Helpers::Get_Response(200, 'success', '', $validator->errors(), $user);
            } //date_format("Y-m-d", $user->verification_date) dont forget
            elseif ($user->verification_count >= 5 && $user_date != Carbon::now()->format('Y-m-d')) {
                //set is_mobile_verification_code_expired to 0
                $user->is_mobile_verification_code_expired = 0;
                //reset verification count to 0
                $user->verification_count = 0;
                // update verification date to current date
                $user->verification_date = Carbon::now()->format('Y-m-d');

                //increase verification count by 1
                $user->verification_count = $user->verification_count + 1;

                if ($user->save()) {
                    //send verification code via Email , sms
                    $status = $twilio->send($sms_mobile, $sms_body);
                    // print_r($status);
                    // return;
                    // $mail=Helpers::mail($user->email,$user->username,$mobile_verification_code);
                }
                return Helpers::Get_Response(200, 'success', '', $validator->errors(), $user);
            } elseif ($user->verification_count >= 5 && $user_date == Carbon::now()->format('Y-m-d')) {
                //set is_mobile_verification_code_expired to 1
                $user->is_mobile_verification_code_expired = 1;
                // response : sorry you have exeeded your verifications limit today
                return Helpers::Get_Response(400, 'error', trans('sorry you have exceeded your verifications limit today'), $validator->errors(), (object)[]);
            } elseif ($user->is_mobile_verification_code_expired = 1 && $user->verification_count < 5 && $user_date == Carbon::now()->format('Y-m-d')) {
                $user->is_mobile_verification_code_expired = 0;
                //send verification code via Email , sms
                //increase verification count by 1
                $user->verification_date = Carbon::now()->format('Y-m-d');
                //$mobile_verification_code =str_random(4);
                $user->mobile_verification_code = $mobile_verification_code;
                $user->verification_count = $user->verification_count + 1;
                if ($user->save()) {
                    //send verification code via Email , sms
                    $status = $twilio->send($sms_mobile, $sms_body);
                    // print_r($status);
                    // return;
                    // $mail=Helpers::mail($user->email,$user->username,$mobile_verification_code);
                }
                return Helpers::Get_Response(200, 'success', '', $validator->errors(), $user);

            }

        }
    }

    public function verify_verification_code(Request $request)
    {

        $request = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request)) {
            Helpers::Set_locale($request['lang_id']);
        }
        $validator = Validator::make($request,
            [
                "mobile" => "required|regex:/^\+?[^a-zA-Z]{5,}$/",
                "mobile_verification_code" => "required",
                "lang_id" => "required|in:1,2"
            ]);
        if ($validator->fails()) {
            // var_dump(current((array)$validator->errors()));
            return Helpers::Get_Response(403, 'error', '', $validator->errors(), (object)[]);
        }
        $user = User::where('mobile', $request['mobile'])->first();
// dd($user->name);
        if ($user) {
            if ($user->mobile_verification_code == $request['mobile_verification_code']) {

                $user->is_mobile_verification_code_expired = 1;
                if ($user->is_active == 0) {

                    $user->update(['is_active' => 1, 'verification_date' => Carbon::now()->format('Y-m-d')]);

                }


                $user->save();
            } else {


                return Helpers::Get_Response(400, 'error', trans('messages.wrong_verification_code'), $validator->errors(), (object)[]);


            }
        } else {
            return Helpers::Get_Response(400, 'error', trans('Mobile number is not registered'), $validator->errors(), (object)[]);
        }


        return Helpers::Get_Response(200, 'success', '', $validator->errors(), $user);

    }


    public function reset_password(Request $request)
    {

        $request = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request)) {
            Helpers::Set_locale($request['lang_id']);
        }
        $validator = Validator::make($request,
            [
                "mobile" => "required|regex:/^\+?[^a-zA-Z]{5,}$/",
                "new_password" => "required|min:6|max:20",
                "confirm_password" => "required|min:6|max:20|same:new_password",
            ]);
        if ($validator->fails()) {
            // var_dump(current((array)$validator->errors()));
            return Helpers::Get_Response(403, 'error', '', $validator->errors(), (object)[]);
        }
        $user = User::where('mobile', $request['mobile'])->first();
        if ($user) {
            if ($user->verificaition_code == $request['verificaition_code'] && $user->is_mobile_verification_code_expired == 1) {
                $user->update(['password' => Hash::make($request['confirm_password'])]);

            } else {
                // echo $user->verificaition_code;
                return Helpers::Get_Response(400, 'error', trans('Invalid verification code, please write the right one'), $validator->errors(), $user);

            }
        } else {
            return Helpers::Get_Response(400, 'error', trans('messages.mobile'), $validator->errors(), []);
        }

        return Helpers::Get_Response(200, 'success', '', $validator->errors(), $user);

    }


    public function login(Request $request)
    {
        $request = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request)) {
            Helpers::Set_locale($request['lang_id']);
        }
        $validator = Validator::make($request, [
            "mobile" => "required|numeric",
            "password" => "required|min:8|max:20",
//            "device_token"=>'required',
//            "lang_id"=>'required',
//            "mobile_os"=>'required',
        ]);
        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', '', $validator->errors(), (object)[]);
        }
        if (array_key_exists('mobile', $request) && array_key_exists('password', $request)) {

            //////

            if (is_numeric($request['mobile'])) {
                $user = User::where("mobile", "=", $request['mobile'])->with('rules')->first();

                if (!$user) {
                    return Helpers::Get_Response(400, 'error', trans('this mobile number isn’t registered'), $validator->errors(), (object)[]);
                }
            }
            // elseif (filter_var($request['mobile'], FILTER_VALIDATE_EMAIL)) {
            //   $user = User:: where("email", "=", $request['mobile'])->with('rules')->first();
            //   if(!$user) {
            //    return Helpers::Get_Response(400,'error',trans('this e-mail isn’t registered'),$validator->errors(),(object)[]);}
            // }

            //////

            // $user = User:: where("mobile", "=", $request['mobile_email'])->with('rules')->first();
            if ($user) {
                if (Hash::check($request['password'], $user->password)) {
                    if ($user->is_active == 1) {
                        $tokenobj = $user->createToken('api_token');
                        $token = $tokenobj->accessToken;
                        $token_id = $tokenobj->token->id;
                        //$user = new User;
                        $user->api_token = $token_id;
                        $user->created_at = Carbon::now()->format('Y-m-d H:i:s');
                        $user->updated_at = Carbon::now()->format('Y-m-d H:i:s');
                        $user->save();
                        // $user_array = $user->toArray();           
                        // foreach ($user_array['rules'] as  $value) {
                        //     if(array_key_exists('lang_id',$request) && $request['lang_id']==1) {
                        //         $rules []=  array($value['id'] => $value['name']);
                        //     } else {
                        //         $rules []= array($value['id'] => $value['name_ar']);          
                        //     }
                        //     $rule_ids [] = $value['id'];
                        // }
                        // $user_array['rule_ids']  = $rule_ids;
                        // $user_array['rules'] = $rules;
                        // $user['roles']=$rules;
                        if ($user['image'] != null) {
                            $user['image'] = ENV('FOLDER') . $user_array['image'];
                        }
//                        $user->update([
//                            "device_token"=>$request['device_token'],
//                            "lang_id"=>$request['lang_id']
//                            "mobile_os"=>$request['mobile_os'],
//                        ]);
                        return Helpers::Get_Response(200, 'success', '', $validator->errors(), $user);
                    } else {
                        return Helpers::Get_Response(400, 'error', trans('messages.active'), $validator->errors(), (object)[]);
                    }
                }
                return Helpers::Get_Response(400, 'error', trans('Password is wrong'), $validator->errors(), (object)[]);
            } else {
                return Helpers::Get_Response(400, 'error', trans('this mobile number isn’t registered'), $validator->errors(), (object)[]);
            }
            return Helpers::Get_Response(200, 'success', '', $validator->errors(), $user);
        } else {
            return Helpers::Get_Response(401, 'error', trans('Invalid mobile number'), $validator->errors(), (object)[]);
        }
    }


    public function logout(Request $request)
    {
        $api_token = $request->header('access-token');
        //dd($request->header('api_token'));
        // $request_header = (array)json_decode($request->header('api_token'), true);
        $request = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request)) {
            Helpers::Set_locale($request['lang_id']);
        }
        // dd($request_header);
        // if(array_key_exists('api_token',$request) && $request['api_token'] != '')
        // {

        // $user=User:: where("api_token", "=",  $api_token )
        //              ->first();
        $user = User:: where("api_token", "=", $api_token)
            ->first();
        if ($user) {
            $user->update(['api_token' => null]);
            $user->save();
            return Helpers::Get_Response(200, 'success', '', '', $user);
        } else {
            return Helpers::Get_Response(400, 'error', trans('messages.logged'), [], (object)[]);
        }
        // }else{
        //   return Helpers::Get_Response(400,'error',trans('messages.logged'),[],(object)[]);
        // }
    }

    public function change_language(Request $request)
    {
        $api_token = $request->header('access-token');
     
        $request = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request)) {
            Helpers::Set_locale($request['lang_id']);
        }
        $validator = Validator::make($request, [
            "lang_id" => "required|in:1,2"

        ]);

        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', '', $validator->errors(), (object)[]);
        } else {
            $user = User:: where("api_token", "=", $api_token)->first();

            if ($user) {
                $user->update(['lang_id' => $request['lang_id']]);
                $user->save();
                return Helpers::Get_Response(200, 'success', '', '', $user);
            } else {

                return Helpers::Get_Response(400, 'error', trans('No user Registerd with this token'), $validator->errors(), (object)[]);

            }
        }

    }


    public function fixed_pages(Request $request)
    {

        $pages = FixedPage::all();

        if ($pages) {

            return Helpers::Get_Response(200, 'success', '', '', $pages);
        } else {

            return Helpers::Get_Response(400, 'error', trans('No pages found'), $validator->errors(), (object)[]);

        }


    }


    public function mail_existence(Request $request)
    {
        $request = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request)) {
            Helpers::Set_locale($request['lang_id']);
        }
        $validator = Validator::make($request, [
            "email" => "required|exists:users,email",

        ]);

        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', '', $validator->errors(), (object)[]);
        } else {

            return Helpers::Get_Response(200, 'success', '', $validator->errors(), trans('Email is exist'));

        }

    }


    public function mobile_existence(Request $request)
    {
        $request = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request)) {
            Helpers::Set_locale($request['lang_id']);
        }
        $validator = Validator::make($request, [
            "mobile" => "required|exists:users,mobile",

        ]);

        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', '', $validator->errors(), (object)[]);
        } else {

            return Helpers::Get_Response(200, 'success', '', $validator->errors(), trans('Interests added to user'));

        }

    }


    //user interets


    public function add_interests(Request $request)
    {
        //read the input
        $request_data = (array)json_decode($request->getContent(), true);
        //validate
        $validator = Validator::make($request_data,
            ["interest" => "required"]);
        //check validation result
        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', '', $validator->errors(), (object)[]);

        } else {
            $api_token = $request->header('access-token');
            $interest = new Interest();
            $interest->name = $request['interest'];
            $interest->created_by = User::where('api_token', '=', $api_token)->first()->id;
            $interest->save();


            return Helpers::Get_Response(200, 'success', '', $validator->errors(), $interest);


        }

    }

    public function add_user_interests(Request $request)
    {
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request['lang_id']);
        }
        $validator = Validator::make($request_data, [

            "interests" => "required"

        ]);

        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', '', $validator->errors(), (object)[]);
        } else {

            $user = User:: where("api_token", "=", $request->header('access-token'))->first();
            if ($user) {
                $user_id = $user->id;
                //interest where in ids
                $interests_ids = $request_data['interests'];
                $user->interests()->attach($interests_ids);


            } else {

                return Helpers::Get_Response(400, 'error', trans('user not exist'), $validator->errors(), (object)[]);
            }

            return Helpers::Get_Response(200, 'success', '', $validator->errors(), trans('user interests added'));

        }

    }


    public function edit_user_interests(Request $request)
    {
        $request_data = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request_data)) {
            Helpers::Set_locale($request['lang_id']);
        }
        $validator = Validator::make($request_data, [

            "interests" => "required"

        ]);

        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', '', $validator->errors(), (object)[]);
        } else {

            $user = User:: where("api_token", "=", $request->header('access-token'))->first();
            if ($user) {

                //interest where in ids
                $interests_ids = $request_data['interests'];
                //remove old interests
                $user->interests()->detach();
                //sent new interests
                $user->interests()->attach($interests_ids);


            } else {

                return Helpers::Get_Response(400, 'error', trans('user not exist'), $validator->errors(), (object)[]);
            }

            return Helpers::Get_Response(200, 'success', '', $validator->errors(), trans('users interests updated'));

        }

    }

    public function user_interests(Request $request)
    {
        //return all user interests
        $user = User::where("api_token", '=', $request->header('access-token'))->first();
        return Helpers::Get_Response(200, 'success', '', [], $user->interests);


    }

    public function all_interests()
    {
        $interests = Interest::all();
        return Helpers::Get_Response(200, 'success', '', [], $interests);


    }


    //password
    public function change_password(Request $request)
    {
        //read the request
        $request_data = (array)json_decode($request->getContent(), true);
        //valdiation
        $validator = Validator::make($request_data,
            ["new_password" => "required|Between:8,20"]);
        //check validation result
        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', '', $validator->errors(), (object)[]);

        } else {
            $user = User::where('api_token', '=', $request->header('access-token'))->first();
            $user->password = Hash::make($request_data['new_password']);
            $user->save();
            return Helpers::Get_Response(200, 'success', '', $validator->errors(), trans('Password Changed Successfully'));


        }


    }

    public function edit_profile(Request $request)
    {


        $api_token = $request->header('access-token');
        //dd($api_token);
        $user = User:: where("api_token", "=", $api_token)->first();

        $request = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request)) {
            Helpers::Set_locale($request['lang_id']);
        }
        if ($user->email == $request['email']) {
            $email_valid = 'required|email|max:35';
        } else {
            $email_valid = 'required|email|unique:users|max:35';
        }
        $validator = Validator::make($request,
            [
                'first_name' => 'required|between:1,12',
                'last_name' => 'required|between:1,12',
                'email' => $email_valid,
                'conutry_code_id' => 'required',
                // 'mobile' => 'required|numeric|unique:users',
                'password' => 'required|between:8,20',
                'photo' => 'image|max:1024',
                //'device_token' => 'required',
                'mobile_os' => 'in:android,ios',
                'lang_id' => 'in:1,2'

            ]);
        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', '', $validator->errors(), (object)[]);
        }

        if (array_key_exists('image', $request)) {
            $request['photo'] = Base64ToImageService::convert($request['photo'], 'users_images/');
        }
        $input = $request;
        /*id username  password  first_name  last_name email tele_code mobile  country_id  city_id gender_id photo birthdate is_active created_by  updated_by  created_at  updated_at  device_token  mobile_os is_social access_token  social_token  lang_id mobile_verification_code is_mobile_verification_code_expired  last_login  api_token longtuide latitude*/
        if (Hash::check($request['password'], $user->password)) {
            $input['password'] = $user->password;
        } else {

            $input['password'] = Hash::make($input['password']);
        }
        //$input['is_active'] = 0;
        $input['username'] = $request['first_name'] . '' . $request['last_name'];
        $input['mobile'] = $user->mobile;
        //$input['code']=mt_rand(100000, 999999);
        $input['email_verification_code'] = str_random(4); //change it to email_verification_code
        //$input['is_mobile_verification_code_expired']=0;
        $old_email = $user->email;
        $user_update = $user->update($input);
        if ($user_update && $old_email != $request['email']) {
            //$status =$twilio->send($request['mobile'],$input['mobile_verification_code']);
         $mail = Helpers::mail($request['email'], $input['username'], $input['email_verification_code']);
            $user->update(['is_email_verified' => 0]);
        }
        return Helpers::Get_Response(200, 'success', '', $validator->errors(), $user);
    }

    public function set_new_password(Request $request)
    {
        //read the request
        $request_data = (array)json_decode($request->getContent(), true);
        //valdiation
        $validator = Validator::make($request_data,
            ["new_password" => "required|Between:8,20", "old_password" => "required|Between:8,20"]);
        //check validation result
        if ($validator->fails()) {
            return Helpers::Get_Response(403, 'error', '', $validator->errors(), (object)[]);

        } else {
            $user = User::where('api_token', '=', $request->header('access-token'))->first();

            if (Hash::check($request_data['old_password'], $user->password)) {
                $user->password = Hash::make($request_data['new_password']);
                $user->save();
                return Helpers::Get_Response(200, 'success', '', $validator->errors(), trans('Password Changed Successfully'));


            } else {
                return Helpers::Get_Response(401, 'faild', 'Wrong user Password', [], trans('Wrong user Password'));


            }


        }


    }


        public function verify_email(Request $request)
    {

        $api_token = $request->header('access-token');
        // $user = User:: where("api_token", "=", $api_token)->first();

        $request = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request)) {
            Helpers::Set_locale($request['lang_id']);
        }
        $validator = Validator::make($request,
            [
                "email" => "required|email",
                "email_verification_code" => "required",
                "lang_id" => "required|in:1,2"
            ]);
        if ($validator->fails()) {
            // var_dump(current((array)$validator->errors()));
            return Helpers::Get_Response(403, 'error', '', $validator->errors(), (object)[]);
        }
        $user = User::where('email', $request['email'])->where("api_token", "=", $api_token)->first();
// dd($user->name);
        if ($user) {
            if ($user->email_verification_code == $request['email_verification_code']) {

               // $user->is_mobile_verification_code_expired = 1;
                if ($user->is_email_verified == 0) {

                    $user->update(['is_email_verified' => 1]);

                }


                $user->save();
            } else {


                return Helpers::Get_Response(400, 'error', trans('messages.wrong_mobile_verification_code'), $validator->errors(), (object)[]);


            }
        } else {
            return Helpers::Get_Response(400, 'error', trans('Email is not registered'), $validator->errors(), (object)[]);
        }


        return Helpers::Get_Response(200, 'success', '', $validator->errors(), $user);

    }


    public function forget_password(Request $request)
    {

        $request = (array)json_decode($request->getContent(), true);
        if (array_key_exists('lang_id', $request)) {
            Helpers::Set_locale($request['lang_id']);
        }
        $validator = Validator::make($request,
            [
                "mobile" => "required|regex:/^\+?[^a-zA-Z]{5,}$/",
                "mobile_verification_code" => "required",
                "new_password" => "required|between:8,20"
            ]);
        if ($validator->fails()) {

            return Helpers::Get_Response(403, 'error', '', $validator->errors(), (object)[]);
        }
        $user = User::where('mobile', $request['mobile'])->first();

        if ($user) {
            if ($user->mobile_verification_code == $request['mobile_verification_code']) {

                $user->is_mobile_verification_code_expired = 1;

                $new_password = Hash::make($request['new_password']);
                $user->update(['password' => $new_password]);


                $user->save();
            } else {


                return Helpers::Get_Response(400, 'error', trans('messages.wrong_mobile_verification_code'), $validator->errors(), (object)[]);


            }
        } else {
            return Helpers::Get_Response(400, 'error', trans('Mobile number is not registered'), $validator->errors(), (object)[]);
        }


        return Helpers::Get_Response(200, 'success', '', $validator->errors(), $user);

    }


    // Social Login
    public  function social_login(Request $request){


    }




    //test SMS
    public function sms(Request $request){
         $twilio_config = [
            'app_id' => 'AC2305889581179ad67b9d34540be8ecc1',
            'token' => '2021c86af33bd8f3b69394a5059c34f0',
            'from' => '+13238701693'
        ];
         $request = (array)json_decode($request->getContent(), true);

        $twilio = new TwilioSmsService($twilio_config);
           $sms_mobile = $request['tele_code'] . '' . $request['mobile'];
          $sms_body = trans('your verification code is : ') . '2582';
          $status = $twilio->send($sms_mobile, $sms_body);
          dd($status);

    }
}