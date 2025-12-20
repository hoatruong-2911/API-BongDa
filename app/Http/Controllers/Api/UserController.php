<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User; // ⬅️ Quan trọng: Phải thêm dòng này
use Illuminate\Http\Request; // ⬅️ Thêm dòng này
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Api\User\UpdateUserRequest;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * [ADMIN] Danh sách tất cả tài khoản
     */
    public function index(Request $request): JsonResponse
    {
        $users = User::with('profile')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * [ADMIN] Tạo tài khoản mới
     */
    /**
     * [ADMIN] Tạo tài khoản mới (Đã cập nhật lưu Ảnh đại diện)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,staff,customer',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Validate thêm ảnh
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // 1. Tạo User
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role' => $request->role,
                ]);

                // 2. Xử lý Ảnh đại diện (nếu có)
                $avatarPath = null;
                if ($request->hasFile('avatar')) {
                    $file = $request->file('avatar');
                    // Đặt tên file duy nhất
                    $fileName = 'avatar_user_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                    // Di chuyển vào thư mục public/uploads/avatars
                    $file->move(public_path('uploads/avatars'), $fileName);
                    $avatarPath = 'uploads/avatars/' . $fileName;
                }

                // 3. Tạo Profile đi kèm (Lưu SĐT và Avatar)
                $user->profile()->create([
                    'phone' => $request->phone,
                    'avatar' => $avatarPath, // Lưu đường dẫn ảnh vào đây
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Tạo tài khoản thành công!',
                    'data' => $user->load('profile')
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    /**
     * [ADMIN] Cập nhật vai trò (Staff/Admin/Customer)
     */
    public function updateRole(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'role' => 'required|in:admin,staff,customer'
        ]);

        $user->update(['role' => $request->role]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật vai trò thành công',
            'data' => $user
        ]);
    }

    /**
     * [ADMIN/USER] Cập nhật thông tin chi tiết
     * Nếu truyền ID -> Admin sửa cho User. Nếu không -> User tự sửa chính mình.
     */


    /**
     * [ADMIN] Cập nhật tài khoản người dùng
     */
    /**
     * [ADMIN] Cập nhật tài khoản người dùng khác
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role'  => 'required|in:admin,staff,customer',
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            return DB::transaction(function () use ($request, $user) {
                // 1. Cập nhật User
                $userData = [
                    'name'  => $request->name,
                    'email' => $request->email,
                    'role'  => $request->role,
                ];

                if ($request->filled('password')) {
                    $userData['password'] = Hash::make($request->password);
                }

                $user->update($userData);

                // 2. Lấy hoặc tạo Profile
                $profile = $user->profile ?: $user->profile()->create(['user_id' => $user->id]);

                // 3. Xử lý Ảnh đại diện mới
                if ($request->hasFile('avatar')) {
                    $file = $request->file('avatar');
                    $fileName = 'avatar_user_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/avatars'), $fileName);

                    // Xóa ảnh cũ
                    if ($profile->avatar && file_exists(public_path($profile->avatar))) {
                        @unlink(public_path($profile->avatar));
                    }
                    $profile->avatar = 'uploads/avatars/' . $fileName;
                }

                // 4. Cập nhật SĐT
                $profile->phone = $request->phone;
                $profile->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật tài khoản thành công!',
                    'data' => $user->load('profile')
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    // app/Http/Controllers/Api/UserController.php


    /**
     * [ADMIN] Lấy chi tiết một tài khoản để sửa
     */
    public function show($id): JsonResponse
    {
        try {
            $user = User::with('profile')->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy người dùng.'
            ], 404);
        }
    }

    /**
     * [USER] Đổi mật khẩu cá nhân
     */
    /**
     * [USER] Đổi mật khẩu cá nhân
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'], // 'confirmed' yêu cầu field 'new_password_confirmation'
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'new_password.confirmed' => 'Mật khẩu xác nhận không trùng khớp.',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.'
        ]);

        $user = Auth::user();

        // 1. Kiểm tra mật khẩu cũ có khớp với DB không
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu hiện tại không chính xác.'
            ], 422);
        }

        // 2. Cập nhật mật khẩu mới
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        // 3. (Tùy chọn) Đăng xuất khỏi các thiết bị khác bằng cách xóa Token
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mật khẩu đã được thay đổi thành công.'
        ]);
    }

    /**
     * [ADMIN] Xóa tài khoản
     */
    /**
     * [ADMIN] Xóa tài khoản người dùng
     */
    /**
     * [ADMIN] Xóa tài khoản người dùng và ảnh đại diện vật lý
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = User::with('profile')->findOrFail($id);

            // 1. Ngăn chặn tự xóa bản thân
            if ($user->id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không thể tự xóa tài khoản của chính mình!'
                ], 400);
            }

            // 2. Ngăn chặn xóa Quản trị viên hệ thống gốc
            if ($user->id === 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa tài khoản Quản trị viên hệ thống!'
                ], 403);
            }

            // 3. XỬ LÝ XÓA ẢNH VẬT LÝ
            if ($user->profile && $user->profile->avatar) {
                $imagePath = public_path($user->profile->avatar);
                if (file_exists($imagePath)) {
                    @unlink($imagePath); // Xóa file ảnh khỏi thư mục public/uploads/avatars
                }
            }

            // 4. Thực hiện xóa tài khoản
            // (Lưu ý: Nếu dùng On Delete Cascade trong Database, Profile sẽ tự mất. 
            // Nếu không, bạn nên xóa profile trước)
            $user->tokens()->delete();
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa tài khoản và dữ liệu liên quan thành công.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }


    // app/Http/Controllers/Api/UserController.php

    public function toggleStatus($id): \Illuminate\Http\JsonResponse
    {
        // 1. Tìm đúng User đang tồn tại dựa trên ID
        $user = \App\Models\User::find($id);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy người dùng'], 404);
        }

        if ($user->role === 'admin') {
            return response()->json(['success' => false, 'message' => 'Không thể khóa tài khoản Admin'], 403);
        }

        // 2. Đảo ngược giá trị is_active (Update chứ không phải Insert)
        $user->is_active = !$user->is_active;
        $user->save();

        // 3. Nếu bị khóa, xóa token để logout
        if (!$user->is_active) {
            $user->tokens()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => $user->is_active ? 'Đã mở khóa tài khoản' : 'Đã khóa tài khoản',
            'data' => [
                'id' => $user->id,
                'is_active' => $user->is_active
            ]
        ]);
    }

    // public function updateProfile(Request $request)
    // {
    //     $user = auth()->user();
    //     // Lấy profile hoặc tạo mới nếu chưa có
    //     $profile = $user->profile ?: $user->profile()->create(['user_id' => $user->id]);

    //     // 1. Cập nhật thông tin User
    //     if ($request->filled('name')) {
    //         $user->name = $request->name;
    //     }

    //     // 2. Cập nhật thông tin Profile
    //     if ($request->has('phone')) {
    //         $profile->phone = $request->phone;
    //     }

    //     // 3. Xử lý file ảnh (Avatar)
    //     if ($request->hasFile('avatar')) {
    //         $file = $request->file('avatar');
    //         $fileName = time() . '_' . $file->getClientOriginalName();
    //         $file->move(public_path('uploads/avatars'), $fileName);

    //         // Xóa ảnh cũ
    //         if ($profile->avatar && file_exists(public_path($profile->avatar))) {
    //             @unlink(public_path($profile->avatar));
    //         }
    //         $profile->avatar = 'uploads/avatars/' . $fileName;
    //     }

    //     // LƯU VÀO DATABASE
    //     $user->save();
    //     $profile->save();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Cập nhật thành công!',
    //         'data' => $user->load('profile')
    //     ]);
    // }

    public function updateProfile(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        // Đảm bảo luôn có Profile, nếu chưa có thì tạo mới
        $profile = $user->profile ?: $user->profile()->create(['user_id' => $user->id]);

        // 1. Cập nhật thông tin cơ bản
        if ($request->filled('name')) {
            $user->update(['name' => $request->name]);
        }

        if ($request->has('phone')) {
            $profile->phone = $request->phone;
        }

        // 2. Xử lý lưu ảnh vào thư mục (Tương tự Sân bóng)
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');

            // Tạo tên file duy nhất để tránh bị ghi đè (Dùng ID user và timestamp)
            $fileName = 'avatar_user_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();

            // Di chuyển file vào thư mục: public/uploads/avatars
            // Laravel sẽ tự động tạo thư mục nếu chưa tồn tại
            $file->move(public_path('uploads/avatars'), $fileName);

            // Xóa ảnh cũ trong thư mục để tiết kiệm bộ nhớ (Nếu không phải ảnh mặc định)
            if ($profile->avatar && file_exists(public_path($profile->avatar))) {
                @unlink(public_path($profile->avatar));
            }

            // Lưu đường dẫn tương đối vào Database
            $profile->avatar = 'uploads/avatars/' . $fileName;
        }

        $profile->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật ảnh đại diện thành công!',
            'data' => $user->load('profile')
        ]);
    }
}
