<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use \App\Models\Coupon;
use \App\Models\Notification;

class CouponExpire extends Command
{

    protected $signature = 'coupon:expire';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $date = date('Y-m-d');
        $coupons = Coupon::where('to', '<', $date)->get();
        foreach($coupons as $coupon){
            $coupon->status = 'Expired';
            $coupon->save();
        }
        
    }
}
