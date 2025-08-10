<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $user = User::factory()->create([
            'name' => 'user',
            'email' => 'user@wafik.net',
            'username' => 'user',
            'phone' => '6281234567891',
            'password' => bcrypt('12345678'),
        ]);
        $adminaga = User::factory()->create([
            'name' => 'Aga Aulia',
            'email' => 'agaaulia@gmail.com',
            'username' => 'agaaulia',
            'phone' => '+6281286227638',
            'password' => bcrypt('12345678'),
        ]);

        $administrator = User::factory()->create([
            'name' => 'Admin ',
            'email' => 'admin@wafik.net',
            'username' => 'adminulin',
            'phone' => '6281234567890',
            'password' => bcrypt('12345678'),
        ]);

        $user->assignRole(Role::findByName('user', 'web'));
        $adminaga->assignRole(Role::findByName('administrator', 'web'));
        $administrator->assignRole(Role::findByName('administrator', 'web'));
    }
}
