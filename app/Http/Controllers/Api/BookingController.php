<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Field;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Api\Booking\StoreBookingRequest;
use Carbon\Carbon; // Đã thêm Carbon

class BookingController extends Controller
{
    /**
     * Lấy lịch sử đặt sân của người dùng hiện tại (Customer) hoặc tất cả (Staff/Admin).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // 1. Chỉ Admin và Staff mới xem được TẤT CẢ bookings
        if ($user->isAdmin() || $user->isStaff()) {
            $bookings = Booking::with('user.profile', 'field')
                ->orderBy('start_time', 'desc')
                ->paginate(10);
        } else {
            // 2. Customer chỉ xem booking của chính mình
            $bookings = $user->bookings()
                ->with('field')
                ->orderBy('start_time', 'desc')
                ->paginate(10);
        }

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    /**
     * Tạo một Booking mới (Sử dụng StoreBookingRequest).
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        try {
            $fieldId = $request->field_id;
            $appTimezone = config('app.timezone', 'Asia/Ho_Chi_Minh');

            // 1. Ép kiểu và tạo đối tượng Carbon
            // Sử dụng parse kèm múi giờ để đảm bảo không bị sai lệch giờ UTC
            $fullStartTime = Carbon::parse($request->start_time, $appTimezone);
            $fullEndTime = Carbon::parse($request->end_time, $appTimezone);

            // 2. Kiểm tra logic thời gian cơ bản
            $now = Carbon::now($appTimezone)->subMinutes(5);

            if ($fullStartTime->lessThan($now)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Thời gian bắt đầu không được ở quá khứ.',
                    'errors' => ['start_time' => ['Thời gian đã trôi qua.']]
                ], 422);
            }

            if ($fullEndTime->lessThanOrEqualTo($fullStartTime)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
                    'errors' => ['end_time' => ['Dữ liệu thời gian không hợp lệ.']]
                ], 422);
            }

            // 3. Trích xuất dữ liệu để lưu vào DB
            $bookingDate = $fullStartTime->toDateString();
            $startTimeStr = $fullStartTime->toTimeString(); // HH:mm:ss
            $endTimeStr = $fullEndTime->toTimeString();     // HH:mm:ss

            // 4. Kiểm tra xung đột lịch (Giữ nguyên logic của bạn)
            $conflict = Booking::where('field_id', $fieldId)
                ->whereDate('booking_date', $bookingDate)
                ->where(function ($query) use ($startTimeStr, $endTimeStr) {
                    $query->where(function ($q) use ($startTimeStr, $endTimeStr) {
                        $q->where('start_time', '<', $endTimeStr)
                            ->where('end_time', '>', $startTimeStr);
                    });
                })
                ->whereIn('status', ['pending', 'confirmed', 'playing'])
                ->exists();

            if ($conflict) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sân bóng đã được đặt trong khoảng thời gian này.',
                    'errors' => ['booking' => ['Trùng lịch sân.']]
                ], 422);
            }

            // 5. TÍNH TOÁN CHÍNH XÁC (SỬA LỖI GIÁ TRỊ ÂM)
            $field = Field::findOrFail($fieldId);

            // Sử dụng diffInMinutes để lấy số phút dương tuyệt đối giữa 2 mốc giờ
            $durationMinutes = $fullStartTime->diffInMinutes($fullEndTime);
            $durationHours = $durationMinutes / 60;

            // Logic giá: tăng 20% nếu sau 20:00 (Sử dụng đơn giá gốc của sân)
            $basePrice = $field->price;
            $finalPricePerHour = ($fullStartTime->hour >= 20) ? ($basePrice * 1.2) : $basePrice;

            // Tổng tiền = Đơn giá (đã tính phụ phí) * Số giờ đá
            $totalAmount = $finalPricePerHour * $durationHours;

            // 6. Tạo Booking với các giá trị đã chuẩn hóa
            $booking = $request->user()->bookings()->create([
                'field_id' => $fieldId,
                'booking_date' => $bookingDate,
                'start_time' => $startTimeStr,
                'end_time' => $endTimeStr,
                'duration' => $durationMinutes, // Lưu số phút đá (ví dụ: 90) để dễ thống kê
                'total_amount' => round($totalAmount), // Lưu số tiền dương (ví dụ: 1125000)
                'status' => 'pending',
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'notes' => $request->notes,
                // Các cột approved_by, confirmed_by sẽ mặc định là NULL khi mới tạo
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đặt sân thành công!',
                'data' => $booking->load('field')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy chi tiết một booking (Dùng Model Binding).
     */
    public function show(Booking $booking): JsonResponse
    {
        $user = request()->user();

        // Kiểm tra quyền sở hữu
        if (!$user->isAdmin() && !$user->isStaff() && $booking->user_id !== $user->id) {
            return response()->json([
                'message' => 'Bạn không có quyền xem chi tiết booking này.'
            ], 403);
        }

        $booking->load('user.profile', 'field');

        return response()->json([
            'success' => true,
            'data' => $booking
        ]);
    }

    public function getSchedule(Field $field, Request $request): JsonResponse
    {
        $request->validate(['date' => 'required|date_format:Y-m-d']);
        $date = $request->date;
        $basePrice = $field->price; // Lấy đúng giá của sân đang chọn

        $schedule = [];
        $startTimeMinutes = 8 * 60; // 08:00
        $endTimeMinutes = 23 * 60;  // 23:00
        $slotDuration = 90;

        for ($time = $startTimeMinutes; $time < $endTimeMinutes; $time += $slotDuration) {
            $start = sprintf('%02d:%02d', floor($time / 60), $time % 60);
            $end = sprintf('%02d:%02d', floor(($time + $slotDuration) / 60), ($time + $slotDuration) % 60);

            // NGHIỆP VỤ GIÁ:
            $currentPrice = $basePrice;

            // Nếu khung giờ bắt đầu từ 20:00 trở đi, cộng thêm phụ phí đêm (ví dụ +20%)
            if ($time >= 20 * 60) {
                $currentPrice = $basePrice * 1.2;
            }

            $isBooked = Booking::where('field_id', $field->id)
                ->where('booking_date', $date)
                ->where('start_time', $start)
                ->whereIn('status', ['pending', 'confirmed', 'playing'])
                ->exists();

            $schedule[] = [
                'start_time' => $start,
                'end_time' => $end,
                'price' => round($currentPrice), // Giá đã tính theo từng sân & khung giờ
                'status' => $isBooked ? 'booked' : 'available',
            ];
        }

        return response()->json(['success' => true, 'data' => $schedule]);
    }

    /**
     * Cập nhật thông tin đặt sân (Dành cho Admin).
     */
    public function update(Request $request, Booking $booking): JsonResponse
    {
        try {
            $appTimezone = config('app.timezone', 'Asia/Ho_Chi_Minh');

            // Xác định rõ định dạng gửi lên để Carbon không parse sai
            $fullStartTime = Carbon::createFromFormat('Y-m-d H:i:s', $request->start_time, $appTimezone);
            $fullEndTime = Carbon::createFromFormat('Y-m-d H:i:s', $request->end_time, $appTimezone);

            if ($fullEndTime->lessThanOrEqualTo($fullStartTime)) {
                return response()->json(['success' => false, 'message' => 'Thời gian kết thúc phải sau thời gian bắt đầu.'], 422);
            }

            $field = Field::findOrFail($request->field_id);
            $durationMinutes = $fullStartTime->diffInMinutes($fullEndTime);
            $durationHours = $durationMinutes / 60;

            $basePrice = $field->price;
            $finalPrice = ($fullStartTime->hour >= 20) ? ($basePrice * 1.2) : $basePrice;
            $totalAmount = $finalPrice * $durationHours;

            $booking->update([
                'field_id'       => $request->field_id,
                'customer_name'  => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'booking_date'   => $fullStartTime->toDateString(),
                'start_time'     => $fullStartTime->toTimeString(),
                'end_time'       => $fullEndTime->toTimeString(),
                'duration'       => $durationMinutes,
                'total_amount'   => round($totalAmount),
                'status'         => (string) ($request->status ?? $booking->status),
                'notes'          => $request->notes,
                // Ép kiểu về số cho database bigint
                'approved_by'    => $request->approved_by ? (int)$request->approved_by : null,
                'confirmed_by'   => $request->confirmed_by ? (int)$request->confirmed_by : null,
            ]);

            return response()->json(['success' => true, 'message' => 'Cập nhật thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Chuyển đổi trạng thái nhanh từ trang danh sách hoặc chi tiết.
     */
    public function changeStatus(Request $request, Booking $booking): JsonResponse
    {
        $newStatus = $request->status;
        $user = $request->user(); // Lấy người đang đăng nhập

        // Logic tự động điền Audit Log
        $updateData = ['status' => $newStatus];

        if ($newStatus === 'approved') {
            $updateData['approved_by'] = $user->id;
            $updateData['approved_at'] = now();
        }

        if ($newStatus === 'playing' || $newStatus === 'completed') {
            $updateData['confirmed_by'] = $user->id;
            if ($newStatus === 'completed') {
                $updateData['confirmed_at'] = now();
            }
        }

        try {
            $booking->update($updateData);
            return response()->json([
                'success' => true,
                'message' => "Đã chuyển trạng thái sang: " . strtoupper($newStatus),
                'data' => $booking
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
