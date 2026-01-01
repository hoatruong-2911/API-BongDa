<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\Shift\ShiftRequest; // Gọi file validate riêng

class ShiftController extends Controller
{
    // Lấy dữ liệu lịch làm việc theo tuần (Giữ nguyên vì chỉ là GET)
    public function getAssignments(Request $request)
    {
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::now();
        $startOfWeek = $date->copy()->startOfWeek();
        $endOfWeek = $date->copy()->endOfWeek();

        $shifts = Shift::where('is_active', true)->get();

        $staffs = Staff::with(['department', 'assignments' => function ($query) use ($startOfWeek, $endOfWeek) {
            $query->whereBetween('work_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
                ->with('shift');
        }])->get();

        $stats = [
            'total_week' => ShiftAssignment::whereBetween('work_date', [$startOfWeek, $endOfWeek])->count(),
            'counts' => []
        ];

        foreach ($shifts as $s) {
            $stats['counts'][$s->name] = ShiftAssignment::where('shift_id', $s->id)
                ->whereBetween('work_date', [$startOfWeek, $endOfWeek])->count();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'week_range' => [
                    'start' => $startOfWeek->format('d/m/Y'),
                    'end' => $endOfWeek->format('d/m/Y')
                ],
                'shifts' => $shifts,
                'staff_schedules' => $staffs,
                'stats' => $stats
            ]
        ]);
    }

    // Gán ca làm việc mới - Sử dụng ShiftRequest
    public function assignShift(ShiftRequest $request)
    {
        $validated = $request->validated();

        $assignment = ShiftAssignment::updateOrCreate(
            [
                'staff_id' => $validated['staff_id'],
                'work_date' => $validated['work_date']
            ],
            ['shift_id' => $validated['shift_id'], 'note' => $validated['note'] ?? null]
        );

        return response()->json([
            'success' => true,
            'message' => 'Phân ca thành công!',
            'data' => $assignment
        ]);
    }

    public function index()
    {
        return response()->json(['success' => true, 'data' => Shift::all()]);
    }

    // Tạo ca làm mới - Sử dụng ShiftRequest
    public function store(ShiftRequest $request)
    {
        $shift = Shift::create($request->validated());
        return response()->json(['success' => true, 'message' => 'Tạo ca làm việc thành công', 'data' => $shift], 201);
    }

    public function show(Shift $shift)
    {
        return response()->json(['success' => true, 'data' => $shift]);
    }

    // Cập nhật ca làm - Sử dụng ShiftRequest
    public function update(ShiftRequest $request, Shift $shift)
    {
        $shift->update($request->validated());
        return response()->json(['success' => true, 'message' => 'Cập nhật ca thành công', 'data' => $shift]);
    }

    public function destroy(Shift $shift)
    {
        if ($shift->assignments()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Không thể xóa ca đang có nhân viên làm việc'], 400);
        }
        $shift->delete();
        return response()->json(['success' => true, 'message' => 'Xóa ca thành công']);
    }

    public function removeAssignment($id)
    {
        $assignment = ShiftAssignment::find($id);
        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy lịch làm việc'], 404);
        }

        $assignment->delete();
        return response()->json(['success' => true, 'message' => 'Đã xóa lịch làm việc']);
    }

    // Thêm hàm này vào ShiftController.php
    public function getStaffDetail($id)
    {
        // Lấy nhân viên kèm phòng ban và tất cả lịch sử phân ca, kèm chi tiết loại ca đó
        $staff = Staff::with(['department', 'assignments.shift'])
            ->find($id);

        if (!$staff) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy nhân viên'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $staff
        ]);
    }


    // Sửa lại hàm này trong ShiftController.php
    public function getAssignmentDetail($id)
    {
        // LOG để bro kiểm tra trong file laravel.log nếu cần: Log::info("Đang tìm ID: " . $id);

        // BƯỚC 1: Tìm bản ghi PHÂN CA (ShiftAssignment) theo đúng $id truyền vào
        $assignment = ShiftAssignment::with(['staff', 'shift'])->find($id);

        // BƯỚC 2: Kiểm tra nếu không tồn tại
        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy lịch phân ca ID: ' . $id
            ], 404);
        }

        // BƯỚC 3: Trả về dữ liệu bản ghi đó
        return response()->json([
            'success' => true,
            'data' => $assignment
        ]);
    }

    public function updateAssignment(Request $request, $id)
    {
        $assignment = ShiftAssignment::find($id);
        $assignment->update([
            'work_date' => $request->work_date,
            'shift_id' => $request->shift_id,
            'note' => $request->note
        ]);
        return response()->json(['success' => true, 'message' => 'Cập nhật thành công']);
    }
}
