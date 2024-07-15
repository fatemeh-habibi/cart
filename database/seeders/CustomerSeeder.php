<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('customers')->insert([
            [
                'id' => 1,
                'first_name' => 'habibi',
                'last_name' => 'h',
                'mobile' => '09123703808',
                'email' => 'habibi@gmail.com',
                'image' => null,    
                'password' => Hash::make('123456'),
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'created_user_id' => 1, //because first admin will be create and system create it. //todo
            ]
        ]);

    }
}
