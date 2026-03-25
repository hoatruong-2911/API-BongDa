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

class BookingController extends Controller
{
    /**
     * Lấy lịch sử đặt sân của người dùng hiện tại (Customer) hoặc tất cả (Staff/Admin).
     */
    // public function index(Request $request): JsonResponse
    // {
    //     $user = $request->user();

    //     // 1. Chỉ Admin và Staff mới xem được TẤT CẢ bookings
    //     if ($user->isAdmin() || $user->isStaff()) {
    //         $bookings = Booking::with('user.profile', 'field')
    //             ->orderBy('start_time', 'desc')
    //             ->paginate(10);
    //     } else {
    //         // 2. Customer chỉ xem booking của chính mình
    //         $bookings = $user->bookings()
    //             ->with('field')
    //             ->orderBy('start_time', 'desc')
    //             ->paginate(10);
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'data' => $bookings
    //     ]);
    // }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user(); // Lấy thông tin user từ token

        // 1. Nếu là Admin hoặc Staff: Cho phép xem TẤT CẢ
        if ($user->isAdmin() || $user->isStaff()) {
            $bookings = Booking::with(['user.profile', 'field'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } else {
            // 2. Nếu là Khách hàng: CHỈ lấy đơn của chính mình
            // 🛑 ĐÂY LÀ CHỖ QUAN TRỌNG ĐỂ KHÔNG XEM NHẦM ĐƠN NGƯỜI KHÁC
            $bookings = Booking::where('user_id', $user->id)
                ->with(['field'])
                ->orderBy('created_at', 'desc')
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
    // public function store(StoreBookingRequest $request): JsonResponse
    // {
    //     try {
    //         $fieldId = $request->field_id;
    //         $appTimezone = config('app.timezone', 'Asia/Ho_Chi_Minh');

    //         // 1. Ép kiểu và tạo đối tượng Carbon
    //         // Sử dụng parse kèm múi giờ để đảm bảo không bị sai lệch giờ UTC
    //         $fullStartTime = Carbon::parse($request->start_time, $appTimezone);
    //         $fullEndTime = Carbon::parse($request->end_time, $appTimezone);

    //         // 2. Kiểm tra logic thời gian cơ bản
    //         $now = Carbon::now($appTimezone)->subMinutes(5);

    //         if ($fullStartTime->lessThan($now)) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Thời gian bắt đầu không được ở quá khứ.',
    //                 'errors' => ['start_time' => ['Thời gian đã trôi qua.']]
    //             ], 422);
    //         }

    //         if ($fullEndTime->lessThanOrEqualTo($fullStartTime)) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
    //                 'errors' => ['end_time' => ['Dữ liệu thời gian không hợp lệ.']]
    //             ], 422);
    //         }

    //         // 3. Trích xuất dữ liệu để lưu vào DB
    //         $bookingDate = $fullStartTime->toDateString();
    //         $startTimeStr = $fullStartTime->toTimeString(); // HH:mm:ss
    //         $endTimeStr = $fullEndTime->toTimeString();     // HH:mm:ss

    //         // 4. Kiểm tra xung đột lịch (Giữ nguyên logic của bạn)
    //         $conflict = Booking::where('field_id', $fieldId)
    //             ->whereDate('booking_date', $bookingDate)
    //             ->where(function ($query) use ($startTimeStr, $endTimeStr) {
    //                 $query->where(function ($q) use ($startTimeStr, $endTimeStr) {
    //                     $q->where('start_time', '<', $endTimeStr)
    //                         ->where('end_time', '>', $startTimeStr);
    //                 });
    //             })
    //             ->whereIn('status', ['pending', 'confirmed', 'playing'])
    //             ->exists();

    //         if ($conflict) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Sân bóng đã được đặt trong khoảng thời gian này.',
    //                 'errors' => ['booking' => ['Trùng lịch sân.']]
    //             ], 422);
    //         }

    //         // 5. TÍNH TOÁN CHÍNH XÁC (SỬA LỖI GIÁ TRỊ ÂM)
    //         $field = Field::findOrFail($fieldId);

    //         // Sử dụng diffInMinutes để lấy số phút dương tuyệt đối giữa 2 mốc giờ
    //         $durationMinutes = $fullStartTime->diffInMinutes($fullEndTime);
    //         $durationHours = $durationMinutes / 60;

    //         // Logic giá: tăng 20% nếu sau 20:00 (Sử dụng đơn giá gốc của sân)
    //         $basePrice = $field->price;
    //         $finalPricePerHour = ($fullStartTime->hour >= 20) ? ($basePrice * 1.2) : $basePrice;

    //         // Tổng tiền = Đơn giá (đã tính phụ phí) * Số giờ đá
    //         $totalAmount = $finalPricePerHour * $durationHours;

    //         // 6. Tạo Booking với các giá trị đã chuẩn hóa
    //         $booking = $request->user()->bookings()->create([
    //             'field_id' => $fieldId,
    //             'booking_date' => $bookingDate,
    //             'start_time' => $startTimeStr,
    //             'end_time' => $endTimeStr,
    //             'duration' => $durationMinutes, // Lưu số phút đá (ví dụ: 90) để dễ thống kê
    //             'total_amount' => round($totalAmount), // Lưu số tiền dương (ví dụ: 1125000)
    //             'status' => 'pending',
    //             'customer_name' => $request->customer_name,
    //             'customer_phone' => $request->customer_phone,
    //             'notes' => $request->notes,
    //             // Các cột approved_by, confirmed_by sẽ mặc định là NULL khi mới tạo
    //         ]);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Đặt sân thành công!',
    //             'data' => $booking->load('field')
    //         ], 201);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Lỗi hệ thống: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    /**
     * Tạo một Booking mới (Sử dụng StoreBookingRequest).
     */
    // public function store(StoreBookingRequest $request): JsonResponse
    // {
    //     try {
    //         $fieldId = $request->field_id;
    //         $appTimezone = config('app.timezone', 'Asia/Ho_Chi_Minh');

    //         // 1. Ép kiểu và tạo đối tượng Carbon chính xác theo múi giờ hệ thống
    //         $fullStartTime = Carbon::parse($request->start_time, $appTimezone);
    //         $fullEndTime = Carbon::parse($request->end_time, $appTimezone);

    //         // 2. 🛑 CHẶN THỜI GIAN QUÁ KHỨ CHẶT CHẼ
    //         // Lấy thời gian hiện tại của Việt Nam
    //         $now = Carbon::now($appTimezone);

    //         // Kiểm tra: Nếu giờ bắt đầu nhỏ hơn giờ hiện tại (quá khứ)
    //         if ($fullStartTime->lessThan($now)) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Giờ này đã qua rồi bro ơi! Bây giờ đã là ' . $now->format('H:i') . ' ngày ' . $now->format('d/m/Y') . '.',
    //                 'errors' => ['start_time' => ['Thời gian bắt đầu không được ở quá khứ.']]
    //             ], 422);
    //         }

    //         // Kiểm tra logic: Giờ kết thúc phải sau giờ bắt đầu
    //         if ($fullEndTime->lessThanOrEqualTo($fullStartTime)) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
    //                 'errors' => ['end_time' => ['Dữ liệu thời gian không hợp lệ.']]
    //             ], 422);
    //         }

    //         // 3. Trích xuất dữ liệu để lưu vào DB
    //         $bookingDate = $fullStartTime->toDateString();
    //         $startTimeStr = $fullStartTime->toTimeString(); // HH:mm:ss
    //         $endTimeStr = $fullEndTime->toTimeString();     // HH:mm:ss

    //         // 4. Kiểm tra xung đột lịch (Giữ nguyên logic của bạn)
    //         $conflict = Booking::where('field_id', $fieldId)
    //             ->whereDate('booking_date', $bookingDate)
    //             ->where(function ($query) use ($startTimeStr, $endTimeStr) {
    //                 $query->where(function ($q) use ($startTimeStr, $endTimeStr) {
    //                     $q->where('start_time', '<', $endTimeStr)
    //                         ->where('end_time', '>', $startTimeStr);
    //                 });
    //             })
    //             ->whereIn('status', ['pending', 'confirmed', 'playing'])
    //             ->exists();

    //         if ($conflict) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Sân bóng đã được đặt trong khoảng thời gian này.',
    //                 'errors' => ['booking' => ['Trùng lịch sân.']]
    //             ], 422);
    //         }

    //         // 5. TÍNH TOÁN CHI PHÍ (Giữ nguyên logic của bạn)
    //         $field = Field::findOrFail($fieldId);
    //         $durationMinutes = $fullStartTime->diffInMinutes($fullEndTime);
    //         $durationHours = $durationMinutes / 60;

    //         $basePrice = $field->price;
    //         $finalPricePerHour = ($fullStartTime->hour >= 20) ? ($basePrice * 1.2) : $basePrice;
    //         $totalAmount = $finalPricePerHour * $durationHours;

    //         // 6. Tạo Booking (Dùng Transaction để an toàn nếu cần)
    //         $booking = $request->user()->bookings()->create([
    //             'field_id' => $fieldId,
    //             'booking_date' => $bookingDate,
    //             'start_time' => $startTimeStr,
    //             'end_time' => $endTimeStr,
    //             'duration' => $durationMinutes,
    //             'total_amount' => round($totalAmount),
    //             'status' => 'pending',
    //             'customer_name' => $request->customer_name,
    //             'customer_phone' => $request->customer_phone,
    //             'notes' => $request->notes,
    //         ]);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Đặt sân thành công rực rỡ!',
    //             'data' => $booking->load('field')
    //         ], 201);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Lỗi hệ thống: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

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

            // 5. TÍNH TOÁN CHI PHÍ
            $field = Field::findOrFail($fieldId);
            $durationMinutes = $fullStartTime->diffInMinutes($fullEndTime);
            $durationHours = $durationMinutes / 60;

            $basePrice = $field->price;
            $finalPricePerHour = ($fullStartTime->hour >= 20) ? ($basePrice * 1.2) : $basePrice;
            $totalAmount = $finalPricePerHour * $durationHours;

            // 6. Tạo Booking
            $booking = $request->user()->bookings()->create([
                'field_id' => $fieldId,
                'booking_date' => $bookingDate,
                'start_time' => $startTimeStr,
                'end_time' => $endTimeStr,
                'duration' => $durationMinutes,
                'total_amount' => round($totalAmount),
                'status' => 'pending',
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'notes' => $request->notes,
            ]);
            Notification::create([
                'type' => 'booking_new',
                'title' => 'LỊCH ĐẶT SÂN MỚI!',
                'message' => "Khách {$request->customer_name} vừa đặt sân vào lúc " . Carbon::parse($request->start_time)->format('H:i d/m'),
                'link' => '/staff/bookings', // Đường dẫn trang quản lý đặt sân ở frontend
                'is_read' => false
            ]);

            // 🛑 LOGIC ĐỒNG BỘ SANG BẢNG CUSTOMERS RỰC RỠ
            // Vì Booking có thể không có Email, ta sẽ dùng Phone làm khóa định danh
            // Nếu bro muốn dùng Email, hãy truyền thêm email từ request
            if ($request->customer_phone) {
                $customer = Customer::updateOrCreate(
                    ['phone' => $request->customer_phone], // Tìm theo số điện thoại
                    [
                        'name'   => $request->customer_name,
                        'email'  => $request->email ?? $request->customer_phone . '@guest.com', // Tạo email giả nếu thiếu
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
                'message' => 'Đặt sân thành công rực rỡ!',
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
     * Lấy chi tiết một booking.
     */
    public function show($id): JsonResponse // Đổi Booking $booking thành $id để query tươi mới hoàn toàn
    {
        // 🛑 DÙNG TRUY VẤN TƯƠI ĐỂ ÉP NẠP FIELD
        $booking = Booking::with(['field', 'user.profile'])->find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy hóa đơn ID: ' . $id
            ], 404);
        }

        $user = request()->user();
        // Logic kiểm tra quyền của bro (giữ nguyên)
        if (!$user->isAdmin() && !$user->isStaff() && $booking->user_id !== $user->id) {
            return response()->json(['message' => 'Bạn không có quyền xem đơn này.'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $booking // Trả về object đã được nạp Field rực rỡ
        ]);
    }

    // public function getSchedule(Field $field, Request $request): JsonResponse
    // {
    //     $request->validate(['date' => 'required|date_format:Y-m-d']);
    //     $date = $request->date;
    //     $basePrice = $field->price; // Lấy đúng giá của sân đang chọn

    //     $schedule = [];
    //     $startTimeMinutes = 8 * 60; // 08:00
    //     $endTimeMinutes = 23 * 60;  // 23:00
    //     $slotDuration = 90;

    //     for ($time = $startTimeMinutes; $time < $endTimeMinutes; $time += $slotDuration) {
    //         $start = sprintf('%02d:%02d', floor($time / 60), $time % 60);
    //         $end = sprintf('%02d:%02d', floor(($time + $slotDuration) / 60), ($time + $slotDuration) % 60);

    //         // NGHIỆP VỤ GIÁ:
    //         $currentPrice = $basePrice;

    //         // Nếu khung giờ bắt đầu từ 20:00 trở đi, cộng thêm phụ phí đêm (ví dụ +20%)
    //         if ($time >= 20 * 60) {
    //             $currentPrice = $basePrice * 1.2;
    //         }

    //         $isBooked = Booking::where('field_id', $field->id)
    //             ->where('booking_date', $date)
    //             ->where('start_time', $start)
    //             ->whereIn('status', ['pending', 'confirmed', 'playing'])
    //             ->exists();

    //         $schedule[] = [
    //             'start_time' => $start,
    //             'end_time' => $end,
    //             'price' => round($currentPrice), // Giá đã tính theo từng sân & khung giờ
    //             'status' => $isBooked ? 'booked' : 'available',
    //         ];
    //     }

    //     return response()->json(['success' => true, 'data' => $schedule]);
    // }
    public function getSchedule(Field $field, Request $request): JsonResponse
    {
        $request->validate(['date' => 'required|date_format:Y-m-d']);
        $date = $request->date;
        $basePrice = $field->price;

        $schedule = [];
        $startTimeMinutes = 8 * 60; // 08:00
        $endTimeMinutes = 23 * 60;  // 23:00
        $slotDuration = 90;

        for ($time = $startTimeMinutes; $time < $endTimeMinutes; $time += $slotDuration) {
            // Tạo chuỗi thời gian chuẩn MySQL (HH:mm:ss)
            $startWithSeconds = sprintf('%02d:%02d:00', floor($time / 60), $time % 60);
            $endWithSeconds = sprintf('%02d:%02d:00', floor(($time + $slotDuration) / 60), ($time + $slotDuration) % 60);

            $startDisplay = substr($startWithSeconds, 0, 5);
            $endDisplay = substr($endWithSeconds, 0, 5);

            // 🛑 LOGIC QUÉT ĐƠN: Đã loại bỏ 'completed' nếu bro muốn ca đá xong là trống ngay
            $isBooked = Booking::where('field_id', $field->id)
                ->whereDate('booking_date', $date)
                ->where(function ($query) use ($startWithSeconds, $endWithSeconds) {
                    $query->where('start_time', '<', $endWithSeconds)
                        ->where('end_time', '>', $startWithSeconds);
                })
                // Chỉ tính những đơn thực sự đang chiếm sân
                // Nếu bro muốn đá xong (completed) là sân trống thì xóa 'completed' khỏi mảng dưới đây
                ->whereIn('status', ['pending', 'approved', 'confirmed', 'playing'])
                ->exists();

            $schedule[] = [
                'start_time' => $startDisplay,
                'end_time' => $endDisplay,
                'price' => round(($time >= 20 * 60) ? $basePrice * 1.2 : $basePrice),
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
}
