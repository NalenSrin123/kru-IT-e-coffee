<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConnectUs;

class ConnectUsController extends Controller
{
    public function connectUs()
    {
        $data = ConnectUs::latest()->first();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'address' => 'required|string|max:255',
            'phone'   => 'required|string|max:50',
            'email'   => 'required|email|max:255',
        ]);

        // រក្សាទុកតែ ១ record ប៉ុណ្ណោះ។ ទិន្នន័យថ្មីនឹងជំនួសទិន្នន័យចាស់។
        $connectUs = ConnectUs::updateOrCreate(
            ['id' => 1],
            $validated
        );

        return response()->json([
            'success' => true,
            'message' => 'Connect Us data saved successfully',
            'data' => $connectUs
        ], 201);
    }
}   