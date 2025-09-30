<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use \App\Models\Booking;
use \App\Models\Notification;
use \App\Models\Vehicle;
use Mail;

class PendingDeposit extends Command
{

    protected $signature = 'pending:deposit';

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
        $vehicles = Vehicle::where('security_paid','No')->get();
        foreach($vehicles as $vehicle){
            $info = array(
            'vehicle' => $vehicle
            );
            try{
                Mail::send('email.vehicle_deposit_pending', $info, function ($message) use ($vehicle)
                {
                    $message->to($vehicle->vendor->email, $vehicle->vendor->name)
                    ->subject('Vehicle Refundable Deposit Pending');
                });
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}
