<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Logo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LogoController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'name' => ['nullable', 'string', 'max:255'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
        ]);

        $logo = Logo::create([
            'user_id' => $data['user_id'],
            'name' => $data['name'] ?? null,
            'image_path' => $this->saveImage($request),
        ]);

        return response()->json(['message' => 'Logo created successfully.', 'data' => $this->withUrl($logo)], 201);
    }

    public function update(Request $request, Logo $logo)
    {
        $data = $request->validate([
            'user_id' => ['sometimes', 'integer', 'exists:users,id'],
            'name' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
        ]);

        if ($request->has('user_id')) {
            $logo->user_id = $data['user_id'];
        }

        if ($request->has('name')) {
            $logo->name = $data['name'];
        }

        if ($request->hasFile('image')) {
            $this->deleteImage($logo->image_path);
            $logo->image_path = $this->saveImage($request);
        }

        $logo->save();

        return response()->json([
            'message' => 'Logo updated successfully.',
            'data' => $this->withUrl($logo),
        ]);
    }

    public function destroy(Logo $logo)
    {
        $this->deleteImage($logo->image_path);
        $logo->delete();

        return response()->json([
            'message' => 'Logo deleted successfully.',
        ]);
    }

    private function withUrl(Logo $logo): array
    {
        $data = $logo->toArray();
        $data['image_url'] = $logo->image_path ? asset($logo->image_path) : null;
        return $data;
    }

    private function saveImage(Request $request): string
    {
        $directory = public_path('logoes');
        File::ensureDirectoryExists($directory);
        $fileName = Str::uuid()->toString() . '.' . $request->file('image')->getClientOriginalExtension();
        $request->file('image')->move($directory, $fileName);
        return 'logoes/' . $fileName;
    }

    private function deleteImage(?string $imagePath): void
    {
        if (! $imagePath) {
            return;
        }

        $fullPath = public_path($imagePath);
        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }
}
