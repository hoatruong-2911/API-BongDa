<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreFieldRequest;
use App\Models\Booking;
use App\Models\Field; // ⬅️ THÊM DÒNG NÀY
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse; // ⬅️ THÊM DÒNG NÀY
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class FieldController extends Controller
{

    // Hàm hỗ trợ xử lý Base64 thành file ảnh
    private function saveBase64Image($base64String)
    {
        if (!$base64String || !Str::startsWith($base64String, 'data:image')) {
            return $base64String; // Trả về nếu nó đã là link hoặc null
        }

        // Tách phần đầu (data:image/png;base64,) và phần dữ liệu
        $format = explode('/', explode(':', substr($base64String, 0, strpos($base64String, ';')))[1])[1];
        $image = str_replace('data:image/' . $format . ';base64,', '', $base64String);
        $image = str_replace(' ', '+', $image);

        // Tạo tên file ngẫu nhiên
        $imageName = 'field_' . time() . '_' . Str::random(10) . '.' . $format;

        // Lưu vào thư mục public/uploads/fields
        $path = public_path('uploads/fields');
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0777, true, true);
        }

        File::put($path . '/' . $imageName, base64_decode($image));

        // Trả về đường dẫn để lưu vào DB (ví dụ: uploads/fields/abc.jpg)
        return 'uploads/fields/' . $imageName;
    }
    /**
     * Lấy danh sách các sân bóng đang hoạt động (Customer/Staff/Admin).
     */
    public function index(Request $request): JsonResponse
    {
        // Bỏ lọc 'available' = true để Admin thấy được tất cả 11 sân
        $query = Field::query();

        // 1. Tùy chọn: Lọc theo loại sân (ví dụ: f5, f7)
        if ($request->has('type') && $request->type != '') {
            $query->where('type', $request->input('type'));
        }

        // 2. Tùy chọn: Lọc theo trạng thái hoạt động (chỉ khi có yêu cầu cụ thể)
        if ($request->has('is_active')) {
            $query->where('available', $request->boolean('is_active'));
        }

        // 3. Tùy chọn: Sắp xếp theo giá hoặc rating
        $sort = $request->input('sort_by', 'created_at'); // Mặc định sân mới nhất lên đầu
        $order = $request->input('order', 'desc');

        // Phân trang: Laravel trả về object phân trang kèm theo metadata
        // THAY paginate(10) THÀNH get()
        $fields = $query->orderBy($sort, $order)->get();

        return response()->json([
            'success' => true,
            'data' => $fields
        ]);
    }

    /**
     * Lấy chi tiết một sân bóng.
     * (Route này nằm trong nhóm auth:sanctum và role:customer, staff, admin)
     */
    public function show(Field $field): JsonResponse
    {
        // Khách hàng cần thấy sân đang hoạt động
        if (!$field->available && request()->user()?->role === 'customer') {
            return response()->json([
                'message' => 'Sân bóng không tồn tại hoặc tạm ngưng hoạt động.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $field
        ]);
    }

    public function getSchedule(Field $field, Request $request): JsonResponse
    {
        // 🛑 FIX: Thêm Validation cho Request
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $date = $request->date;
        $price = $field->price; // Lấy giá cơ bản từ model

        // Tạo lịch trống mẫu (giả định sân hoạt động từ 8h sáng đến 22h tối, mỗi ca 90 phút)
        $schedule = [];
        $startTime = 8 * 60; // 8:00 AM in minutes
        $endTime = 22 * 60;  // 10:00 PM in minutes
        $slotDuration = 90; // 90 minutes per slot

        for ($time = $startTime; $time < $endTime; $time += $slotDuration) {
            $startHour = floor($time / 60);
            $startMin = $time % 60;
            $endHour = floor(($time + $slotDuration) / 60);
            $endMin = ($time + $slotDuration) % 60;

            $start = sprintf('%02d:%02d', $startHour, $startMin);
            $end = sprintf('%02d:%02d', $endHour, $endMin);

            // Logic giả lập: Ca bắt đầu từ 18:00 (1080 phút) đến 20:00 sẽ bận (booked)
            $timeInMinutes = ($startHour * 60) + $startMin;

            $status = ($timeInMinutes >= 1080 && $timeInMinutes < 1200) ? 'booked' : 'available';

            $slotPrice = $price;
            // Tăng giá 20% cho giờ cao điểm (18:00 - 22:00)
            if ($timeInMinutes >= 1080) {
                $slotPrice = $price * 1.2;
            }

            $schedule[] = [
                'start_time' => $start,
                'end_time' => $end,
                'price' => round($slotPrice),
                'status' => $status,
            ];
        }

        return response()->json([
            'success' => true,
            'date' => $date,
            'data' => $schedule // Trả về mảng lịch trống
        ]);
    }

    public function store(StoreFieldRequest $request): JsonResponse
    {
        // Khi bạn thay 'Request $request' thành 'StoreFieldRequest $request'
        // Laravel sẽ tự động chạy các rules validate trước khi nhảy vào hàm này.

        try {
            // Lấy dữ liệu đã được xác thực thành công
            $validated = $request->validated();
            // Xử lý ảnh: Chuyển Base64 thành file vật lý
            $imagePath = $this->saveBase64Image($request->image);

            // Tạo sân mới
            $field = Field::create([
                'name'          => $validated['name'],
                'slug'          => Str::slug($validated['name']),
                'type'          => $validated['type'],
                'price'         => $validated['price'],
                'size'          => $validated['size'],
                'surface'       => $validated['surface'] ?? 'Cỏ nhân tạo',
                'description'   => $validated['description'] ?? null,
                'location'      => $validated['location'],
                'image'         => $imagePath, // Lưu đường dẫn file (vd: uploads/fields/abc.png)
                // Sử dụng $request->boolean để ép kiểu đúng cho Switch từ Frontend
                'features'      => $request->input('features', []),
                'available'     => $request->boolean('available', true),
                'is_vip'        => $request->boolean('is_vip', false),
                'rating'        => 5.0,
                'reviews_count' => 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Thêm sân bóng thành công!',
                'data'    => $field
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }
    // ... Các phương thức store, update, destroy sẽ làm ở bước Admin

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $field = Field::findOrFail($id);

            $imagePath = $this->saveBase64Image($request->image);
            // Bạn có thể validate dữ liệu ở đây hoặc dùng StoreFieldRequest tương tự hàm store
            $field->update([
                'name'        => $request->name,
                'slug'        => Str::slug($request->name), // ✅ Thêm cập nhật slug ở đây
                'type'        => $request->type,
                'price'       => $request->price,
                'size'        => $request->size,
                'surface'     => $request->surface,
                'description' => $request->description,
                'location'    => $request->location,
                'image'       => $imagePath,
                'features'    => $request->input('features', []),
                'available'   => $request->boolean('available'),
                'is_vip'      => $request->boolean('is_vip'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật sân bóng thành công!',
                'data'    => $field
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }
    public function destroy(string $id): JsonResponse
    {
        try {
            $field = Field::findOrFail($id);

            // 1. Lấy đường dẫn file ảnh từ Database
            // Lưu ý: Lấy giá trị gốc trong DB, không lấy qua Accessor (vì Accessor đã nối thêm domain)
            $imagePath = $field->getRawOriginal('image');

            // 2. Xóa file ảnh vật lý nếu tồn tại
            if ($imagePath && File::exists(public_path($imagePath))) {
                File::delete(public_path($imagePath));
            }

            // 3. Xóa bản ghi trong Database
            $field->delete();

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa sân bóng và ảnh liên quan thành công!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getLiveStatus(Request $request): JsonResponse
    {
        $date = $request->query('date', now()->toDateString());
        $fields = \App\Models\Field::all();

        // Chỉ lấy những đơn đang chiếm chỗ trên sân
        // Loại bỏ: 'cancelled' (Hủy) và 'completed' (Đã đá xong)
        $bookings = \App\Models\Booking::with('field')
            ->whereDate('booking_date', $date)
            ->whereNotIn('status', ['cancelled', 'completed', 'rejected'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'fields' => $fields,
                'bookings' => $bookings
            ]
        ]);
    }

   
}
