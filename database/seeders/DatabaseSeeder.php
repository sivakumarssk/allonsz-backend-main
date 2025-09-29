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
        DB::table('settings')->insert([
            ['key' => 'app_name', 'value' => 'Circle App', 'type' => 'text', 'group' => 'general'],
            ['key' => 'logo', 'value' => 'logo.png', 'type' => 'file', 'group' => 'general'],
            ['key' => 'favicon', 'value' => 'favicon.ico', 'type' => 'file', 'group' => 'general'],
        ]);

        // Insert countries
        DB::table('countries')->insert([
            ['name' => 'India', 'code' => 'IN'],
            ['name' => 'United States', 'code' => 'US'],
        ]);

        // Insert states for India
        DB::table('states')->insert([
            ['name' => 'Andhra Pradesh', 'code' => 'AP', 'country_id' => 1],
            ['name' => 'Telangana', 'code' => 'TS', 'country_id' => 1],
            ['name' => 'Karnataka', 'code' => 'KA', 'country_id' => 1],
        ]);

        // Insert districts for Andhra Pradesh
        DB::table('districts')->insert([
            ['name' => 'West Godavari', 'state_id' => 1],
            ['name' => 'East Godavari', 'state_id' => 1],
            ['name' => 'Krishna', 'state_id' => 1],
        ]);

        // Insert mandals
        DB::table('mandals')->insert([
            ['name' => 'Bhimavaram', 'district_id' => 1, 'state_id' => 1],
            ['name' => 'Narasapuram', 'district_id' => 1, 'state_id' => 1],
        ]);

        // Insert age ranges
        DB::table('ages')->insert([
            ['range' => '18-25', 'min_age' => 18, 'max_age' => 25],
            ['range' => '26-35', 'min_age' => 26, 'max_age' => 35],
            ['range' => '36-45', 'min_age' => 36, 'max_age' => 45],
            ['range' => '46-60', 'min_age' => 46, 'max_age' => 60],
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
                'status' => 'active'
            ],
            [
                'name' => 'Premium Package',
                'description' => 'Premium circle package',
                'price' => 5000.00,
                'reward_amount' => 1000.00,
                'total_members' => 13,
                'max_downlines' => 3,
                'status' => 'active'
            ],
            [
                'name' => 'VIP Package',
                'description' => 'VIP circle package',
                'price' => 10000.00,
                'reward_amount' => 2000.00,
                'total_members' => 21,
                'max_downlines' => 4,
                'status' => 'active'
            ],
        ]);

        // Insert admin user
        DB::table('users')->insert([
            'role' => 'admin',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'phone' => '9999999999',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'referal_code' => 'ADMIN123',
            'wallet' => 0,
            'status' => 'Active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert sample tours
        DB::table('tours')->insert([
            [
                'name' => 'Goa Beach Tour',
                'description' => 'Beautiful beaches of Goa',
                'location' => 'Goa, India',
                'price' => 5000.00,
                'start_date' => '2025-01-01',
                'end_date' => '2025-01-05'
            ],
            [
                'name' => 'Kerala Backwaters',
                'description' => 'Scenic backwaters of Kerala',
                'location' => 'Kerala, India',
                'price' => 8000.00,
                'start_date' => '2025-02-01',
                'end_date' => '2025-02-07'
            ],
        ]);
    }
}