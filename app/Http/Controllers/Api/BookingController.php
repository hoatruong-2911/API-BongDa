<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Field;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Api\Booking\StoreBookingRequest;
use App\Models\Customer;
use App\Models\Notification;
use Carbon\Carbon; // Đã thêm Carbon
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{


    public function index(Request $request): JsonResponse
    {
        $user = $request->user(); // Lấy thông tin user từ token

        // 1. Nếu là Admin hoặc Staff: Cho phép xem TẤT CẢ
        if ($user->isAdmin() || $user->isStaff()) {
            $bookings = Booking::with(['user.profile', 'field'])
                ->orderBy('created_at', 'desc')
                ->paginate(7);
        } else {
            // 2. Nếu là Khách hàng: CHỈ lấy đơn của chính mình
            // 🛑 ĐÂY LÀ CHỖ QUAN TRỌNG ĐỂ KHÔNG XEM NHẦM ĐƠN NGƯỜI KHÁC
            $bookings = Booking::where('user_id', $user->id)
                ->with(['field'])
                ->orderBy('created_at', 'desc')
                ->paginate(7);
        }

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }


    public function store(StoreBookingRequest $request): JsonResponse
    {
        try {
            $fieldId = $request->field_id;
            $appTimezone = config('app.timezone', 'Asia/Ho_Chi_Minh');

            // 1. Ép kiểu và tạo đối tượng Carbon chính xác
            $fullStartTime = Carbon::parse($request->start_time, $appTimezone);
            $fullEndTime = Carbon::parse($request->end_time, $appTimezone);

            // 2. 🛑 CHẶN THỜI GIAN QUÁ KHỨ
            $now = Carbon::now($appTimezone);
            if ($fullStartTime->lessThan($now)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Giờ này đã qua rồi bro ơi! Bây giờ đã là ' . $now->format('H:i') . ' ngày ' . $now->format('d/m/Y') . '.',
                    'errors' => ['start_time' => ['Thời gian bắt đầu không được ở quá khứ.']]
                ], 422);
            }

            if ($fullEndTime->lessThanOrEqualTo($fullStartTime)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
                    'errors' => ['end_time' => ['Dữ liệu thời gian không hợp lệ.']]
                ], 422);
            }

            // 3. Trích xuất dữ liệu
            $bookingDate = $fullStartTime->toDateString();
            $startTimeStr = $fullStartTime->toTimeString();
            $endTimeStr = $fullEndTime->toTimeString();

            // 4. Kiểm tra xung đột lịch
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

            // 5. TÍNH TOÁN CHI PHÍ & TIỀN CỌC
            $field = Field::findOrFail($fieldId);
            $durationMinutes = $fullStartTime->diffInMinutes($fullEndTime);

            // 🛑 VALIDATE THỜI GIAN TỐI THIỂU: Yêu cầu đặt sân ít nhất 1 giờ (60 phút)
            if ($durationMinutes < 60) {
                return response()->json([
                    'success' => false,
                    'message' => 'Thời gian đặt sân tối thiểu phải là 1 giờ (60 phút).',
                    'errors' => ['end_time' => ['Thời gian đặt sân quá ngắn. Vui lòng chọn thời gian kết thúc xa hơn.']]
                ], 422);
            }

            $durationHours = $durationMinutes / 60;

            $basePrice = $field->price;
            $finalPricePerHour = ($fullStartTime->hour >= 20) ? ($basePrice * 1.2) : $basePrice;
            $totalAmount = $finalPricePerHour * $durationHours;

            // 🚀 TÍNH TOÁN TIỀN CỌC: Bằng 30% tổng chi phí của buổi đá lẻ
            $depositAmount = $totalAmount * 0.30;

            // 6. Tạo Booking (Bổ sung lưu thông tin tiền cọc)
            $booking = $request->user()->bookings()->create([
                'field_id'        => $fieldId,
                'booking_date'    => $bookingDate,
                'start_time'      => $startTimeStr,
                'end_time'        => $endTimeStr,
                'duration'        => $durationMinutes,
                'total_amount'    => round($totalAmount),
                'deposit_amount'  => round($depositAmount), // 🚀 BỔ SUNG: Lưu số tiền cọc 30%
                'status'          => 'pending',
                'payment_status'  => 'unpaid',              // 🚀 BỔ SUNG: Đơn mới tạo mặc định chưa trả tiền cọc
                'customer_name'   => $request->customer_name,
                'customer_phone'  => $request->customer_phone,
                'notes'           => $request->notes,
            ]);

            Notification::create([
                'type' => 'booking_new',
                'title' => 'LỊCH ĐẶT SÂN MỚI!',
                'message' => "Khách {$request->customer_name} vừa đặt sân vào lúc " . Carbon::parse($request->start_time)->format('H:i d/m'),
                'link' => '/staff/bookings',
                'is_read' => false
            ]);
            Notification::create([
                'type' => 'booking_new',
                'title' => 'LỊCH ĐẶT SÂN MỚI!',
                'message' => "Khách {$request->customer_name} vừa đặt sân vào lúc " . Carbon::parse($request->start_time)->format('H:i d/m'),
                'link' => '/staff/bookings', // Đường dẫn trang quản lý đặt sân ở frontend
                'is_read' => false
            ]);

            // 🛑 LOGIC ĐỒNG BỘ SANG BẢNG CUSTOMERS RỰC RỠ
            if ($request->customer_phone) {
                $customer = Customer::updateOrCreate(
                    ['phone' => $request->customer_phone],
                    [
                        'name'   => $request->customer_name,
                        'email'  => $request->email ?? $request->customer_phone . '@guest.com',
                        'status' => 'active',
                    ]
                );

                // Cộng dồn chỉ số
                $customer->increment('total_bookings');
                $customer->total_spent += round($totalAmount);
                $customer->last_booking = now();

                // Tự động lên VIP nếu chi tiêu đạt mốc
                if ($customer->total_spent >= 5000000) {
                    $customer->is_vip = true;
                }
                $customer->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Đặt sân lẻ thành công rực rỡ! Vui lòng thực hiện chuyển khoản cọc.',
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
     * 🚀 HÀM ĐẶT SÂN RIÊNG CHO STAFF TẠI QUẦY (Walk-in / Staff Panel)
     * Tránh ảnh hưởng hoàn toàn đến luồng đặt của Khách hàng từ xa
     */
    // public function store2(Request $request): JsonResponse
    // {
    //     try {
    //         $user = $request->user(); // Nhân viên hoặc Admin đang trực ca quầy
    //         $appTimezone = config('app.timezone', 'Asia/Ho_Chi_Minh');

    //         // Parse cấu trúc thời gian từ Payload của Staff gửi sang
    //         $fullStartTime = Carbon::parse($request->start_time, $appTimezone);
    //         $fullEndTime = Carbon::parse($request->end_time, $appTimezone);

    //         $bookingDate = $fullStartTime->toDateString();
    //         $startTimeStr = $fullStartTime->toTimeString();
    //         $endTimeStr = $fullEndTime->toTimeString();

    //         // Tính toán thời lượng sử dụng sân
    //         $durationMinutes = $fullStartTime->diffInMinutes($fullEndTime);
    //         $durationHours = $durationMinutes / 60;

    //         // Truy xuất thông tin giá tiền cấu hình của Sân bóng
    //         $field = Field::findOrFail($request->field_id);
    //         $basePrice = $field->price;

    //         // Tự động tính phụ phí ca đêm 20% nếu giờ bắt đầu vào sân từ 20h kịch trần trở đi
    //         $finalPricePerHour = ($fullStartTime->hour >= 20) ? ($basePrice * 1.2) : $basePrice;
    //         $totalAmount = $finalPricePerHour * $durationHours;

    //         // 🚀 BÓC TÁCH DÒNG TIỀN QUẦY: Hứng chuẩn chuỗi fully_paid / partial_paid từ Staff gửi lên
    //         $paymentStatus = $request->payment_status ?? 'unpaid';

    //         // Xử lý tính toán lượng tiền đã thu thực tế để đối soát đúng với Index Admin
    //         if ($paymentStatus === 'fully_paid') {
    //             $depositAmount = $totalAmount; // Nếu trả đủ 100% thì gán lượng tiền thu bằng tổng tiền bill
    //         } else if ($paymentStatus === 'partial_paid') {
    //             $depositAmount = $totalAmount * 0.30; // Nếu cọc 30% thì tính lượng cọc tạm giữ chỗ
    //         } else {
    //             $depositAmount = 0;
    //         }

    //         // Tiến hành lưu thông tin đặt sân trực tiếp xuống cơ sở dữ liệu MySQL
    //         $booking = Booking::create([
    //             'user_id'         => $user->id, // Ghi nhận ID tài khoản xử lý tạo đơn
    //             'field_id'        => $request->field_id,
    //             'booking_date'    => $bookingDate,
    //             'start_time'      => $startTimeStr,
    //             'end_time'        => $endTimeStr,
    //             'duration'        => $durationMinutes,
    //             'total_amount'    => round($totalAmount),
    //             'deposit_amount'  => round($depositAmount), // Lưu mốc tiền thu thực tế để phân biệt Tag Index
    //             'payment_status'  => $paymentStatus,        // Lưu chuẩn xác 'fully_paid' hoặc 'partial_paid'
    //             'customer_name'   => $request->customer_name,
    //             'customer_phone'  => $request->customer_phone,
    //             'notes'           => $request->notes,
    //             'status'          => $request->status ?? 'approved', // Mặc định Staff tạo đá lẻ quầy là duyệt luôn hoặc lên sân
    //         ]);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Staff tạo đơn đặt sân tại quầy và ghi nhận dòng tiền thành công rực rỡ!',
    //             'data' => $booking->load('field')
    //         ], 201);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Lỗi hệ thống xử lý lưu đơn tại quầy: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }
    public function store2(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $appTimezone = config('app.timezone', 'Asia/Ho_Chi_Minh');

            $fullStartTime = Carbon::parse($request->start_time, $appTimezone);
            $fullEndTime = Carbon::parse($request->end_time, $appTimezone);

            $durationMinutes = $fullStartTime->diffInMinutes($fullEndTime);
            $durationHours = $durationMinutes / 60;

            $field = Field::findOrFail($request->field_id);
            $totalAmount = $field->price * $durationHours;
            if ($fullStartTime->hour >= 20) {
                $totalAmount = $totalAmount * 1.2; // Phụ phí ca đêm 20%
            }

            // Hứng hình thức thanh toán từ Frontend quầy gửi lên (full hoặc deposit)
            $paymentType = $request->payment_type ?? 'full';

            // 🚀 ĐÃ CẢI TIẾN LOGIC DÒNG TIỀN THEO ĐÚNG KẾ HOẠCH CỦA NÍ:
            if ($paymentType === 'deposit') {
                $paymentStatus = 'partial_paid';
                $depositAmount = $totalAmount * 0.30; // Tiền cọc giữ chỗ = 30%
                $amountPaid    = 0; // 🚀 SỬA LẠI: Trả cọc thì KHÔNG lưu vào cột amount_paid nữa
            } else {
                $paymentStatus = 'fully_paid';
                $depositAmount = 0;                   // Trả đủ rồi thì khoản nợ cọc về bằng 0
                $amountPaid    = $totalAmount;        // Quầy thực thu trọn gói 100% tiền sân
            }

            $booking = Booking::create([
                'user_id'         => $user->id,
                'field_id'        => $request->field_id,
                'staff_id'        => $user->id, // Ghi nhận nhân viên quầy trực tiếp xử lý
                'booking_date'    => $fullStartTime->toDateString(),
                'start_time'      => $fullStartTime->toTimeString(),
                'end_time'        => $fullEndTime->toTimeString(),
                'duration'        => $durationMinutes,
                'total_amount'    => round($totalAmount),
                'deposit_amount'  => round($depositAmount), // Phản ánh đúng bản chất luồng cọc
                'amount_paid'     => round($amountPaid),     // 🚀 CỘT MỚI: Đã được điền số tiền thật kịch trần!
                'payment_status'  => $paymentStatus,
                'customer_name'   => $request->customer_name,
                'customer_phone'  => $request->customer_phone,
                'notes'           => $request->notes,
                'status'          => $request->status ?? 'approved',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Staff tạo đơn tại quầy và phân rã dòng tiền CSDL thành công rực rỡ!',
                'data' => $booking->load('field')
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    /**
     * Lấy chi tiết một booking.
     */
    // public function show($id): JsonResponse // Đổi Booking $booking thành $id để query tươi mới hoàn toàn
    // {
    //     // 🛑 DÙNG TRUY VẤN TƯƠI ĐỂ ÉP NẠP FIELD
    //     $booking = Booking::with(['field', 'user.profile'])->find($id);

    //     if (!$booking) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Không tìm thấy hóa đơn ID: ' . $id
    //         ], 404);
    //     }

    //     $user = request()->user();
    //     // Logic kiểm tra quyền của bro (giữ nguyên)
    //     if (!$user->isAdmin() && !$user->isStaff() && $booking->user_id !== $user->id) {
    //         return response()->json(['message' => 'Bạn không có quyền xem đơn này.'], 403);
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'data' => $booking // Trả về object đã được nạp Field rực rỡ
    //     ]);
    // }
    public function show($id): JsonResponse
    {
        try {
            // 🚀 ĐÃ CẬP NHẬT: Load đầy đủ quan hệ field để tránh lỗi null tên sân ngoài giao diện
            $booking = Booking::with(['field', 'user.profile'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $booking
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đơn đặt sân hoặc lỗi hệ thống: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Lấy lịch các ca trống của một sân, được tối ưu để hiển thị linh hoạt trên frontend.
     * - Sinh ra các ca có thể bắt đầu mỗi 30 phút.
     * - Kiểm tra tính khả dụng cho một ca mặc định 90 phút từ thời điểm bắt đầu đó.
     * - Tối ưu hiệu suất bằng cách chỉ query DB một lần.
     */
    public function getSchedule(Field $field, Request $request): JsonResponse
    {
        $request->validate(['date' => 'required|date_format:Y-m-d']);
        $date = $request->date;
        $basePrice = $field->price;
        $appTimezone = config('app.timezone', 'Asia/Ho_Chi_Minh');

        // 1. Lấy tất cả các booking đang chiếm sân trong ngày để tối ưu, tránh query trong vòng lặp
        $existingBookings = Booking::where('field_id', $field->id)
            ->whereDate('booking_date', $date)
            ->whereIn('status', ['pending', 'approved', 'confirmed', 'playing', 'paid']) // Các trạng thái chiếm sân
            ->select('start_time', 'end_time')
            ->get();

        $schedule = [];
        $operatingStartHour = 8;  // Sân bắt đầu hoạt động lúc 8h
        $operatingEndHour = 23; // Sân kết thúc hoạt động lúc 23h
        $slotIncrement = 30; // Bước nhảy 30 phút
        $defaultDuration = 90; // Ca mặc định là 90 phút

        $now = Carbon::now($appTimezone);
        $requestedDate = Carbon::parse($date, $appTimezone)->startOfDay();

        // 2. Vòng lặp sinh các ca (slots) với bước nhảy 30 phút
        for ($hour = $operatingStartHour; $hour < $operatingEndHour; $hour++) {
            for ($minute = 0; $minute < 60; $minute += $slotIncrement) {
                $slotStart = $requestedDate->copy()->setTime($hour, $minute);

                // Chỉ hiển thị các ca trong tương lai (cho phép đặt trước 5 phút)
                if ($slotStart->lessThan($now->copy()->subMinutes(5))) {
                    continue;
                }

                $slotEnd = $slotStart->copy()->addMinutes($defaultDuration);

                // Không tạo ca nếu giờ kết thúc vượt quá giờ hoạt động
                if ($slotEnd->hour >= $operatingEndHour && $slotEnd->minute > 0) {
                    continue;
                }

                // 3. Kiểm tra xung đột với các booking đã có (logic trong memory, không query DB)
                $isBooked = $this->isSlotOverlapping($slotStart, $slotEnd, $existingBookings, $date, $appTimezone);

                // 4. Tính giá cho ca 90 phút
                $hourlyPrice = ($slotStart->hour >= 20) ? ($basePrice * 1.2) : $basePrice;
                $slotPrice = $hourlyPrice * ($defaultDuration / 60);

                $schedule[] = [
                    'start_time' => $slotStart->format('H:i'),
                    'end_time'   => $slotEnd->format('H:i'),
                    'price'      => round($slotPrice),
                    'status'     => $isBooked ? 'booked' : 'available',
                ];
            }
        }

        return response()->json(['success' => true, 'data' => $schedule]);
    }

    /**
     * Cập nhật thông tin đặt sân (Dành cho Admin).
     */

    // public function update(Request $request, $id): JsonResponse
    // {
    //     try {
    //         $booking = Booking::findOrFail($id);
    //         $appTimezone = config('app.timezone', 'Asia/Ho_Chi_Minh');

    //         // 🚀 Đã đồng bộ: Nhận chuỗi thời gian an toàn từ Frontend gửi lên
    //         $fullStartTime = Carbon::parse($request->start_time, $appTimezone);
    //         $fullEndTime = Carbon::parse($request->end_time, $appTimezone);

    //         $bookingDate = $fullStartTime->toDateString();
    //         $startTimeStr = $fullStartTime->toTimeString();
    //         $endTimeStr = $fullEndTime->toTimeString();

    //         // Tính toán lại số phút và số tiền phòng trường hợp Admin sửa giờ đá
    //         $durationMinutes = $fullStartTime->diffInMinutes($fullEndTime);
    //         $durationHours = $durationMinutes / 60;

    //         $field = Field::findOrFail($request->field_id);
    //         $basePrice = $field->price;
    //         $finalPricePerHour = ($fullStartTime->hour >= 20) ? ($basePrice * 1.2) : $basePrice;
    //         $totalAmount = $finalPricePerHour * $durationHours;

    //         // Tự động tính lại hạn mức cọc 30% phòng khi Admin thay đổi khung giờ
    //         $depositAmount = $totalAmount * 0.30;

    //         // Cập nhật dữ liệu xuống MySQL
    //         $booking->update([
    //             'field_id'        => $request->field_id,
    //             'booking_date'    => $bookingDate,
    //             'start_time'      => $startTimeStr,
    //             'end_time'        => $endTimeStr,
    //             'duration'        => $durationMinutes,
    //             'total_amount'    => round($totalAmount),
    //             'deposit_amount'  => round($depositAmount), // 🚀 BỔ SUNG: Lưu tiền cọc cập nhật
    //             'payment_status'  => $request->payment_status ?? $booking->payment_status, // 🚀 BỔ SUNG: Trạng thái cọc
    //             'customer_name'   => $request->customer_name,
    //             'customer_phone'  => $request->customer_phone,
    //             'notes'           => $request->notes,
    //             'status'          => $request->status,
    //             'approved_by'     => $request->approved_by,
    //             'confirmed_by'    => $request->confirmed_by,
    //         ]);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Cập nhật đơn đặt sân thành công rực rỡ!',
    //             'data' => $booking->load('field')
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Lỗi hệ thống khi sửa đơn: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $booking = Booking::findOrFail($id);
            $appTimezone = config('app.timezone', 'Asia/Ho_Chi_Minh');

            // Ép kiểu thời gian an toàn từ chuỗi Full DateTime Frontend gửi lên
            $fullStartTime = Carbon::parse($request->start_time, $appTimezone);
            $fullEndTime = Carbon::parse($request->end_time, $appTimezone);

            $bookingDate = $fullStartTime->toDateString();
            $startTimeStr = $fullStartTime->toTimeString();
            $endTimeStr = $fullEndTime->toTimeString();

            $durationMinutes = $fullStartTime->diffInMinutes($fullEndTime);
            $durationHours = $durationMinutes / 60;

            $field = Field::findOrFail($request->field_id);
            $basePrice = $field->price;
            $finalPricePerHour = ($fullStartTime->hour >= 20) ? ($basePrice * 1.2) : $basePrice;
            $totalAmount = $finalPricePerHour * $durationHours;

            // 🚀 ĐÃ SỬA CHUẨN: Đồng bộ ENUM giá trị dòng tiền của Database
            $paymentStatus = $request->payment_status ?? $booking->payment_status;

            // Fix lỗi đổi chữ 'paid' thành 'fully_paid' cho khớp ENUM của hệ thống
            if ($paymentStatus === 'paid') {
                $paymentStatus = 'fully_paid';
            }

            // Giữ nguyên số tiền cọc 30% để làm mốc đối soát ở trang danh sách, không dọn về 0 nữa!
            $depositAmount = $totalAmount * 0.30;

            // Cập nhật dữ liệu trực tiếp xuống MySQL
            $booking->update([
                'field_id'        => $request->field_id,
                'booking_date'    => $bookingDate,
                'start_time'      => $startTimeStr,
                'end_time'        => $endTimeStr,
                'duration'        => $durationMinutes,
                'total_amount'    => round($totalAmount),
                'deposit_amount'  => round($depositAmount),
                'payment_status'  => $paymentStatus, // Lưu chuỗi chuẩn 'fully_paid', 'partial_paid' hoặc 'unpaid'
                'customer_name'   => $request->customer_name,
                'customer_phone'  => $request->customer_phone,
                'notes'           => $request->notes,
                'status'          => $request->status,
                'approved_by'     => $request->approved_by,
                'confirmed_by'    => $request->confirmed_by,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Admin cập nhật và sửa đổi trạng thái đơn rực rỡ!',
                'data' => $booking->load('field')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống khi sửa đơn: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Chuyển đổi trạng thái nhanh từ trang danh sách hoặc chi tiết.
     * Cập nhật thêm staff_id để tính KPI cho nhân viên.
     */
    // public function changeStatus(Request $request, Booking $booking): JsonResponse
    // {
    //     $newStatus = $request->status;
    //     $user = $request->user(); // Lấy người đang đăng nhập

    //     // Logic tự động điền Audit Log
    //     $updateData = ['status' => $newStatus];

    //     if ($newStatus === 'approved') {
    //         $updateData['approved_by'] = $user->id;
    //         $updateData['approved_at'] = now();
    //     }

    //     if ($newStatus === 'playing' || $newStatus === 'completed') {
    //         $updateData['confirmed_by'] = $user->id;
    //         if ($newStatus === 'completed') {
    //             $updateData['confirmed_at'] = now();
    //         }
    //     }

    //     try {
    //         $booking->update($updateData);
    //         return response()->json([
    //             'success' => true,
    //             'message' => "Đã chuyển trạng thái sang: " . strtoupper($newStatus),
    //             'data' => $booking
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    //     }
    // }
    /**
     * Chuyển đổi trạng thái nhanh từ trang danh sách hoặc chi tiết.
     * Cập nhật thêm staff_id để tính KPI cho nhân viên.
     */
    public function changeStatus(Request $request, Booking $booking): JsonResponse
    {
        $newStatus = $request->status;
        $user = $request->user(); // Lấy thông tin Staff/Admin đang thực hiện

        // 1. ✅ LOGIC CHẶN BẮT ĐẦU SAI NGÀY (Gộp từ changeStatus2 của bro vào cho gọn)
        if ($newStatus === 'playing') {
            $today = now()->toDateString();
            $bookingDate = \Carbon\Carbon::parse($booking->booking_date)->toDateString();

            if ($today !== $bookingDate) {
                return response()->json([
                    'success' => false,
                    'message' => "Không thể bắt đầu! Đơn này đặt cho ngày {$bookingDate}, hôm nay là {$today} ní ơi!"
                ], 403);
            }
        }

        // 2. ✅ CHUẨN BỊ DỮ LIỆU CẬP NHẬT
        $updateData = [
            'status'   => $newStatus,
            'staff_id' => $user->id, // 👈 Ghi nhận nhân viên xử lý vào đây
        ];

        // Tự động điền Audit Log như cũ của bro
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
            // 3. ✅ CẬP NHẬT VÀO DATABASE
            $booking->update($updateData);

            return response()->json([
                'success' => true,
                'message' => "Đã chuyển trạng thái sang: " . strtoupper($newStatus) . " (Ghi nhận cho nhân viên: {$user->name})",
                'data' => $booking->load(['field', 'staff']) // Load thêm staff để Frontend hiển thị nếu cần
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Xóa hoàn toàn một booking khỏi hệ thống (Dành cho Admin/Staff).
     */
    public function destroy(Booking $booking): JsonResponse
    {
        $user = request()->user();

        // 1. Chỉ Admin hoặc Staff mới có quyền xóa
        if (!$user->isAdmin() && !$user->isStaff()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện hành động này.'
            ], 403);
        }

        try {
            $booking->delete();

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa đơn đặt sân thành công!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancelBooking(Booking $booking): JsonResponse
    {
        /** @var \App\Models\User $user */ // 🛑 THÊM DÒNG NÀY ĐỂ MÁY HIỂU BIẾN $user CÓ HÀM isAdmin
        $user = auth('sanctum')->user();

        // 1. Kiểm tra quyền: Đơn này có phải của ông đang login không?
        if ($booking->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['message' => 'Bro định hủy đơn của người khác à? Không được nhé!'], 403);
        }

        // 2. Kiểm tra trạng thái: Chỉ cho hủy khi đang 'pending' (Chờ duyệt)
        // Nếu đơn đã duyệt (approved) hoặc đang đá (playing) thì không cho khách tự hủy
        if ($booking->status !== 'pending') {
            return response()->json(['message' => 'Đơn đã được xử lý, không thể tự hủy. Vui lòng gọi Hotline!'], 422);
        }

        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Đã hủy lượt đặt sân rực rỡ!'
        ]);
    }

    /**
     * Lấy lịch bận thực tế của MỘT sân cụ thể để khách hàng chọn giờ.
     * Loại bỏ các đơn đã Hủy hoặc đã Hoàn thành để giải phóng sân.
     */
    public function getFieldSchedule(Request $request): JsonResponse
    {
        $request->validate([
            'field_id' => 'required|exists:fields,id',
            'date'     => 'required|date_format:Y-m-d',
        ]);

        $fieldId = $request->query('field_id');
        $date    = $request->query('date');

        // Lấy các booking đang chiếm dụng sân (Chờ đá, Đang đá, Đã duyệt...)
        // Loại bỏ 'cancelled' (Hủy) và 'completed' (Hoàn thành)
        $bookings = Booking::where('field_id', $fieldId)
            ->whereDate('booking_date', $date)
            ->whereNotIn('status', ['cancelled', 'completed', 'rejected'])
            ->select(['id', 'field_id', 'start_time', 'end_time', 'status'])
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $bookings
        ]);
    }

    public function changeStatus2(Request $request, Booking $booking): JsonResponse
    {
        $newStatus = $request->input('status');

        // ✅ LOGIC CHẶN BẮT ĐẦU SAI NGÀY
        if ($newStatus === 'playing') {
            $today = now()->toDateString();
            $bookingDate = \Carbon\Carbon::parse($booking->booking_date)->toDateString();

            if ($today !== $bookingDate) {
                return response()->json([
                    'success' => false,
                    'message' => "Không thể bắt đầu! Đơn này đặt cho ngày {$bookingDate}, hôm nay là {$today} ní ơi!"
                ], 403);
            }
        }

        $booking->update(['status' => $newStatus]);
        return response()->json(['success' => true, 'data' => $booking]);
    }

    /**
     * Hàm hỗ trợ kiểm tra một slot có bị trùng với các booking đã có hay không.
     */
    private function isSlotOverlapping(Carbon $slotStart, Carbon $slotEnd, $existingBookings, string $date, string $timezone): bool
    {
        foreach ($existingBookings as $booking) {
            $bookingStart = Carbon::parse($date . ' ' . $booking->start_time, $timezone);
            $bookingEnd = Carbon::parse($date . ' ' . $booking->end_time, $timezone);

            // Công thức kiểm tra 2 khoảng thời gian giao nhau: (StartA < EndB) and (EndA > StartB)
            if ($slotStart->lt($bookingEnd) && $slotEnd->gt($bookingStart)) {
                return true; // Nếu đã trùng, trả về true ngay
            }
        }
        return false;
    }

    // API XÁC NHẬN CỌC CHO ADMIN


    // API XÁC NHẬN CỌC CHO ADMIN (CẢ LẺ VÀ CHUỖI ĐỊNH KỲ)
    public function confirmDeposit($id_or_group)
    {
        // 1. Thử tìm xem đây có phải là một đơn lẻ hợp lệ dựa theo ID hay không
        $isSingleBooking = Booking::where('id', $id_or_group)->exists();

        if ($isSingleBooking) {
            // 👉 LUỒNG ĐƠN LẺ: Tìm đúng đơn theo ID và đang chưa thanh toán cọc
            $bookings = Booking::where('id', $id_or_group)->where('payment_status', 'unpaid');
        } else {
            // 👉 LUỒNG ĐỊNH KỲ: Tìm theo mã chuỗi recurring_group_id và đang chưa thanh toán cọc
            $bookings = Booking::where('recurring_group_id', $id_or_group)->where('payment_status', 'unpaid');
        }

        // Nếu kiểm tra không có bản ghi nào thỏa mãn điều kiện
        if ($bookings->count() == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy lượt đặt sân này, hoặc hóa đơn đã được xử lý thanh toán cọc trước đó.'
            ], 404);
        }

        // Cập nhật trạng thái đồng loạt sang "Đã cọc 30%" và "Kích hoạt lịch đã duyệt"
        $bookings->update([
            'payment_status' => 'partial_paid',
            'status' => 'approved'
        ]);

        return response()->json([
            'success' => true,
            'message' => "Đã xác nhận kích hoạt cọc giữ chỗ thành công cho đơn/chuỗi: {$id_or_group}"
        ]);
    }
    public function createRecurring(Request $request): JsonResponse
    {
        $request->validate([
            'field_id'         => 'required|exists:fields,id',
            'start_date'       => 'required|date|after_or_equal:today',
            'number_of_months' => 'required|in:1,3,6',
            'start_time'       => 'required|date_format:H:i',
            'end_time'         => 'required|date_format:H:i|after:start_time',
        ]);

        $field = Field::find($request->field_id);
        $startDate = Carbon::parse($request->start_date);
        $numberOfWeeks = $request->number_of_months * 4;

        $recurringGroupId = 'REC-' . now()->format('ym') . '-' . strtoupper(Str::random(5));

        $generatedDates = [];
        for ($i = 0; $i < $numberOfWeeks; $i++) {
            $generatedDates[] = $startDate->copy()->addWeeks($i)->format('Y-m-d');
        }

        foreach ($generatedDates as $date) {
            $fullStart = $date . ' ' . $request->start_time . ':00';
            $fullEnd = $date . ' ' . $request->end_time . ':00';

            $isOverlapped = Booking::where('field_id', $request->field_id)
                ->where('status', '!=', 'cancelled')
                ->where(function ($query) use ($fullStart, $fullEnd) {
                    $query->where('start_time', '<', $fullEnd)
                        ->where('end_time', '>', $fullStart);
                })->exists();

            if ($isOverlapped) {
                $formattedDate = Carbon::parse($date)->format('d/m/Y');
                return response()->json([
                    'success' => false,
                    'message' => "Sự cố trùng lịch vào ngày {$formattedDate}. Vui lòng chọn khung giờ hoặc sân khác!"
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            $userId = $request->user()->id;

            foreach ($generatedDates as $date) {
                $fullStart = Carbon::parse($date . ' ' . $request->start_time . ':00');
                $fullEnd = Carbon::parse($date . ' ' . $request->end_time . ':00');

                $hours = $fullStart->diffInMinutes($fullEnd) / 60;
                $totalAmount = $hours * ($field->price ?? 0);
                $depositAmount = $totalAmount * 0.30;

                Booking::create([
                    'user_id'            => $userId,
                    'field_id'           => $request->field_id,
                    'booking_date'       => $date,
                    'recurring_group_id' => $recurringGroupId,
                    'start_time'         => $request->start_time . ':00',
                    'end_time'           => $request->end_time . ':00',
                    'duration'           => $hours * 60,
                    'total_amount'       => round($totalAmount),
                    'deposit_amount'     => round($depositAmount),
                    'status'             => 'pending',
                    'payment_status'     => 'unpaid',
                    'customer_name'      => $request->customer_name ?? '',
                    'customer_phone'     => $request->customer_phone ?? '',
                    'notes'              => $request->notes ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'success'            => true,
                'message'            => 'Đặt lịch sân định kỳ thành công! Vui lòng thực hiện chuyển khoản cọc.',
                'recurring_group_id' => $recurringGroupId,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra trong quá trình thiết lập chuỗi hóa đơn.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
