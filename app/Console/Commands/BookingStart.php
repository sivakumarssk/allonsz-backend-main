<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use \App\Models\Booking;
use \App\Models\Notification;

class BookingStart extends Command
{

    protected $signature = 'booking:start';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // \Log::info("Cron is working fine!");
        $time = date('Y-m-d h:i:s');
        $twentyplus = strtotime("+15 minutes", strtotime($time));
        $bookings = Booking::whereBetween('from_time', [$time, $twentyplus])->get();
        foreach($bookings as $booking){
            
            $token = $booking->user->device_token;
            $title = 'Booking will start on '.$booking->from_time;
            $body = 'Hell Mr '.$booking->user->name.' Your booking will start on '.$booking->from_time.' Booking Id '.$booking->id;
            
            $SERVER_API_KEY = \App\Models\Setting::first()->fcm_key;
        $data = [
            "registration_ids" => $token,
            "notification" => [
                "title" => $title,
                "body" => $body,
            ]
        ];
        $dataString = json_encode($data);
        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        $response = curl_exec($ch);
        
            $notification = new Notification();
            $notification->user_id = $booking->user_id;
            $notification->from_id = 1;
            $notification->title = $title;
            $notification->body = $body;
            $notification->save();
        }
    }
}
