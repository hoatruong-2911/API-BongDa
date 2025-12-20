<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse; // Nên dùng JsonResponse cho API
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Api\Auth\RegisterRequest; // ⬅️ THÊM
use App\Http\Requests\Api\Auth\LoginRequest;    // ⬅️ THÊM


class AuthController extends Controller
{
    /**
     * Xử lý Đăng ký người dùng mới (Sử dụng Form Request).
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        // Validation được xử lý trong RegisterRequest
        $userRole = $request->input('role', 'customer');

        $user = User::create([
            'email' => $request->email,
            'name' => $request->first_name . ' ' . $request->last_name,
            'password' => Hash::make($request->password),
            'role' => $userRole,
        ]);

        $user->profile()->create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'status' => 'active',
        ]);

        $user->load('profile');
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký thành công.',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ]
        ], 201);
    }

    /**
     * Xử lý Đăng nhập (Sử dụng Form Request).
     */
    /**
     * Xử lý Đăng nhập
     */
    public function login(LoginRequest $request): JsonResponse
    {
        // 1. Kiểm tra email/password cơ bản
        if (! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Thông tin đăng nhập không hợp lệ.'],
            ])->status(401);
        }

        // 2. Tìm User Model để lấy dữ liệu (Phải làm bước này trước khi kiểm tra is_active)
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Lỗi hệ thống: Không tìm thấy người dùng.'],
            ])->status(401);
        }

        // 3. KIỂM TRA TRẠNG THÁI KHÓA (Logic mới của bạn)
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ Admin.'
            ], 403);
        }

        // 4. Xử lý Token và Profile
        $user->tokens()->delete(); // Xóa các phiên đăng nhập cũ (Single Device Login)

        // Tạo token mới, gán kèm Role vào Ability của Sanctum
        $token = $user->createToken('auth_token', [$user->role])->plainTextToken;

        $user->load('profile');

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công.',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ]
        ]);
    }

    /**
     * Lấy thông tin người dùng hiện tại (Dùng Request gốc, vì Auth đã xử lý).
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('profile');

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Xử lý Đăng xuất (Dùng Request gốc).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đăng xuất thành công.'
        ]);
    }
}
