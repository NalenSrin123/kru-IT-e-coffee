<?php

namespace App\Services;

use App\Models\User;

class ApiGetAllUser
{
    public function getUsers()
    {
        return User::all();
    }
}