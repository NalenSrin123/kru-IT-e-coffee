<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role): Response
    {
        // ១. បំបែកអក្សរ 'Super Admin|Admin' ទៅជា Array: ['Super Admin', 'Admin']
        $allowedRoles = explode('|', $role);

        // ២. ទាញយកឈ្មោះ Role របស់អ្នកដែលកំពុង Login
        $userRole = $request->user()->role->name ?? '';

        // ៣. ឆែកមើលថាតើ Role របស់គាត់ មាននៅក្នុងបញ្ជីដែលយើងអនុញ្ញាតដែរឬទេ?
        if (!in_array($userRole, $allowedRoles)) {
            // ប្រើ Error Response Format ឱ្យស្របតាមគម្រោង
            return response()->json([
                'success' => false,
                'message' => 'អ្នកមិនមានសិទ្ធិប្រើប្រាស់ទីនេះទេ (Access Denied - 403)'
            ], 403);
        }

        return $next($request);
    }
}
