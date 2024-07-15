<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            SettingSeeder::class,
            LanguageSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            UserSeeder::class,
            CustomerSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            RolePermissionSeeder::class,
            UserRoleSeeder::class,
            CitySeeder::class,
        ]);
    }
}
