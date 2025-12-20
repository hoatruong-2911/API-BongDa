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
     * @param  string  ...$roles  // Danh sách các roles được phép (ví dụ: 'admin', 'staff')
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Kiểm tra xác thực (Sanctum)
        if (! $request->user()) {
            return response()->json([
                'message' => 'Unauthenticated. Vui lòng đăng nhập.'
            ], 401);
        }

        // 2. Lấy role của người dùng hiện tại
        $userRole = $request->user()->role;
        
        // 3. Kiểm tra quyền
        if (! in_array($userRole, $roles)) {
            // Trả về lỗi 403 Forbidden (Không có quyền)
            return response()->json([
                'message' => 'Forbidden. Bạn không có quyền truy cập chức năng này.'
            ], 403);
        }

        return $next($request);
    }
}