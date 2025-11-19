<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Insert basic settings
        DB::table('settings')->updateOrInsert(
            ['id' => 1],
            [
            'bussiness_name' => 'Allons-Z',
            'logo' => 'logo.png',
            'favicon' => 'favicon.ico',
            'pagination' => 10,
            'msg91_key' => '',
            'msg91_sender' => '',
            'msg91_flow_id' => '',
            'razorpay_key' => 'rzp_test_your_key',
            'razorpay_secret' => 'your_secret',
            'fcm_key' => '',
            'google_map_api_key' => '',
            'call_support_number' => '1800000000',
            'whatsapp_support_number' => '9999999999',
            'email_support' => 'support@allonsz.com',
            'add_type' => 'image',
            'add_url' => 'ads/default.jpg',
            'cancellation_check_amount' => 100,
            'cgst' => 9,
            'sgst' => 9,
            'tds' => 5,
            'admin_charge' => 10,
            'privacy_policy' => 'Privacy policy content here',
            'terms_conditions' => 'Terms and conditions content here',
            'about_us' => 'About us content here',
            'how_it_works' => 'How it works content here',
            'return_and_refund_policy' => 'Return and refund policy here',
            'accidental_policy' => 'Accidental policy here',
            'cancellation_policy' => 'Cancellation policy here',
            'faqs' => 'FAQs content here',
            'created_at' => now(),
            'updated_at' => now(),
            ]
        );

        // Insert countries
        DB::table('countries')->updateOrInsert(
            ['code' => 'IN'],
            ['name' => 'India', 'created_at' => now(), 'updated_at' => now()]
        );
        DB::table('countries')->updateOrInsert(
            ['code' => 'US'],
            ['name' => 'United States', 'created_at' => now(), 'updated_at' => now()]
        );

        // Insert states for India
        DB::table('states')->insert([
            ['name' => 'Andhra Pradesh', 'code' => 'AP', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Telangana', 'code' => 'TS', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Karnataka', 'code' => 'KA', 'country_id' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Insert districts for Andhra Pradesh
        DB::table('districts')->insert([
            ['name' => 'West Godavari', 'state_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'East Godavari', 'state_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Krishna', 'state_id' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Insert mandals
        DB::table('mandals')->insert([
            ['name' => 'Bhimavaram', 'district_id' => 1, 'state_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Narasapuram', 'district_id' => 1, 'state_id' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Insert age ranges
        DB::table('ages')->insert([
            ['range' => '18-25', 'min_age' => 18, 'max_age' => 25, 'created_at' => now(), 'updated_at' => now()],
            ['range' => '26-35', 'min_age' => 26, 'max_age' => 35, 'created_at' => now(), 'updated_at' => now()],
            ['range' => '36-45', 'min_age' => 36, 'max_age' => 45, 'created_at' => now(), 'updated_at' => now()],
            ['range' => '46-60', 'min_age' => 46, 'max_age' => 60, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Insert sample packages
        DB::table('packages')->insert([
            [
                'name' => 'Basic Package',
                'description' => 'Basic circle package',
                'price' => 1000.00,
                'reward_amount' => 200.00,
                'total_members' => 7,
                'max_downlines' => 2,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Premium Package',
                'description' => 'Premium circle package',
                'price' => 5000.00,
                'reward_amount' => 1000.00,
                'total_members' => 13,
                'max_downlines' => 3,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'VIP Package',
                'description' => 'VIP circle package',
                'price' => 10000.00,
                'reward_amount' => 2000.00,
                'total_members' => 21,
                'max_downlines' => 4,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Insert colors for packages
        // Basic Package (7 members)
        $basicColors = [
            ['package_id' => 1, 'position' => 1, 'color' => '#FF6B6B'],
            ['package_id' => 1, 'position' => 2, 'color' => '#4ECDC4'],
            ['package_id' => 1, 'position' => 3, 'color' => '#45B7D1'],
            ['package_id' => 1, 'position' => 4, 'color' => '#96CEB4'],
            ['package_id' => 1, 'position' => 5, 'color' => '#FFEAA7'],
            ['package_id' => 1, 'position' => 6, 'color' => '#DFE6E9'],
            ['package_id' => 1, 'position' => 7, 'color' => '#A29BFE'],
        ];

        // Premium Package (13 members)
        $premiumColors = [];
        for ($i = 1; $i <= 13; $i++) {
            $colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7', '#DFE6E9', '#A29BFE'];
            $premiumColors[] = [
                'package_id' => 2,
                'position' => $i,
                'color' => $colors[($i - 1) % 7]
            ];
        }

        // VIP Package (21 members)
        $vipColors = [];
        for ($i = 1; $i <= 21; $i++) {
            $colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7', '#DFE6E9', '#A29BFE'];
            $vipColors[] = [
                'package_id' => 3,
                'position' => $i,
                'color' => $colors[($i - 1) % 7]
            ];
        }

        // Insert all colors
        foreach (array_merge($basicColors, $premiumColors, $vipColors) as $color) {
            DB::table('colors')->insert([
                'package_id' => $color['package_id'],
                'position' => $color['position'],
                'color' => $color['color'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Insert admin user
        DB::table('users')->updateOrInsert(
            ['username' => 'admin'],
            [
                'role' => 'admin',
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email' => 'admin@example.com',
                'phone' => '9999999999',
                'password' => Hash::make('Admin@123'),
                'email_verified_at' => now(),
                'referal_code' => 'ADMIN123',
                'referal_id' => null,  // Admin has no upline
                'wallet' => 0,
                'status' => 'Active',
                'profile_status' => 'Verified',
                'gender' => 'Male',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        $adminId = DB::table('users')->where('username', 'admin')->value('id');

        // Insert sample tours
        DB::table('tours')->insert([
            [
                'name' => 'Goa Beach Tour',
                'desc' => 'Beautiful beaches of Goa',
                'place' => 'Goa',
                'area' => 'Beach',
                'type' => 'domestic',
                'price' => 5000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kerala Backwaters',
                'desc' => 'Scenic backwaters of Kerala',
                'place' => 'Kerala',
                'area' => 'Backwaters',
                'type' => 'domestic',
                'price' => 8000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

       // Create a circle + members + timer for each package
$packages = DB::table('packages')->get();
foreach ($packages as $package) {
    $circleId = DB::table('circles')->insertGetId([
        'user_id' => $adminId,
        'name' => strtoupper(substr(md5(uniqid()), 0, 8)),
        'package_id' => $package->id,
        'reward_amount' => 0,
        'status' => 'Active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    for ($i = 1; $i <= $package->total_members; $i++) {
        DB::table('members')->insert([
            'circle_id' => $circleId,
            'package_id' => $package->id,
            'position' => $i,
            'user_id' => $i === 1 ? $adminId : null,
            'status' => $i === 1 ? 'Occupied' : 'Empty',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // Seed timer for admin
    DB::table('timers')->insert([
        'user_id' => $adminId,
        'package_id' => $package->id,
        'started_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

    }
}