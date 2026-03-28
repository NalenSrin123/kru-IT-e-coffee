<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * READ: មើលព័ត៌មានគណនីផ្ទាល់ខ្លួន
     */
    public function show(Request $request)
    {
        // ទាញយកព័ត៌មានអ្នកដែលកំពុង Login ព្រមទាំងភ្ជាប់ឈ្មោះ Role មកជាមួយ
        $user = $request->user()->load('role');

        return $this->successResponse($user, "Get profile successfully");
    }

    /**
     * UPDATE: កែប្រែព័ត៌មានទូទៅ (ឈ្មោះ និង អ៊ីមែល)
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'  => 'required|string|max:255',
            // អនុញ្ញាតឱ្យប្រើ Email ខ្លួនឯងដដែលបាន តែហាមជាន់អ្នកផ្សេង
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
        ]);

        $user->update($request->only(['name', 'email']));

        return $this->successResponse($user->fresh()->load('role'), "Update profile successfully");
    }

    /**
     * UPDATE PASSWORD: ផ្លាស់ប្តូរលេខសម្ងាត់ (ទាមទារលេខសម្ងាត់ចាស់)
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password', // 🌟 Laravel នឹងឆែកលេខសម្ងាត់ចាស់ឱ្យស្វ័យប្រវត្តិ
            'new_password'     => 'required|string|min:6|confirmed', // ត្រូវមាន field new_password_confirmation មកជាមួយ
        ]);

        $request->user()->update([
            'password' => Hash::make($request->new_password)
        ]);

        return $this->successResponse(null, "Update password successfully");
    }

    /**
     * UPLOAD AVATAR: ផ្លាស់ប្តូររូបតំណាងគណនីផ្ទាល់ខ្លួន
     */
    public function uploadAvatar(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // លុបរូបចាស់ចេញពី Server
        if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
            Storage::disk('public')->delete($user->avatar_url);
        }

        // រក្សាទុករូបថ្មី
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar_url' => $path]);

        return $this->successResponse(
            ['avatar_url' => asset('storage/' . $path)],
            "Upload avatar successfully"
        );
    }
}
