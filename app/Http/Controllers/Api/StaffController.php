<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Staff\StaffRequest;
// use App\Http\Api\Requests\Staff\StaffRequest;
use App\Http\Controllers\Controller;
use App\Models\Staff;
// use App\Http\Api\Requests\Staff\StaffRequest; // Dùng 1 file duy nhất
// Thông thường Laravel mặc định sẽ là:
use Illuminate\Support\Facades\Storage;

use Illuminate\Http\JsonResponse;


use Illuminate\Support\Facades\Auth;
use App\Models\Booking; // Giả định bro có model này
use App\Models\Field;
use App\Models\Order;

class StaffController extends Controller
{
    public function index(): JsonResponse
    {
        $staffs = Staff::with('department')->orderBy('created_at', 'desc')->get();
        return response()->json(['success' => true, 'data' => $staffs]);
    }



    public function store(StaffRequest $request): JsonResponse
    {
        $data = $request->validated();

        // 1. Tạo tài khoản User trước cho nhân viên này (nếu chưa có)
        // Hoặc nếu bro đã chọn user_id từ một danh sách có sẵn ở Frontend:
        // $data['user_id'] = $request->user_id;

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('staff', 'public');
        }

        $staff = Staff::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Thêm nhân viên và liên kết tài khoản thành công!',
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


    /**
     * Lấy dữ liệu tổng quan cho Dashboard của nhân viên đang đăng nhập
     */





    // public function getOverview(): JsonResponse
    // {
    //     try {
    //         /** @var \App\Models\User $user */
    //         $user = Auth::user();

    //         // ✅ ÉP MÚI GIỜ VIỆT NAM: Khắc phục lỗi 0đ do lệch múi giờ (Timezone)
    //         $vnTime = now('Asia/Ho_Chi_Minh');
    //         $startOfToday = $vnTime->copy()->startOfDay(); // 00:00:00
    //         $endOfToday = $vnTime->copy()->endOfDay();     // 23:59:59

    //         $staff = Staff::with('department')->where('user_id', $user->id)->first();
    //         $isStaff = (bool)$staff;
    //         $targetStaffId = $user->id;

    //         // --- TÍNH TOÁN DỮ LIỆU THẬT ---

    //         // A. Doanh thu hôm nay (Orders + Bookings)
    //         $todayOrderRev = (int) Order::whereBetween('created_at', [$startOfToday, $endOfToday])
    //             ->where('status', 'completed')
    //             ->when($isStaff, fn($q) => $q->where('staff_id', $targetStaffId))
    //             ->sum('total_amount');

    //         $todayBookingRev = (int) Booking::whereBetween('booking_date', [$vnTime->format('Y-m-d'), $vnTime->format('Y-m-d')])
    //             ->where('status', 'completed')
    //             ->when($isStaff, fn($q) => $q->where('staff_id', $targetStaffId))
    //             ->sum('total_amount');

    //         // B. Đơn hàng hôm nay
    //         $ordersTodayCount = Order::whereBetween('created_at', [$startOfToday, $endOfToday])
    //             ->when($isStaff, fn($q) => $q->where('staff_id', $targetStaffId))
    //             ->count();

    //         // C. Hiệu suất tổng thể (Tích lũy)
    //         $totalOrdersCount = Order::when($isStaff, fn($q) => $q->where('staff_id', $targetStaffId))->count();

    //         $totalRevenueValue = (int) Order::where('status', 'completed')
    //             ->when($isStaff, fn($q) => $q->where('staff_id', $targetStaffId))
    //             ->sum('total_amount')
    //             + (int) Booking::where('status', 'completed')
    //                 ->when($isStaff, fn($q) => $q->where('staff_id', $targetStaffId))
    //                 ->sum('total_amount');

    //         $fieldsManagedCount = Booking::when($isStaff, fn($q) => $q->where('staff_id', $targetStaffId))->count();

    //         // D. Trạng thái hệ thống
    //         $pendingBookings = Booking::where('status', 'pending')->count();
    //         $playingFields = Booking::where('status', 'playing')->count();

    //         $stats = [
    //             'todayRevenue'    => $todayOrderRev + $todayBookingRev,
    //             'ordersToday'     => $ordersTodayCount,
    //             'pendingBookings' => $pendingBookings,
    //             'playingFields'   => $playingFields,
    //             'rating'          => 5.0,
    //             'totalOrders'     => $totalOrdersCount,
    //             'totalRevenue'    => $totalRevenueValue,
    //             'fieldsManaged'   => $fieldsManagedCount,
    //             'attendance'      => $isStaff ? [
    //                 'totalShifts'     => 24,
    //                 'completedShifts' => 20,
    //                 'remainingShifts' => 4,
    //                 'totalHours'      => 160
    //             ] : ['totalShifts' => 0, 'completedShifts' => 0, 'remainingShifts' => 0, 'totalHours' => 0]
    //         ];

    //         // Trả về dữ liệu chuẩn
    //         $staffData = $isStaff ? [
    //             'id'         => $staff->id,
    //             'name'       => $staff->name,
    //             'avatar'     => $staff->avatar,
    //             'position'   => $staff->position,
    //             'department' => $staff->department ? $staff->department->name : 'N/A',
    //             'shift'      => $staff->shift,
    //             'phone'      => $staff->phone,
    //             'email'      => $staff->email,
    //             'status'     => $staff->status,
    //         ] : [
    //             'id'         => $user->id,
    //             'name'       => $user->name,
    //             'avatar'     => $user->profile?->avatar ?? $user->avatar,
    //             'position'   => 'Admin',
    //             'department' => 'Ban quản trị',
    //             'shift'      => '24/7',
    //             'phone'      => $user->profile?->phone ?? 'N/A',
    //             'email'      => $user->email,
    //             'status'     => 'active',
    //         ];

    //         return response()->json([
    //             'success' => true,
    //             'data' => [
    //                 'staff' => $staffData,
    //                 'stats' => $stats
    //             ]
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    //     }
    // }

    public function getOverview(): JsonResponse
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            // ✅ ÉP MÚI GIỜ VIỆT NAM
            $vnTime = now('Asia/Ho_Chi_Minh');
            $startOfToday = $vnTime->copy()->startOfDay();
            $endOfToday = $vnTime->copy()->endOfDay();
            $currentMonth = $vnTime->month;
            $currentYear = $vnTime->year;

            $staff = Staff::with('department')->where('user_id', $user->id)->first();
            $isStaff = (bool)$staff;
            $targetStaffId = $isStaff ? $staff->id : null;

            // --- A. DOANH THU & ĐƠN HÀNG THẬT ---
            $todayOrderRev = (int) Order::whereBetween('created_at', [$startOfToday, $endOfToday])
                ->where('status', 'completed')
                ->when($isStaff, fn($q) => $q->where('staff_id', $user->id)) // Theo user_id người xử lý
                ->sum('total_amount');

            $todayBookingRev = (int) Booking::whereDate('booking_date', $vnTime->format('Y-m-d'))
                ->where('status', 'completed')
                ->sum('total_amount');

            $ordersTodayCount = Order::whereBetween('created_at', [$startOfToday, $endOfToday])
                ->when($isStaff, fn($q) => $q->where('staff_id', $user->id))
                ->count();

            // --- B. HIỆU SUẤT TỔNG THỂ (TÍCH LŨY) ---
            // --- B. HIỆU SUẤT TỔNG THỂ (TÍCH LŨY) ---

            // 1. Đếm tổng đơn hàng nhân viên này đã xử lý
            $totalOrdersCount = Order::when($isStaff, fn($q) => $q->where('staff_id', $user->id))->count();

            // 2. Tính tổng doanh thu tích lũy nhân viên này mang lại
            $totalRevenueValue = (int) Order::where('status', 'completed')
                ->when($isStaff, fn($q) => $q->where('staff_id', $user->id))
                ->sum('total_amount')
                + (int) Booking::where('status', 'completed')
                    ->when($isStaff, fn($q) => $q->where('staff_id', $user->id)) // ✅ SỬA TẠI ĐÂY: Chỉ tính booking nhân viên này xử lý
                    ->sum('total_amount');

            // 3. ✅ FIX LOGIC: Số lượt sân đã quản lý (Chỉ đếm đơn của nhân viên này)
            $fieldsManagedCount = Booking::when($isStaff, fn($q) => $q->where('staff_id', $user->id))
                ->count();
            // --- C. TRẠNG THÁI HỆ THỐNG THẬT ---
            $pendingBookings = Booking::where('status', 'pending')->count();
            $playingFields = Booking::where('status', 'playing')->count();

            // --- D. LOGIC CA LÀM VIỆC THẬT (THEO THÁNG HIỆN TẠI) ---
            if ($isStaff) {
                // 1. Tổng số ca được gán trong tháng (Bảng shift_assignments)
                $totalShifts = \App\Models\ShiftAssignment::where('staff_id', $targetStaffId)
                    ->whereMonth('work_date', $currentMonth)
                    ->whereYear('work_date', $currentYear)
                    ->count();

                // 2. Số ca đã hoàn thành chấm công (Bảng attendances)
                $completedAttendances = \App\Models\Attendance::where('staff_id', $targetStaffId)
                    ->whereMonth('date', $currentMonth)
                    ->whereYear('date', $currentYear)
                    ->whereIn('status', ['present', 'late']);

                $completedCount = $completedAttendances->count();
                $totalHours = (float) $completedAttendances->sum('work_hours');
                $remainingShifts = max(0, $totalShifts - $completedCount);
            } else {
                $totalShifts = $completedCount = $remainingShifts = $totalHours = 0;
            }

            $stats = [
                'todayRevenue'    => $todayOrderRev + $todayBookingRev,
                'ordersToday'     => $ordersTodayCount,
                'pendingBookings' => $pendingBookings,
                'playingFields'   => $playingFields,
                'rating'          => 5.0, // Ní có thể thay bằng logic tính AVG rating nếu có bảng feedback
                'totalOrders'     => $totalOrdersCount,
                'totalRevenue'    => $totalRevenueValue,
                'fieldsManaged'   => $fieldsManagedCount,
                'attendance'      => [
                    'totalShifts'     => $totalShifts,
                    'completedShifts' => $completedCount,
                    'remainingShifts' => $remainingShifts,
                    'totalHours'      => round($totalHours, 1)
                ]
            ];

            // --- E. THÔNG TIN NHÂN VIÊN ---
            $staffData = $isStaff ? [
                'id'         => $staff->id,
                'name'       => $staff->name,
                'avatar'     => $staff->avatar,
                'position'   => $staff->position,
                'department' => $staff->department ? $staff->department->name : 'N/A',
                'shift'      => $staff->shift,
                'phone'      => $staff->phone,
                'email'      => $staff->email,
                'status'     => $staff->status,
            ] : [
                'id'         => $user->id,
                'name'       => $user->name,
                'avatar'     => $user->profile?->avatar ?? $user->avatar,
                'position'   => 'Admin',
                'department' => 'Ban quản trị',
                'shift'      => '24/7',
                'phone'      => $user->profile?->phone ?? 'N/A',
                'email'      => $user->email,
                'status'     => 'active',
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'staff' => $staffData,
                    'stats' => $stats
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
