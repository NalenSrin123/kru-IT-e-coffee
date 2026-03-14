<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\AdminOtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;


class LoginController extends Controller
{
    public function login(Request $request)
    {
        // ១. Validate Input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // ២. ផ្ទៀងផ្ទាត់ Email & Password
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid Email or Password.'],
            ]);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'This account has been deactivated.'], 403);
        }

        $user->update(['last_login_at' => now()]);

        // ៣. បំបែកលំហូរតាម Role
        if ($user->isAdmin()) {
            // លំហូរ Admin: បង្កើត និងផ្ញើ OTP
            $otpCode = rand(100000, 999999);

            $user->update([
                'otp_code' => Hash::make($otpCode),
                'otp_expires_at' => now()->addMinutes(3),
            ]);

            // ផ្ញើអ៊ីមែលតាមរយៈ Brevo
            Mail::to($user->email)->send(new AdminOtpMail($otpCode));

            return response()->json([
                'status' => 'otp_required',
                'message' => 'Please enter the OTP code that has been sent to your email.',
                'user_id' => $user->id,
            ]);
        } else {
            // លំហូរធម្មតា: អនុញ្ញាតឱ្យចូលដោយមិនបាច់មាន OTP
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'access_token' => $token,
                'user' => $user,
            ]);
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'otp_code' => 'required|digits:6',
        ]);

        $user = User::find($request->user_id);

        // ១. ផ្ទៀងផ្ទាត់កូដ និងម៉ោងផុតកំណត់
        if (! $user->otp_code || ! Hash::check($request->otp_code, $user->otp_code)) {
            return response()->json(['message' => 'Invalid OTP code.'], 401);
        }

        if (now()->greaterThan($user->otp_expires_at)) {
            return response()->json(['message' => 'OTP code has expired.'], 401);
        }

        // ២. សម្អាត OTP ចោលបន្ទាប់ពីប្រើរួច
        $user->update([
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        // ៣. បង្កើត Token ឱ្យ Admin
        $token = $user->createToken('admin_auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'access_token' => $token,
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'You have been logged out.']);
    }

    public function resendOtp(Request $request)
    {
        // ១. Validate ទិន្នន័យ (យើងប្រើ Email ដើម្បីស្វែងរកគណនី)
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        // ២. ឆែកមើលស្ថានភាពគណនី
        if (! $user->is_active) {
            return response()->json(['message' => 'គណនីនេះត្រូវបានផ្អាកការប្រើប្រាស់។'], 403);
        }

        if (! $user->isAdmin()) {
            return response()->json(['message' => 'គណនីនេះមិនទាមទារលេខកូដ OTP ឡើយ។'], 400);
        }

        // ៣. ប្រព័ន្ធការពារ Spam (Cooldown ៦០ វិនាទី)
        // ដោយសារកូដយើងមានសុពលភាព ៣ នាទី (១៨០ វិនាទី)
        // បើពេលវេលាដែលនៅសល់មានច្រើនជាង ១២០ វិនាទី មានន័យថាគាត់ទើបតែស្នើសុំមិនទាន់បាន ១ នាទីផង
        if ($user->otp_expires_at && now()->diffInSeconds($user->otp_expires_at, false) > 120) {
            return response()->json([
                'status' => 'too_many_requests',
                'message' => 'សូមរង់ចាំប្រមាណ ១ នាទីសិន មុននឹងស្នើសុំលេខកូដថ្មីម្តងទៀត។'
            ], 429);
        }

        // ៤. បង្កើតកូដ OTP ថ្មី និង Update ម៉ោងផុតកំណត់ថ្មី
        $otpCode = rand(100000, 999999);

        $user->update([
            'otp_code' => \Illuminate\Support\Facades\Hash::make($otpCode),
            'otp_expires_at' => now()->addMinutes(3),
        ]);

        // ៥. បាញ់អ៊ីមែលថ្មីទៅកាន់គាត់
        \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\AdminOtpMail($otpCode));

        return response()->json([
            'status' => 'success',
            'message' => 'លេខកូដ OTP ថ្មីត្រូវបានផ្ញើទៅកាន់អ៊ីមែលរបស់អ្នកហើយ។',
            'user_id' => $user->id,
        ]);
    }
}
