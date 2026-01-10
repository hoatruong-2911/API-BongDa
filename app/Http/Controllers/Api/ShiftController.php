<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
// use App\Http\Requests\Shift\ShiftRequest; // Gọi file validate riêng
use App\Http\Api\Requests\Shift\ShiftRequest;


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

    //----------------------------------------------------------
    //----------------------------------------------------------

    public function assignShift(ShiftRequest $request)
    {
        $validated = $request->validated();
        $staffId = $validated['staff_id'];
        $workDate = $validated['work_date'];

        $staff = Staff::find($staffId);
        if (!$staff || $staff->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Nhân viên không thể phân ca'], 422);
        }

        // 1. Luôn xóa sạch ca cũ của ngày này
        ShiftAssignment::where('staff_id', $staffId)
            ->where('work_date', $workDate)
            ->delete();

        // 2. Lấy mảng ID từ request
        $shiftIds = $request->shift_id ? (array)$request->shift_id : [];

        // 3. Nếu mảng rỗng -> Kết thúc (Ngày đó sẽ hiện chữ NGHỈ trên Index)
        if (empty($shiftIds)) {
            return response()->json(['success' => true, 'message' => 'Đã cập nhật trạng thái nghỉ làm cho ngày ' . $workDate]);
        }

        // 4. Nếu có ca -> Tạo mới các bản ghi
        foreach ($shiftIds as $id) {
            ShiftAssignment::create([
                'staff_id'  => $staffId,
                'work_date' => $workDate,
                'shift_id'  => $id,
                'note'      => $request->note ?? null
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Cập nhật lịch thành công!']);
    }
    //----------------------------------------------------------
    //----------------------------------------------------------

    public function index()
    {
        return response()->json(['success' => true, 'data' => Shift::all()]);
    }

    //----------------------------------------------------------
    //----------------------------------------------------------

    // Tạo ca làm mới - Sử dụng ShiftRequest
    public function store(ShiftRequest $request)
    {
        $shift = Shift::create($request->validated());
        return response()->json(['success' => true, 'message' => 'Tạo ca làm việc thành công', 'data' => $shift], 201);
    }

    //----------------------------------------------------------
    //----------------------------------------------------------

    public function show(Shift $shift)
    {
        return response()->json(['success' => true, 'data' => $shift]);
    }

    //----------------------------------------------------------
    //----------------------------------------------------------


    // Cập nhật ca làm - Sử dụng ShiftRequest
    public function update(ShiftRequest $request, Shift $shift)
    {
        $shift->update($request->validated());
        return response()->json(['success' => true, 'message' => 'Cập nhật ca thành công', 'data' => $shift]);
    }

    //----------------------------------------------------------
    //----------------------------------------------------------


    public function destroy(Shift $shift)
    {
        if ($shift->assignments()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Không thể xóa ca đang có nhân viên làm việc'], 400);
        }
        $shift->delete();
        return response()->json(['success' => true, 'message' => 'Xóa ca thành công']);
    }

    //----------------------------------------------------------
    //----------------------------------------------------------


    public function removeAssignment($id)
    {
        $assignment = ShiftAssignment::find($id);
        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy lịch làm việc'], 404);
        }

        $assignment->delete();
        return response()->json(['success' => true, 'message' => 'Đã xóa lịch làm việc']);
    }

    //----------------------------------------------------------
    //----------------------------------------------------------


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

    //----------------------------------------------------------
    //----------------------------------------------------------



    // Hàm lấy dữ liệu để Edit cũng cần trả về mảng ID để Frontend map vào Select
    public function getAssignmentDetail($id)
    {
        // Tìm 1 bản ghi làm gốc để lấy staff_id và work_date
        $base = ShiftAssignment::find($id);
        if (!$base) return response()->json(['success' => false], 404);

        // Lấy tất cả các ca trong cùng ngày đó của nhân viên
        $allShiftsInDay = ShiftAssignment::where('staff_id', $base->staff_id)
            ->where('work_date', $base->work_date)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'staff'    => Staff::find($base->staff_id),
                'work_date' => $base->work_date,
                'note'     => $base->note,
                'shift_ids' => $allShiftsInDay->pluck('shift_id') // Trả về mảng ID [1, 2]
            ]
        ]);
    }


    //----------------------------------------------------------
    //----------------------------------------------------------

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

    //----------------------------------------------------------
    //----------------------------------------------------------


    public function removeStaffWeeklyAssignments(Request $request, $staffId)
    {
        // Lấy ngày bắt đầu và kết thúc tuần từ request để xóa chính xác
        $start = $request->input('start_date');
        $end = $request->input('end_date');

        if (!$start || !$end) {
            return response()->json(['success' => false, 'message' => 'Thiếu thông tin ngày'], 400);
        }

        // Xóa tất cả các ca của nhân viên này trong khoảng thời gian xem
        ShiftAssignment::where('staff_id', $staffId)
            ->whereBetween('work_date', [$start, $end])
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa toàn bộ lịch làm việc trong tuần của nhân sự'
        ]);
    }
}
