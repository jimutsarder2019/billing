<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\AdminSetting;
use App\Models\Manager;
use App\Models\SubZone;
use App\Models\Zone;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $faker = Factory::create();

        // Manager::create(['type' => 'app_manager', 'name' => 'manager', 'email' => 'manager@gmail.com', 'phone' => '8801617629336', 'password' => Hash::make('123456'),],);
        AdminSetting::updateOrCreate(['slug' => 'balance', 'value' => 00]);
        // Zone::updateOrCreate(['name' => 'Zone']);
        // SubZone::updateOrCreate(['name' => 'subzone', 'zone_id' => 1]);
        $this->call(
            [
                PermissionsSeeder::class,
                SmsApiSeeder::class,
                // MikrotikSeeder::class,
                // DivisionSeeder::class,
                // DistrictSeeder::class,
                // UpazilaSeeder::class,
            ]
        );
    }
}
