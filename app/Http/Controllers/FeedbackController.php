<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $feedback = Feedback::create($validated);

        return response()->json([
            'message' => 'Feedback sent successfully',
            'data' => $feedback,
        ], 201);
    }

    public function index(Request $request)
    {
        $perPage = max(1, min($request->integer('per_page', 10), 100));
        $feedback = Feedback::latest()->paginate($perPage);

        return response()->json([
            'message' => 'Feedback list retrieved successfully',
            'data' => $feedback,
        ]);
    }
}
