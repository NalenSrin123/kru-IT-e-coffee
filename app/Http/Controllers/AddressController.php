<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Response;
use App\Models\Address;

class AddressController extends Controller
{
    // GET /addresses
    public function index()
    {
        $addresses = Address::with('user')->get();
        return Response::sendSuccess($addresses, "Get all addresses successfully");
    }

    // POST /addresses
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'street'  => 'required|string',
            'city'    => 'required|string',
            'country' => 'required|string',
        ]);

        $address = Address::create($request->only('user_id', 'street', 'city', 'country'));

        return Response::sendSuccess($address, "Address created successfully");
    }

    // PUT /addresses/{id}
    public function update(Request $request, $id)
    {
        $address = Address::find($id);

        if (!$address) {
            return Response::sendError("Address not found", 404);
        }

        $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'street'  => 'sometimes|string',
            'city'    => 'sometimes|string',
            'country' => 'sometimes|string',
        ]);

        $address->update($request->only('user_id', 'street', 'city', 'country'));

        return Response::sendSuccess($address, "Address updated successfully");
    }

    // DELETE /addresses/{id}
    public function destroy($id)
    {
        $address = Address::find($id);

        if (!$address) {
            return Response::sendError("Address not found", 404);
        }

        $address->delete();

        return Response::sendSuccess(null, "Address deleted successfully");
    }
}
