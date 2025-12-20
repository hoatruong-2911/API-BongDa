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
    public function login(LoginRequest $request): JsonResponse
    {
        // ... (logic Auth::attempt)
        if (! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Thông tin đăng nhập không hợp lệ.'],
            ])->status(401);
        }

        // ⬅️ THAY THẾ TOÀN BỘ LOGIC DƯỚI ĐÂY

        // 1. Tìm User Model MỚI từ DB (đảm bảo nó là Model có đủ Trait)
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Lỗi hệ thống: Không tìm thấy người dùng.'],
            ])->status(401);
        }

        // 2. Chạy các phương thức Sanctum và Eloquent trên đối tượng $user mới
        $user->tokens()->delete(); // ⬅️ Lỗi này sẽ được khắc phục

        $token = $user->createToken('auth_token', [$user->role])->plainTextToken; // ⬅️ Lỗi này sẽ được khắc phục

        $user->load('profile'); // ⬅️ Lỗi này sẽ được khắc phục

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
