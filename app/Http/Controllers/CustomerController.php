<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * READ ALL: ទាញយកបញ្ជីអតិថិជនទាំងអស់ (សម្រាប់ Admin Dashboard)
     */
    public function index(Request $request)
    {
        $query = User::with('role')->whereHas('role', function ($q) {
            $q->where('name', 'Customer');
        });

        // អាចថែមមុខងារ Search បើចង់បាន
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        $customers = $query->orderBy('id', 'desc')->paginate(10); // ប្រើ Paginate សម្រាប់ទិន្នន័យច្រើន

        return $this->successResponse($customers, "Get all customers successfully");
    }

    /**
     * READ SINGLE: មើលព័ត៌មានលម្អិតរបស់អតិថិជនម្នាក់
     */
    public function show($id)
    {
        $customer = User::with('role')->whereHas('role', function ($q) {
            $q->where('name', 'Customer');
        })->find($id);

        if (!$customer) {
            return $this->errorResponse("No customer found", 404);
        }

        return $this->successResponse($customer, "Get customer successfully");
    }

    /**
     * UPDATE STATUS: អនុញ្ញាតឱ្យ Admin ត្រឹមតែ Block ឬ Unblock អតិថិជនប៉ុណ្ណោះ
     */
    public function update(Request $request, $id)
    {
        $customer = User::whereHas('role', function ($q) {
            $q->where('name', 'Customer');
        })->find($id);

        if (!$customer) {
            return $this->errorResponse("No customer found", 404);
        }

        // Validate ទទួលយកតែ Field 'is_active' ប៉ុណ្ណោះ
        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        // Update តែ Status មួយគត់
        $customer->update([
            'is_active' => $request->is_active
        ]);

        $statusName = $request->is_active ? 'Unblocked' : 'Blocked';

        return $this->successResponse($customer, "The customer's status has been updated to $statusName successfully");
    }

    /**
     * DELETE: លុបអតិថិជន (Soft Delete)
     */
    public function destroy($id)
    {
        $customer = User::whereHas('role', function ($q) {
            $q->where('name', 'Customer');
        })->find($id);

        if (!$customer) {
            return $this->errorResponse("No customer found", 404);
        }

        $customer->delete(); // Soft Delete

        return $this->successResponse(null, "The customer has been deleted successfully");
    }
}
