<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ១. រកមើល ID របស់ Super Admin សិន
        $superAdminRole = Role::where('name', 'Super Admin')->first();

        // ២. បើមាន Role នេះ ទើបយើងបង្កើតគណនី
        if ($superAdminRole) {
            User::updateOrCreate(
                ['email' => 'slesrofath2203@gmail.com'], // ប្រើអ៊ីមែលនេះជាគោល
                [
                    'name'      => 'Mr. Super Admin',
                    'role_id'   => $superAdminRole->id,
                    'password'  => Hash::make('password123'), // លេខសម្ងាត់សម្រាប់ធ្វើតេស្ត
                    'provider'  => 'local',
                    'is_active' => true,
                ]
            );
        }
    }
}
