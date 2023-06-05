<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{

    public function testnotify(Request  $request)
    {
        return Self::sendNotificationToUser(
            $request->title, 
            $request->body, 
            $request->user_id, 
            $request->notification_type, 
            $request->chat_id
        );
    }

    public static function sendNotificationToUser($title, $body, $client_id, $type, $chat_id, $image = NULL)
    {

        $url = env('FCM_URL', null);

        $FcmToken = User::whereNotNull("device_key")
            ->where('id', $client_id)
            ->pluck("device_key")
            ->first();

        $serverKey = env('FCM_SERVER_KEY', null);
        $dataArr = [
            "type" => $type,
            "chat_id" => $chat_id,
            "click_action" => "FLUTTER_NOTIFICATION_CLICK",
            "status" => "done",
            "image" => $image
        ];

        $data = [
            "registration_ids" => [$FcmToken],
            "notification" => [
                "title" => $title, //str
                "body" => $body, //str
                "sound" => "default",
                "image" => $image
            ],
            "data" => $dataArr,
            "priority" => "high",
        ];
        $encodedData = json_encode($data);

        $headers = [
            "Authorization:key=" . $serverKey,
            "Content-Type: application/json",
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);

        // Execute post
        $result = curl_exec($ch);

        if ($result === FALSE) {
            die("Curl failed: " . curl_error($ch));
        }

        // Close connection
        curl_close($ch);

        // FCM response
        // dd($result);
        return response()->json([
            'message' => 'success',
            'r' => $result,
            'success' => true,
        ], 200);
    }
}
