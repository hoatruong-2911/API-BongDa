<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Http\Requests\Api\Oder\StoreOrderRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Lấy danh sách lịch sử đơn hàng của chính User đang đăng nhập
     */
    public function index(): JsonResponse
    {
        $userId = auth('sanctum')->id();

        $orders = Order::with('items') // 🛑 QUAN TRỌNG: Thêm with('items') để hiện sản phẩm ở bảng ngoài
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }
    /**
     * 🛑 HÀM LƯU ĐƠN CHÍNH THỨC: Gọi sau khi đã xác nhận có tiền về túi
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            // Lấy ID người dùng hiện tại (bắt buộc phải đăng nhập)
            $userId = auth('sanctum')->id();

            // 1. Tạo đơn hàng với trạng thái 'paid'
            $order = Order::create([
                'order_code'     => $request->order_code,
                'user_id'        => $userId, // 🛑 Gắn ID tài khoản đang đăng nhập
                'customer_name'  => $request->customer_name,
                'phone'          => $request->phone,
                'email'          => $request->email,
                'total_amount'   => $request->total_amount,
                'payment_method' => $request->payment_method,
                'status'         => 'paid', // Tiền đã vào túi mới gọi hàm này
                'order_type'     => 'online',
                'notes'          => $request->notes,
                'pickup_address' => 'Sân bóng Thanh Hóa Soccer, Văn Lâm 3, Phước Nam, Ninh Thuận',
            ]);

            // 2. Lưu chi tiết sản phẩm
            foreach ($request->items as $item) {
                OrderItem::create([
                    'order_id'     => $order->id,
                    'product_id'   => $item['id'],
                    'product_name' => $item['name'],
                    'unit'         => $item['unit'] ?? 'món',
                    'quantity'     => $item['quantity'],
                    'price'        => $item['price'],
                    'subtotal'     => $item['price'] * $item['quantity'],
                ]);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Đã chốt đơn cực phẩm thành công! 🏆',
                'data'    => $order->load('items')
            ], 201);
        });
    }

    /**
     * 🛑 HÀM KIỂM TRA BIẾN ĐỘNG SỐ DƯ: Frontend sẽ gọi liên tục (Polling)
     * Hàm này kiểm tra tiền thật từ ngân hàng chứ không kiểm tra trong bảng orders
     */
    // Thêm Request vào tham số để lấy total_amount từ Frontend gửi lên
    public function checkStatus(Request $request, string $orderCode): JsonResponse
    {
        // Kiểm tra tiền thật trong bảng bank_transactions
        $paymentReceived = DB::table('bank_transactions')
            ->where('description', 'like', "%$orderCode%")
            ->where('amount', '>=', $request->query('total_amount'))
            ->exists();

        return response()->json([
            'order_code' => $orderCode,
            'status'     => $paymentReceived ? 'paid' : 'pending'
        ]);
    }

    /**
     * Lấy chi tiết một đơn hàng cụ thể theo mã đơn
     */
    public function show(string $orderCode): JsonResponse
    {
        // Lấy ID user đang đăng nhập để bảo vệ dữ liệu
        $userId = auth('sanctum')->id();

        // Tìm đơn hàng khớp mã và thuộc về đúng chủ nhân
        $order = Order::with(['items']) // 🛑 Load các sản phẩm chi tiết của đơn này
            ->where('order_code', $orderCode)
            ->where('user_id', $userId)
            ->first();

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy đơn hàng hoặc bro không có quyền xem cực phẩm này!'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $order
        ]);
    }
}
