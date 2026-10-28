<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@pos.test'],
 memory-benchmark
            [
                'name' => 'Admin Kafe',
                'role' => 'admin',
                'password' => 'password',
            ]
=======
            ['name' => 'Admin Kafe', 'role' => 'admin', 'password' => 'password'],
main
        );

        User::firstOrCreate(
            ['email' => 'kasir@pos.test'],
 memory-benchmark
            [
                'name' => 'Kasir Kafe',
                'role' => 'kasir',
                'password' => 'password',
            ]
=======
            ['name' => 'Kasir Kafe', 'role' => 'kasir', 'password' => 'password'],
        );

        User::firstOrCreate(
            ['email' => 'manager@pos.test'],
            ['name' => 'Manager Kafe', 'role' => 'manager', 'password' => 'password'],
 main
        );
    }
}