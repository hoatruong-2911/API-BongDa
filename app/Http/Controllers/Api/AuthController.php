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
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Xử lý Đăng ký người dùng mới (Sử dụng Form Request).
     */
    // public function register(RegisterRequest $request): JsonResponse
    // {
    //     // Validation được xử lý trong RegisterRequest
    //     $userRole = $request->input('role', 'customer');

    //     $user = User::create([
    //         'email' => $request->email,
    //         'name' => $request->first_name . ' ' . $request->last_name,
    //         'password' => Hash::make($request->password),
    //         'role' => $userRole,
    //     ]);

    //     $user->profile()->create([
    //         'first_name' => $request->first_name,
    //         'last_name' => $request->last_name,
    //         'phone' => $request->phone,
    //         'status' => 'active',
    //     ]);

    //     $user->load('profile');
    //     $token = $user->createToken('auth_token')->plainTextToken;

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Đăng ký thành công.',
    //         'data' => [
    //             'user' => $user,
    //             'token' => $token,
    //             'token_type' => 'Bearer',
    //         ]
    //     ], 201);
    // }

    public function register(RegisterRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $userRole = $request->input('role', 'customer');

            // 1. Xử lý Upload ảnh (nếu có)
            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                // Lưu ảnh vào storage/app/public/avatars và lấy đường dẫn
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
            }

            // 2. Tạo User
            $user = User::create([
                'email' => $request->email,
                'name' => $request->first_name . ' ' . $request->last_name,
                'password' => Hash::make($request->password),
                'role' => $userRole,
                'avatar' => $avatarPath, // ✅ Lưu path ảnh vào bảng users
            ]);

            // 3. Tạo Profile
            $user->profile()->create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'avatar' => $avatarPath, // Cập nhật cả bảng profile nếu ní muốn
                'status' => 'active',
            ]);

            $user->load('profile');
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Đăng ký thành công rực rỡ!',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'token_type' => 'Bearer',
                ]
            ], 201);
        });
    }

    /**
     * Xử lý Đăng nhập (Sử dụng Form Request).
     */
    /**
     * Xử lý Đăng nhập
     */
    // public function login(LoginRequest $request): JsonResponse
    // {
    //     // 1. Kiểm tra email/password cơ bản
    //     if (! Auth::attempt($request->only('email', 'password'))) {
    //         throw ValidationException::withMessages([
    //             'email' => ['Thông tin đăng nhập không hợp lệ.'],
    //         ])->status(401);
    //     }

    //     // 2. Tìm User Model để lấy dữ liệu (Phải làm bước này trước khi kiểm tra is_active)
    //     $user = User::where('email', $request->email)->first();

    //     if (!$user) {
    //         throw ValidationException::withMessages([
    //             'email' => ['Lỗi hệ thống: Không tìm thấy người dùng.'],
    //         ])->status(401);
    //     }

    //     // 3. KIỂM TRA TRẠNG THÁI KHÓA (Logic mới của bạn)
    //     if (!$user->is_active) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ Admin.'
    //         ], 403);
    //     }

    //     // 4. Xử lý Token và Profile
    //     $user->tokens()->delete(); // Xóa các phiên đăng nhập cũ (Single Device Login)

    //     // Tạo token mới, gán kèm Role vào Ability của Sanctum
    //     $token = $user->createToken('auth_token', [$user->role])->plainTextToken;

    //     $user->load('profile');

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Đăng nhập thành công.',
    //         'data' => [
    //             'user' => $user,
    //             'token' => $token,
    //             'token_type' => 'Bearer',
    //         ]
    //     ]);
    // }
    public function login(LoginRequest $request): JsonResponse
    {
        // 1. Tìm User dựa trên Email
        $user = User::where('email', $request->email)->first();

        // 2. Nếu không tìm thấy Email (Dù LoginRequest đã check exists nhưng làm vầy cho chắc)
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản không tồn tại!',
                'errors' => [
                    'email' => ['Tài khoản này chưa đăng ký trên hệ thống ní ơi!']
                ]
            ], 422);
        }

        // 3. Kiểm tra Mật khẩu thủ công
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu không chính xác!',
                'errors' => [
                    'password' => ['Mật khẩu sai rồi, kiểm tra lại và nhập lại mật khẩu cho đúng nhé!']
                ]
            ], 422); // Trả về 422 để khớp với logic validate của Ant Design
        }

        // 4. Kiểm tra trạng thái khóa
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản đã bị khóa!',
                'errors' => [
                    'email' => ['Tài khoản của bạn đã bị khóa. Vui lòng liên hệ Admin.']
                ]
            ], 403);
        }

        // 5. Xử lý Token (Single Device Login)
        $user->tokens()->delete();
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
