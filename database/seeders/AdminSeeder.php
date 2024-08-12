<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('admins')->insert([
            'id' => Str::uuid()->toString(),
            'name' => 'Admin Aksamedia',
            'username' => 'admin',
            'phone' => '082269324126',
            'email' => 'adminaksamedia@gmail.com',
            'password' => Hash::make('pastibisa'),
        ]);
    }
}
