<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * READ: ទាញយកបញ្ជីបុគ្គលិកទាំងអស់ (លាក់ Customer ចេញ)
     */
    public function index()
    {
        // ទាញយក User ទាំងអស់ ដោយភ្ជាប់មកជាមួយឈ្មោះ Role
        // តែត្រងយកតែអ្នកណាដែលមិនមែនជា 'Customer'
        $staff = User::with('role')
            ->whereHas('role', function ($query) {
                $query->where('name', '!=', 'Customer');
            })
            ->orderBy('id', 'desc')
            ->get();

        return $this->successResponse($staff, 'Get all staff successfully'); // ត្រឡប់ទៅ JSON ជាមួយសារ
    }

    /**
     * CREATE: បង្កើតគណនីបុគ្គលិកថ្មី
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role_id'  => 'required|exists:roles,id',
        ]);

        // ការពារកុំឱ្យគេលួចបញ្ជូន role_id របស់ Customer (ឧបមាថា Customer មាន ID = 4)
        $role = Role::find($request->role_id);
        if ($role->name === 'Customer') {
            return $this->errorResponse('You are not allowed to create a customer account here.', 400);
        }

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role_id'   => $request->role_id,
            'provider'  => 'local',
            'is_active' => true,
        ]);

        // ទាញយកព័ត៌មាន Role មកបង្ហាញភ្លាមៗ ដើម្បីឱ្យ Frontend ស្រួលប្រើ
        $user->load('role');

        return $this->createdResponse($user, 'New staff account created successfully.');
    }

    /**
     * READ (Single): មើលព័ត៌មានបុគ្គលិកណាម្នាក់លម្អិត
     */
    public function show($id)
    {
        $user = User::with('role')->find($id);

        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        return $this->successResponse($user, 'User data retrieved successfully.');
    }

    /**
     * UPDATE: កែប្រែព័ត៌មានបុគ្គលិក (អាចដំឡើងសិទ្ធិ ឬ Block គណនី)
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        $request->validate([
            'name'      => 'sometimes|required|string|max:255',
            // អនុញ្ញាតឱ្យប្រើ Email ដដែលបាន ប៉ុន្តែហាមជាន់អ្នកផ្សេង
            'email'     => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($user->id)],
            'password'  => 'nullable|string|min:6', // Password អាចមិនបញ្ចូលក៏បាន (nullable)
            'role_id'   => 'sometimes|required|exists:roles,id',
            'is_active' => 'sometimes|boolean',
        ]);

        // រៀបចំទិន្នន័យសម្រាប់ Update
        $updateData = $request->only(['name', 'email', 'role_id', 'is_active']);

        // ប្រសិនបើ Super Admin ចង់វាយបញ្ចូល Password ថ្មីឱ្យគាត់
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);
        $user->load('role');

        return $this->successResponse($user, 'User data updated successfully.');
    }

    /**
     * DELETE: បញ្ឈប់ការងារបុគ្គលិក (Soft Delete)
     */
    public function destroy(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        // 🌟 សុវត្ថិភាពខ្ពស់៖ ការពារកុំឱ្យ Super Admin លុបគណនីខ្លួនឯងកំពុង Login
        if ($request->user()->id === $user->id) {
            return $this->errorResponse('You are not allowed to delete your own account.', 403);
        }

        // ដោយសារ Model យើងមាន use SoftDeletes វានឹងកត់ត្រាម៉ោងលុប តែមិនលុបទិន្នន័យមែនទែនទេ
        $user->delete();

        return $this->successResponse(null, 'User account deleted successfully.');
    }

    public function uploadAvatar(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        // ១. ត្រួតពិនិត្យថាពិតជា File រូបភាពមែន និងទំហំមិនលើស 2MB
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // ២. លុបរូបចាស់ចេញពី Storage សិន (បើគាត់ធ្លាប់មានរូបពីមុន) ដើម្បីកុំឱ្យពេញ Server
        if ($user->avatar_url && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar_url)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar_url);
        }

        // ៣. រក្សាទុករូបថ្មីចូលទៅក្នុង Folder 'avatars' នៃ Public Storage
        $path = $request->file('avatar')->store('avatars', 'public');

        // ៤. Update ទីតាំងរូបភាពចូលទៅក្នុង Database
        $user->update(['avatar_url' => $path]);

        // ៥. បោះ Link រូបភាពពេញលេញទៅឱ្យ Frontend វិញ ដើម្បីងាយស្រួលបង្ហាញ
        return $this->successResponse(
            ['avatar_url' => asset('storage/' . $path)],
            'Profile picture updated successfully.'
        );
    }
}
