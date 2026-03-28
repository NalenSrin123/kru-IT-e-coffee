<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // ឆែកមើលថាគាត់បាន Login ហើយឬនៅ? និងតើ Role របស់គាត់ត្រូវនឹងអ្វីដែលយើងទាមទារឬទេ?
        if (!$request->user() || !in_array($request->user()->role->name, $roles)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Access Denied (Forbidden - 403)'
            ], 403);
        }

        return $next($request);
    }
}
