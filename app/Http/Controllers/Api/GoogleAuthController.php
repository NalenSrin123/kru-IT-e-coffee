<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Exception;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    private function googleDriver(): AbstractProvider
    {
        /** @var AbstractProvider $driver */
        $driver = Socialite::driver('google');
        return $driver;
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
            $customerRole = Role::firstOrCreate(
                ['name' => 'Customer'],
                ['description' => 'Default customer role']
            );

            $user = User::updateOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'role_id'           => $customerRole->id,
                    'name'              => $googleUser->getName(),
                    'provider'          => 'google',
                    'provider_id'       => $googleUser->getId(),
                    'google_id'         => $googleUser->getId(),
                    'avatar'            => $googleUser->getAvatar(),
                    'password'          => bcrypt(Str::random(16)),
                    'is_active'         => true,
                ]
            );

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
            $customerRole = Role::firstOrCreate(
                ['name' => 'Customer'],
                ['description' => 'Default customer role']
            );

            $user = User::updateOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'role_id'           => $customerRole->id,
                    'name'              => $googleUser->getName(),
                    'provider'          => 'google',
                    'provider_id'       => $googleUser->getId(),
                    'google_id'         => $googleUser->getId(),
                    'avatar'            => $googleUser->getAvatar(),
                    'password'          => bcrypt(Str::random(16)),
                    'is_active'         => true,
                ]
            );

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
}
