<?php

namespace App\Library\Services;
use App\Notification;
use App\NotificationPush;
use App\Users;

class NotificationsService
{

	
    
    public function save_notification($message,$type,$entity_id, $item_id,$user_id){
        $notification = new Notification();
        $notification->msg = $message['en'];
        $notification->msg_ar = $message['ar'];
        $notification->entity_id = $entity_id;
        $notification->item_id = $item_id;
        $notification->notification_type_id = $type;
        $notification->is_read = 0;
        $notification->user_id = $user_id;
        $notification->save();
        return $notification;
    }

    /**
    * General function to push to Andriod Devices 
    *
    *
    */
    public  function PushAndroid($message,$device_token){
        
        $registrationIds = array( $device_token );
        // prep the bundle
        $msg = array
        (
            'message'   => $message,
            'title'     => 'This is a title. title',
            'subtitle'  => 'This is a subtitle. subtitle',
            'tickerText'    => 'Ticker text here...Ticker text here...Ticker text here',
            'vibrate'   => 1,
            'sound'     => 1,
            'largeIcon' => 'large_icon',
            'smallIcon' => 'small_icon'
        );
        $fields = array
        (
            'registration_ids'  => $registrationIds,
            'data'          => $msg
        );
         
        $headers = array
        (
            'Authorization: key=' . env('API_ACCESS_KEY'),
            'Content-Type: application/json'
        );
         
        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );      
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
        $result = curl_exec($ch );
        curl_close( $ch );
        header('Content-type:application/json;charset=utf-8');
        echo $result;
    }

    /**
    * General function to push to IOS Devices 
    *
    *
    */    

    public   function PushIos($message, $token, $production = false, $item_id)
    {
        $deviceToken = $token;

        // Put your private key's passphrase here:
        $passphrase = 'Dawa2y';

        // Put your alert message here:
        $message = $message; 
        $url = "penta-test.com";

        if (!$message || !$url)
            exit('Example Usage: $php newspush.php \'Breaking News!\' \'https://raywenderlich.com\'' . "\n");

        ////////////////////////////////////////////////////////////////////////////////

        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert',  public_path() . "EventokomPushCert.pem");
        //stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

        // Open a connection to the APNS server
        if($production){
            $gateway = 'ssl://gateway.push.apple.com:2195';
        }else{
            $gateway = 'ssl://gateway.sandbox.push.apple.com:2195';
        }
        $fp = stream_socket_client(
          $gateway, $err,
          $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
        //'ssl://gateway.push.apple.com:2195'
            //'ssl://gateway.sandbox.push.apple.com:2195'
        if (!$fp)
          exit("Failed to connect: $err $errstr" . PHP_EOL);

        echo 'Connected to APNS' . PHP_EOL;

        // if($lang == 1){
        //  $message_body = $message->translations->message;

        // }else{
        //  $message_body = $message->message;
        // }
        $message_body = $message;
        // Create the payload body
        $body['aps'] = array(
          'alert' => $message_body,
          'item_id' => $item_id,
          'sound' => 'default',
          'badge' => '1'
          );

        // Encode the payload as JSON
        $payload = json_encode($body);

        // Build the binary notification
        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

        // Send it to the server
        $result = fwrite($fp, $msg, strlen($msg));

        if (!$result)
          echo 'Message not delivered' . PHP_EOL;
        else
          echo 'Message successfully delivered' . PHP_EOL;

        // Close the connection to the server
        fclose($fp);
    } 



    public  function PushToManyUsers($users,$notification){
    	foreach($users as $user){
            $queue = new NotificationPush();
            $queue->notification_id = $notification->id;
            $queue->device_token  	= $user->device_token;
            $queue->mobile_os   	= $user->mobile_os;
            $queue->lang_id   		= $user->lang_id;
            $queue->user_id			= $user->id;
            $queue->save();
        }

    }

    public  function PushToSingleUser($user,$notification){
    		$queue = new NotificationPush();
            $queue->notification_id = $notification->id;
            $queue->device_token  	= $user->device_token;
            $queue->mobile_os   	= $user->mobile_os;
            $queue->lang_id   		= $user->lang_id;
            $queue->user_id			= $user->id;
            $queue->save();
    }

    public function EventInterestsPush($event){
    	$categories = $event->categories;
    	if(count($categories)){
	    	foreach ($categories as $category) {
	    		$message['en'] = 'New event added to '.$category->name;
	    		$message['ar'] = 'تم إضافة حدث جديد متعلق ب'.' ' .$category->name;
                foreach ($category->users as $user) {
                    $notification = $this->save_notification(
                    $message,
                    5,//notification_type
                    4,//Entity_id refer to "events"
                    $event->id,
                    $user->id
                    );
                    foreach ($category->users as $user) {
                        $queue = new NotificationPush();
                        $queue->notification_id = $notification->id;
                        $queue->device_token    = $user->device_token;
                        $queue->mobile_os       = $user->mobile_os;
                        $queue->lang_id         = $user->lang_id;
                        $queue->user_id         = $user->id;
                        $queue->save();                        
                    }

                }
                //Push to many users
                // $this->PushToManyUsers($category->users , $notification);
	    	}    		
    	}
    }



}

