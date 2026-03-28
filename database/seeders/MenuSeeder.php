<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;

class MenuSeeder extends Seeder
{
    public function run()
    
    {
        Menu::create(['name'=>'Home','visible'=>true]);
        Menu::create(['name'=>'Menu','visible'=>true]);
        Menu::create(['name'=>'Services','visible'=>true]);
        Menu::create(['name'=>'About','visible'=>true]);
        Menu::create(['name'=>'Contact','visible'=>true]);
        Menu::create(['name'=>'Cart','visible'=>true]);
        Menu::create(['name'=>'Login','visible'=>true]);
        Menu::create(['name'=>'Sign Up','visible'=>true]);
    }
}