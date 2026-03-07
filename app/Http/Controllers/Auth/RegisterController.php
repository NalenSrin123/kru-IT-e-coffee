<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;


class RegisterController extends Controller
{
    public function register(Request $request)
    {
        // ១. Validate input
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        try {
            // ២. 🌟 ស្វែងរក Role 'Customer' ដោយស្វ័យប្រវត្តិ (បើអត់មាន វានឹងបង្កើតឱ្យភ្លាម)
            $customerRole = Role::firstOrCreate(
                ['name' => 'Customer'],
                ['description' => 'អតិថិជនទូទៅដែលចុះឈ្មោះតាម App/Web']
            );

            // ៣. បង្កើត User ដោយយក ID ពី Role ដែលទើបតែរកឃើញខាងលើ
            $user = User::create([
                'role_id'   => $customerRole->id, // ចាប់យក ID ដោយស្វ័យប្រវត្តិ (លែងខ្វល់ថាវាលេខ ២ ឬលេខ ៣ ទៀតហើយ)
                'name'      => $request->name,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
                'provider'  => 'local',
                'is_active' => true,
            ]);

            // ៤. ប្រើ Sanctum ដើម្បី Generate Token ពិតប្រាកដ
            $token = $user->createToken('auth_token')->plainTextToken;

            // ៥. បោះទិន្នន័យជោគជ័យទៅកាន់ Postman ឬ React
            return response()->json([
                'status'       => 'success',
                'message'      => 'បានចុះឈ្មោះគណនីថ្មីដោយជោគជ័យ',
                'user'         => $user,
                'access_token' => $token,
                'token_type'   => 'Bearer',
            ], 201);
        } catch (\Throwable $e) {
            // ៦. ចាប់ Error បង្ហាញក្នុង Postman ដើម្បីងាយស្រួល Debug
            return response()->json([
                'status'    => 'error',
                'message'   => $e->getMessage(),
                'exception' => get_class($e),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ], 500);
        }
    }

}
