<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Http\Api\Requests\Staff\StaffRequest; // Dùng 1 file duy nhất

use Illuminate\Support\Facades\Storage;

use Illuminate\Http\JsonResponse;

class StaffController extends Controller
{
    public function index(): JsonResponse
    {
        $staffs = Staff::with('department')->orderBy('created_at', 'desc')->get();
        return response()->json(['success' => true, 'data' => $staffs]);
    }

    // public function store(StaffRequest $request): JsonResponse
    // {
    //     $staff = Staff::create($request->validated());
    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Thêm nhân viên thành công!',
    //         'data'    => $staff->load('department')
    //     ], 201);
    // }
    public function store(StaffRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('staff', 'public');
        }

        $staff = Staff::create($data);
        return response()->json([
            'success' => true,
            'message' => 'Thêm nhân viên thành công!',
            'data'    => $staff->load('department')
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $staff = Staff::with('department')->findOrFail($id);
        return response()->json(['success' => true, 'data' => $staff]);
    }

    // public function update(StaffRequest $request, $id): JsonResponse
    // {
    //     $staff = Staff::findOrFail($id);
    //     $staff->update($request->validated());
    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Cập nhật nhân viên thành công!',
    //         'data'    => $staff->load('department')
    //     ]);
    // }
    public function update(StaffRequest $request, $id): JsonResponse
    {
        $staff = Staff::findOrFail($id);
        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            // Xóa ảnh cũ nếu có
            if ($staff->avatar) {
                Storage::disk('public')->delete($staff->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('staff', 'public');
        }

        $staff->update($data);
        return response()->json([
            'success' => true,
            'message' => 'Cập nhật nhân viên thành công!',
            'data'    => $staff->load('department')
        ]);
    }

    
    public function destroy($id): JsonResponse
    {
        Staff::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Đã xóa nhân viên!']);
    }

    /**
     * Đổi trạng thái nhân viên nhanh (Toggle Status)
     */
    public function toggleStatus($id): JsonResponse
    {
        try {
            $staff = Staff::findOrFail($id);

            // Logic: Nếu đang active thì chuyển sang inactive, ngược lại thì active
            $staff->status = ($staff->status === 'active') ? 'inactive' : 'active';
            $staff->save();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái nhân viên thành công!',
                'data' => [
                    'id' => $staff->id,
                    'status' => $staff->status
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }
}
