<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

            $user = User::updateOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name'              => $googleUser->getName(),
                    'google_id'         => $googleUser->getId(),
                    'avatar'            => $googleUser->getAvatar(),
                    'email_verified_at' => now(),
                    'password'          => bcrypt(Str::random(16)),
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

            $user = User::updateOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name'              => $googleUser->getName(),
                    'google_id'         => $googleUser->getId(),
                    'avatar'            => $googleUser->getAvatar(),
                    'email_verified_at' => now(),
                    'password'          => bcrypt(Str::random(16)),
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
