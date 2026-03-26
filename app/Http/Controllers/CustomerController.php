<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    // get user
    public function getAllCustomer(){

        $customers = DB::select("SELECT * FROM users WHERE role_id = ?", [4]);
        $customers = json_decode(json_encode($customers), true);
        return Response::sendSuccess($customers, "Get all customers successfully");
    }

    public function updateCustomer(Request $request, $id){
        // check if customer exists
        $customer = DB::select("SELECT * FROM users WHERE id = ? AND role_id = ?", [$id,4]);

        if(!$customer){
            return Response::sendError("Customer not found",404);
        }

        // hash password
        $password = Hash::make($request->password);

        // update email and password
        DB::update(
            "UPDATE users SET email = ?, password = ? WHERE id = ? AND role_id = ?",
            [$request->email, $password, $id, 4]
        );

        // get updated customer
        $updatedCustomer = DB::select("SELECT * FROM users WHERE id = ?", [$id]);

        return Response::sendSuccess($updatedCustomer, "Customer updated successfully");
    }

    //delete customer
    public function deleteCustomer($id)
    {
        // check if customer exists
        $customer = DB::select("SELECT * FROM users WHERE id = ? AND role_id = ?", [$id,4]);

        if(empty($customer)){
            return Response::sendError("Customer not found",404);
        }
        // delete one customer
        DB::delete("DELETE FROM users WHERE id = ? AND role_id = ?", [$id,4]);

        return Response::sendSuccess(null, "Customer deleted successfully");
    }
}
