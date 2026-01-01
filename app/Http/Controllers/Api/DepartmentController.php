<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Http\Requests\Api\Department\StoreDepartmentRequest; // Import file validate
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class DepartmentController extends Controller
{
    public function index(): JsonResponse
    {
        $departments = Department::withCount('staff')->get();
        return response()->json(['success' => true, 'data' => $departments]);
    }

    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $department = Department::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tạo phòng ban thành công!',
            'data' => $department
        ], 201);
    }

    public function toggleStatus($id): JsonResponse
    {
        $department = Department::findOrFail($id);
        $department->is_active = !$department->is_active;
        $department->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công!', // Thông báo này sẽ gửi về Frontend
            'data' => $department
        ]);
    }

    public function show(Department $department): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $department]);
    }

    public function update(StoreDepartmentRequest $request, Department $department): JsonResponse
    {
        $validated = $request->validated();

        $department->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? $department->description,
            'is_active' => $request->boolean('is_active', $department->is_active)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật phòng ban thành công!',
            'data' => $department
        ]);
    }

    public function destroy(Department $department): JsonResponse
    {
        if ($department->staff()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa phòng ban đang có nhân viên!'
            ], 400);
        }

        $department->delete();
        return response()->json(['success' => true, 'message' => 'Đã xóa phòng ban thành công!']);
    }
}
