<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            [
                'id' => 1,
                'name' => 'admin',
                'name_fa' => 'ادمین',
                'slug' => 'admin',
                'created_user_id' => 1,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ],
        ]);   
    }
}
