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

        return response()->json(['url' => $url]);
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = $this->googleDriver()->stateless()->user();
            
            // ហៅ Function កាត់បន្ថយកូដវែង
            $user = $this->findOrCreateGoogleUser($googleUser);

            $token = $user->createToken('google-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'token'   => $token,
                'user'    => $user,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed',
                'error'   => $e->getMessage(),
            ], 401);
        }
    }

    public function loginWithIdToken(Request $request)
    {
        $request->validate(['id_token' => 'required|string']);

        try {
            $googleUser = $this->googleDriver()->stateless()->userFromToken($request->id_token);

            // ហៅ Function កាត់បន្ថយកូដវែង
            $user = $this->findOrCreateGoogleUser($googleUser);

            $token = $user->createToken('google-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'token'   => $token,
                'user'    => $user,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed',
                'error'   => $e->getMessage(),
            ], 401);
        }
    }

    /**
     * Helper Function សម្រាប់រៀបចំទិន្នន័យ (កុំឱ្យសរសេរកូដ UpdateOrCreate ២ដង)
     */
    private function findOrCreateGoogleUser($googleUser)
    {
        // ត្រូវតែចាប់យក Role Customer ជាមុនសិន ការពារកុំឱ្យលោត Error Foreign Key
        $customerRole = Role::firstOrCreate(
            ['name' => 'Customer'],
            ['description' => 'អតិថិជនទូទៅ']
        );

        return User::updateOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name'        => $googleUser->getName(),
                'provider_id' => $googleUser->getId(),      
                'provider'    => 'google',                  // ✅ បញ្ជាក់ថាគាត់ Login តាម google
                'avatar_url'  => $googleUser->getAvatar(),
                'role_id'     => $customerRole->id,         // ✅ ភ្ជាប់សិទ្ធិជា Customer ស្វ័យប្រវត្តិ
                'password'    => null,                      // ✅ ទុក null បាន ព្រោះយើងបានដាក់ nullable() ក្នុង Migration
                'is_active'   => true,
            ]
        );
    }
}
