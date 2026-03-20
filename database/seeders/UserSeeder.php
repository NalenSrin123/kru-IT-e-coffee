<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'nalensrin2023@gmail.com',
                'password' => Hash::make('password123'), // Change to a secure password
                'role_id' => DB::table('roles')->where('name', 'Super Admin')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Admin',
                'email' => 'nalensrin3005@gmail.com',
                'password' => Hash::make('password123'), // Change to a secure password
                'role_id' => DB::table('roles')->where('name', 'Admin')->first()->id,
                'is_active' => true,
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['email' => $user['email']],
                $user + ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
