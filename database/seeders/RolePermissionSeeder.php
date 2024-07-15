<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {        
        DB::table('roles_permissions')->insert([
            [
                'role_id' => 1,
                'permission_id' => 7,
                'created_user_id' => 1,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                'role_id' => 1,
                'permission_id' => 15,
                'created_user_id' => 1,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                'role_id' => 1,
                'permission_id' => 21,
                'created_user_id' => 1,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                'role_id' => 1,
                'permission_id' => 33,
                'created_user_id' => 1,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                'role_id' => 1,
                'permission_id' => 39,
                'created_user_id' => 1,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
