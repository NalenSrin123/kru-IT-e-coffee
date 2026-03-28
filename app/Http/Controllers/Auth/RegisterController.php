<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Services\ApiGetAllUser;


class RegisterController extends Controller
{
    public function getAllUsers(ApiGetAllUser $service)
    {
        $users = $service->getUsers();

        if ($users->isEmpty()) {
            // ប្រើប្រាស់ errorResponse ពី Trait
            return $this->errorResponse("No users found", 404);
        }

        // ប្រើប្រាស់ successResponse ពី Trait
        return $this->successResponse($users, "Get All Users Successfully");
    }

    public function register(Request $request)
    {
        // ១. Validate input
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        try {
            // ២. ស្វែងរក Role 'Customer' ដោយស្វ័យប្រវត្តិ
            $customerRole = Role::firstOrCreate(
                ['name' => 'Customer'],
                ['description' => 'Customer role for App/Web']
            );

            // ៣. បង្កើត User ដោយយក ID ពី Role ខាងលើ
            $user = User::create([
                'role_id'   => $customerRole->id,
                'name'      => $request->name,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
                'provider'  => 'local',
                'is_active' => true,
            ]);

            // ៤. ប្រើ Sanctum ដើម្បី Generate Token
            $token = $user->createToken('auth_token')->plainTextToken;

            // ៥. ប្រើប្រាស់ createdResponse ពី Trait (សម្រាប់ Status 201)
            return $this->createdResponse(
                [
                    'access_token' => $token,
                    'token_type'   => 'Bearer',
                    'user'         => $user,
                ],
                'Registration successful.'
            );
        } catch (\Throwable $e) {
            // ៦. ប្រើប្រាស់ errorResponse ព្រមទាំងបោះទិន្នន័យ Debug ចូលទៅក្នុង Parameter ទី៣ ($errors)
            $debugInfo = [
                'exception' => get_class($e),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ];

            return $this->errorResponse($e->getMessage(), 500, $debugInfo);
        }
    }
}