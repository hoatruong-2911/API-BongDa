<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Api\User\UpdateUserRequest; // Tạo ở bước tiếp theo
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Cập nhật thông tin User và Profile của người dùng hiện tại.
     */
    public function update(UpdateUserRequest $request): JsonResponse // ⬅️ Dùng Form Request
    {
        $user = Auth::user();
        $profile = $user->profile; // Đảm bảo mối quan hệ profile đã được load

        $data = $request->validated();

        // 1. Cập nhật User (Name)
        if (isset($data['first_name']) || isset($data['last_name'])) {
            // Cập nhật tên đầy đủ trong bảng users
            $user->name = ($data['first_name'] ?? $profile->first_name) . ' ' . ($data['last_name'] ?? $profile->last_name);
            $user->save();
        }

        // 2. Cập nhật Profile (first_name, last_name, phone, address...)
        $profileData = $request->only(['first_name', 'last_name', 'phone']);
        $profile->update($profileData);

        // Load lại Profile để đảm bảo dữ liệu mới nhất được trả về
        $user->load('profile');

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật hồ sơ thành công.',
            'data' => $user // Trả về user object đã được cập nhật
        ]);
    }

    /**
     * Cập nhật mật khẩu.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Xóa tất cả các token hiện có để buộc đăng nhập lại (tùy chọn)
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mật khẩu đã được thay đổi thành công. Vui lòng đăng nhập lại.'
        ]);
    }
}
