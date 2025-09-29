<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use \App\Models\Booking;
use \App\Models\Notification;

class BookingEnd extends Command
{

    protected $signature = 'booking:end';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $time = date('Y-m-d h:i:s');
        $bookings = Booking::where('to_time', '<=', $time)->where('status', 'Approved')->get();
        foreach($bookings as $booking){
            
            $booking->status = 'Completed';
            $booking->save();
            
            $token = $booking->user->device_token;
            $title = 'Booking completed';
            $body = 'Hell Mr '.$booking->user->name.' Your booking has been ended automatically on '.$booking->to_time.' Booking Id '.$booking->id;
            
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
