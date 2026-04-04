<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Social;
use App\Services\Response;

class SocialController extends Controller
{
    // Apply auth middleware
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // GET ALL
    public function index()
    {
        $socials = Social::latest()->get();
        return Response::sendSuccess($socials, "Social links retrieved successfully");
    }

    // CREATE
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'url' => 'required|string',
            'img' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $social = Social::create($data); // created_by is set automatically
         $social->update($data);

        return Response::sendSuccess($social, "Social created successfully", 201);
    }

    // GET ONE
    public function show($id)
    {
        $social = Social::find($id);
        if (!$social) return Response::sendError("Social not found", 404);

        return Response::sendSuccess($social, "Social retrieved successfully");
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $social = Social::find($id);
        if (!$social) return Response::sendError("Social not found", 404);

        $data = $request->validate([
            'name' => 'sometimes|string',
            'url' => 'sometimes|string',
            'img' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $social->update($data); // updated_by is set automatically

        return Response::sendSuccess($social, "Social updated successfully");
    }

    // DELETE
    public function destroy($id)
    {
        $social = Social::find($id);
        if (!$social) return Response::sendError("Social not found", 404);

        $social->delete();

        return Response::sendSuccess(null, "Social deleted successfully");
    }
}