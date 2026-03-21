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
            return $this->errorResponse('This account has been deactivated.', 403);
        }

        $user->update(['last_login_at' => now()]);

        // ៣. បំបែកលំហូរតាម Role
        // 🌟 កែប្រែនៅទីនេះ៖ ប្រើ requiresOtp() ជំនួសឱ្យ isAdmin()
        if ($user->requiresOtp()) {
            // លំហូរ Admin & Super Admin: បង្កើត និងផ្ញើ OTP
            $otpCode = rand(100000, 999999);

            $user->update([
                'otp_code' => Hash::make($otpCode),
                'otp_expires_at' => now()->addMinutes(3),
            ]);

            // ផ្ញើអ៊ីមែលតាមរយៈ Brevo
            Mail::to($user->email)->send(new AdminOtpMail($otpCode));

            return $this->successResponse(
                ['user_id' => $user->id, 'status' => 'otp_required'],
                'Please enter the OTP code that has been sent to your email.'
            );
        } else {
            // លំហូរធម្មតា (Customer): អនុញ្ញាតឱ្យចូលដោយមិនបាច់មាន OTP
            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->successResponse(
                ['access_token' => $token, 'user' => $user],
                'Login successful.'
            );
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
            return $this->errorResponse('Invalid OTP code.', 401);
        }

        if (now()->greaterThan($user->otp_expires_at)) {
            return $this->errorResponse('OTP code has expired.', 401);
        }

        // ២. សម្អាត OTP ចោលបន្ទាប់ពីប្រើរួច
        $user->update([
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        // ៣. បង្កើត Token ឱ្យ Admin/Super Admin
        $token = $user->createToken('admin_auth_token')->plainTextToken;

        return $this->successResponse(
            ['access_token' => $token, 'user' => $user],
            'OTP verified successfully.'
        );
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'You have been logged out.');
    }

    public function resendOtp(Request $request)
    {
        // ១. Validate ទិន្នន័យ
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        // ២. ឆែកមើលស្ថានភាពគណនី
        if (! $user->is_active) {
            return $this->errorResponse('គណនីនេះត្រូវបានផ្អាកការប្រើប្រាស់។', 403);
        }

        // 🌟 កែប្រែនៅទីនេះ៖ ប្រើ requiresOtp() ជំនួសឱ្យ isAdmin()
        if (! $user->requiresOtp()) {
            return $this->errorResponse('គណនីនេះមិនទាមទារលេខកូដ OTP ឡើយ។', 400);
        }

        // ៣. ប្រព័ន្ធការពារ Spam (Cooldown ៦០ វិនាទី)
        if ($user->otp_expires_at && now()->diffInSeconds($user->otp_expires_at, false) > 120) {
            return $this->errorResponse('សូមរង់ចាំប្រមាណ ១ នាទីសិន មុននឹងស្នើសុំលេខកូដថ្មីម្តងទៀត។', 429);
        }

        // ៤. បង្កើតកូដ OTP ថ្មី និង Update ម៉ោងផុតកំណត់ថ្មី
        $otpCode = rand(100000, 999999);

        $user->update([
            'otp_code' => Hash::make($otpCode),
            'otp_expires_at' => now()->addMinutes(3),
        ]);

        // ៥. បាញ់អ៊ីមែលថ្មីទៅកាន់គាត់
        Mail::to($user->email)->send(new AdminOtpMail($otpCode));

        return $this->successResponse(
            ['user_id' => $user->id],
            'លេខកូដ OTP ថ្មីត្រូវបានផ្ញើទៅកាន់អ៊ីមែលរបស់អ្នកហើយ។'
        );
    }
}
