<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Super Admin',
                'description' => 'អ្នកគ្រប់គ្រងប្រព័ន្ធជាន់ខ្ពស់បំផុត (គ្រប់គ្រងបុគ្គលិក)'
            ],
            [
                'name' => 'Admin',
                'description' => 'អ្នកគ្រប់គ្រងទិន្នន័យទូទៅ (ទំនិញ, ប្រភេទ)'
            ],
            [
                'name' => 'Cashier',
                'description' => 'អ្នកគិតលុយ និងគ្រប់គ្រងការបញ្ជាទិញ'
            ],
            [
                'name' => 'Customer',
                'description' => 'អតិថិជនទូទៅដែលប្រើប្រាស់ App/Web'
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']], // ឆែករកមើលឈ្មោះនេះសិន
                $role // បើអត់មាន បង្កើតថ្មី, បើមានស្រាប់ Update វា
            );
        }
    }
}
