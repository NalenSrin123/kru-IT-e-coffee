<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;

class GoogleAuthController extends Controller
{
    private function googleDriver(): AbstractProvider
    {
        return Socialite::driver('google');
    }

    public function redirectToGoogle()
    {
        $url = $this->googleDriver()
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        // ប្រើប្រាស់ successResponse សម្រាប់រុញ URL ទៅកាន់ Frontend
        return $this->successResponse(
            ['url' => $url],
            'Redirect URL generated successfully.'
        );
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = $this->googleDriver()->stateless()->user();

            $user = $this->findOrCreateGoogleUser($googleUser);

            $token = $user->createToken('google-token')->plainTextToken;

            // ប្រើប្រាស់ successResponse ជាមួយទម្រង់ដូចគ្នានឹង LoginController
            return $this->successResponse(
                ['access_token' => $token, 'user' => $user],
                'Google login successful.'
            );
        } catch (Exception $e) {
            // ប្រើប្រាស់ errorResponse ពេលមានបញ្ហា
            return $this->errorResponse('Authentication failed: ' . $e->getMessage(), 401);
        }
    }

    public function loginWithIdToken(Request $request)
    {
        $request->validate(['id_token' => 'required|string']);

        try {
            $googleUser = $this->googleDriver()->stateless()->userFromToken($request->id_token);

            // 💡 ចំណុចដែលបានលុប៖ ខ្ញុំបានលុបកូដ Role::firstOrCreate ពីទីនេះ 
            // ព្រោះយើងមានវានៅក្នុង Helper function ខាងក្រោមរួចទៅហើយ។

            $user = $this->findOrCreateGoogleUser($googleUser);

            $token = $user->createToken('google-token')->plainTextToken;

            return $this->successResponse(
                ['access_token' => $token, 'user' => $user],
                'Google login successful.'
            );
        } catch (Exception $e) {
            return $this->errorResponse('Authentication failed: ' . $e->getMessage(), 401);
        }
    }

    /**
     * Helper Function សម្រាប់រៀបចំទិន្នន័យ
     */
    private function findOrCreateGoogleUser($googleUser)
    {
        $customerRole = Role::firstOrCreate(
            ['name' => 'Customer'],
            ['description' => 'Customer role for general users']
        );

        return User::updateOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name'        => $googleUser->getName(),
                'provider_id' => $googleUser->getId(),
                'provider'    => 'google',
                'avatar_url'  => $googleUser->getAvatar(),
                'role_id'     => $customerRole->id,
                'password'    => null,
                'is_active'   => true,
            ]
        );
    }
}