<?php

namespace App\Http\Controllers;

use App\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::select('id', 'name')->get();

        return $this->successResponse($roles, 'Roles retrieved successfully.');
    }
}
