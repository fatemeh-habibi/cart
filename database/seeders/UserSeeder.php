<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'id' => 1,
                'first_name' => 'admin',
                'last_name' => null,
                'username' => 'admin',
                'mobile' => '09123703808',
                'email' => 'admin@gmail.com',
                'image' => null,    
                'password' => Hash::make('123456'),
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'created_user_id' => 1, //because first admin will be create and system create it. //todo
            ]
        ]);
    }
}
