<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Staff;
use Illuminate\Http\Request;
use App\Http\Requests\Api\Attendance\AttendanceRequest;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // Lấy danh sách chấm công theo ngày
    public function index(Request $request)
    {
        $date = $request->input('date') ? Carbon::parse($request->input('date'))->format('Y-m-d') : Carbon::today()->format('Y-m-d');

        $attendances = Attendance::with(['staff.department'])
            ->where('date', $date)
            ->get();

        // SỬA TẠI ĐÂY: Thống kê phải bao gồm cả Nghỉ phép
        $stats = [
            'total_staff' => Staff::where('status', 'active')->count(),
            'present'     => $attendances->where('status', 'present')->count(),
            'late'        => $attendances->where('status', 'late')->count(),
            'absent'      => $attendances->where('status', 'absent')->count(),
            'leave'       => $attendances->where('status', 'leave')->count(), // Thêm dòng này
            'total_work_hours' => $attendances->sum('work_hours'),
            'total_overtime'   => $attendances->sum('overtime_hours'),
        ];

        return response()->json([
            'success' => true,
            'data' => $attendances,
            'stats' => $stats
        ]);
    }

    // public function store(AttendanceRequest $request)
    // {
    //     $data = $request->validated();
    //     $staffId = $data['staff_id'];
    //     $status = $data['status'];
    //     $workDate = Carbon::parse($data['date']);

    //     // --- NGHIỆP VỤ 1: CHẶN GIỚI HẠN NGHỈ PHÉP (Tối đa 2 ngày/tháng) ---
    //     if ($status === 'leave') {
    //         $leaveCount = Attendance::where('staff_id', $staffId)
    //             ->where('status', 'leave')
    //             ->whereMonth('date', $workDate->month)
    //             ->whereYear('date', $workDate->year)
    //             ->where('date', '!=', $data['date']) // Không đếm chính ngày đang cập nhật
    //             ->count();

    //         if ($leaveCount >= 2) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Nhân viên này đã sử dụng hết 2 ngày nghỉ phép hưởng lương trong tháng ' . $workDate->month
    //             ], 422);
    //         }
    //     }

    //     // --- NGHIỆP VỤ 2: TỰ ĐỘNG TÍNH GIỜ LÀM & TĂNG CA & FIX LỖI ĐI MUỘN ---
    //     // Chỉ tính toán nếu trạng thái không phải là "Vắng mặt" (absent)
    //     if ($status !== 'absent' && isset($data['check_in']) && isset($data['check_out'])) {
    //         $in = Carbon::parse($data['check_in']);
    //         $out = Carbon::parse($data['check_out']);

    //         // Tính tổng số giờ làm (đơn vị: giờ, làm tròn 2 chữ số thập phân)
    //         $totalHours = round($out->diffInMinutes($in) / 60, 2);
    //         $data['work_hours'] = $totalHours;

    //         // Nếu là trạng thái LÀM VIỆC (present/late), tự động kiểm tra đủ 8h hay không
    //         if ($status !== 'leave') {
    //             $data['status'] = ($totalHours >= 8) ? 'present' : 'late';
    //         }

    //         // Tự động tính giờ tăng ca nếu tổng giờ > 8
    //         $data['overtime_hours'] = ($totalHours > 8) ? round($totalHours - 8, 2) : 0;
    //     } else {
    //         // Nếu vắng mặt, mặc định các chỉ số về 0
    //         $data['work_hours'] = 0;
    //         $data['overtime_hours'] = 0;
    //         $data['check_in'] = null;
    //         $data['check_out'] = null;
    //     }

    //     // --- LƯU HOẶC CẬP NHẬT VÀO DATABASE ---
    //     $attendance = Attendance::updateOrCreate(
    //         ['staff_id' => $staffId, 'date' => $data['date']],
    //         $data
    //     );

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Ghi nhận chấm công thành công!',
    //         'data' => $attendance
    //     ]);
    // }

    // public function store(AttendanceRequest $request)
    // {
    //     $data = $request->validated();
    //     $staffId = $data['staff_id'];
    //     $status = $data['status']; // Lấy trạng thái Admin chọn thủ công
    //     $workDate = Carbon::parse($data['date']);

    //     // --- NGHIỆP VỤ 1: CHẶN GIỚI HẠN NGHỈ PHÉP (Giữ nguyên) ---
    //     if ($status === 'leave') {
    //         $leaveCount = Attendance::where('staff_id', $staffId)
    //             ->where('status', 'leave')
    //             ->whereMonth('date', $workDate->month)
    //             ->whereYear('date', $workDate->year)
    //             ->where('date', '!=', $data['date'])
    //             ->count();

    //         if ($leaveCount >= 2) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Nhân viên này đã sử dụng hết 2 ngày nghỉ phép hưởng lương trong tháng ' . $workDate->month
    //             ], 422);
    //         }
    //     }

    //     // --- NGHIỆP VỤ 2: TỰ ĐỘNG TÍNH GIỜ LÀM & TĂNG CA (Cập nhật logic chọn tay) ---
    //     if ($status !== 'absent' && isset($data['check_in']) && isset($data['check_out'])) {
    //         $in = Carbon::parse($data['check_in']);
    //         $out = Carbon::parse($data['check_out']);

    //         // 1. Luôn tính tổng số giờ làm thực tế dựa trên giờ vào/ra
    //         $totalHours = round($out->diffInMinutes($in) / 60, 2);
    //         $data['work_hours'] = $totalHours;

    //         // 2. TỰ ĐỘNG TÍNH TĂNG CA: Nếu làm > 8h thì phần dư là tăng ca
    //         // Phần này lưu vào database để hiển thị rực rỡ ở cột Tăng ca
    //         $data['overtime_hours'] = ($totalHours > 8) ? round($totalHours - 8, 2) : 0;

    //         // ĐÃ LOẠI BỎ: Logic tự động ghi đè trạng thái 'present' hay 'late'. 
    //         // Giờ đây $data['status'] sẽ lấy đúng giá trị Admin đã chọn từ Form.

    //     } else {
    //         // Trường hợp Vắng mặt hoặc dữ liệu giờ trống
    //         $data['work_hours'] = 0;
    //         $data['overtime_hours'] = 0;
    //         $data['check_in'] = null;
    //         $data['check_out'] = null;
    //     }

    //     // --- LƯU HOẶC CẬP NHẬT VÀO DATABASE (Giữ nguyên) ---
    //     $attendance = Attendance::updateOrCreate(
    //         ['staff_id' => $staffId, 'date' => $data['date']],
    //         $data
    //     );

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Ghi nhận chấm công thành công!',
    //         'data' => $attendance
    //     ]);
    // }

    public function store(AttendanceRequest $request)
    {
        $data = $request->validated();
        $status = $data['status'];

        if ($status !== 'absent' && !empty($data['check_in']) && !empty($data['check_out'])) {
            $in = Carbon::parse($data['check_in']);
            $out = Carbon::parse($data['check_out']);

            // FIX: Đảm bảo lấy Giờ Ra trừ Giờ Vào để không bị số âm
            // diffInMinutes trả về giá trị tuyệt đối, nhưng để chắc chắn ta dùng phương thức so sánh chuẩn
            $totalMinutes = $out->diffInMinutes($in, false); // Tham số false để lấy đúng dấu âm/dương nếu cần soi

            // Tuy nhiên, vì diffInMinutes thường trả về số dương, 
            // lỗi của bro có thể do dùng hàm $in->diffInMinutes($out) ở một số phiên bản cũ.
            // Cách an toàn nhất là dùng abs() hoặc đảm bảo thứ tự Ra - Vào:
            $totalHours = round(abs($out->diffInMinutes($in)) / 60, 2);

            $data['work_hours'] = $totalHours;

            // Tính tăng ca: Nếu làm > 8h thì phần dư là tăng ca
            $data['overtime_hours'] = ($totalHours > 8) ? round($totalHours - 8, 2) : 0;
        } else {
            $data['work_hours'] = 0;
            $data['overtime_hours'] = 0;
        }

        $attendance = Attendance::updateOrCreate(
            ['staff_id' => $data['staff_id'], 'date' => $data['date']],
            $data
        );

        return response()->json(['success' => true, 'message' => 'Lưu thành công!', 'data' => $attendance]);
    }

    //======================================
    public function show($id)
    {
        $attendance = Attendance::with('staff')->find($id);
        // Cần load 'staff.department' thay vì chỉ 'staff'
        $attendance = Attendance::with(['staff.department'])->find($id);

        if (!$attendance) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy bản ghi'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $attendance
        ]);
    }

    public function destroy($id)
    {
        $attendance = Attendance::find($id);
        if (!$attendance) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy bản ghi'], 404);
        }

        $attendance->delete();
        return response()->json(['success' => true, 'message' => 'Xóa chấm công thành công!']);
    }
}
