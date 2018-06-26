<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Libraries\Helpers;
use App\Libraries\Base64ToImageService;
use App\User;
use App\Notification;
use App\NotificationType;


class NotificationController extends Controller
{



	public function user_notifications(Request $request){
		//retrive the user Data
		$user = User::where('api_token','=',$request->header('access-token'))->first();
		return Helpers::Get_Response(200, 'success', '', [],$user->notifications()->orderby('created_at','DESC')->get());

	}


	public function mark_read(Request $request,$id){
		//find Notification
		$notification = Notification::find($id);
		if(!$notification){
			return Helpers::Get_Response(403, 'error', 'not found', [], []);
		}
		$notification->is_read = 1;
		$notification->save();
		return Helpers::Get_Response(200, 'success', '', [],[]);
	}

	public function notification_types(Request $request){
		$type = NotificationType::all();
		return Helpers::Get_Response(200, 'success', '', [],$type);


	}



}

