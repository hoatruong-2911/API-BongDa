<?php

namespace App\Http\Controllers\Api;

use App\Models\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Http\Requests\Api\Oder\StoreOrderRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Product; // Nhớ import Model Product


class OrderController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */ // 🛑 THÊM DÒNG NÀY ĐỂ MÁY HIỂU BIẾN $user CÓ HÀM isAdmin
        $user = auth('sanctum')->user(); // Lấy cả object user để check role

        // 1. Nếu là Admin hoặc Staff: Lấy TẤT CẢ đơn hàng của hệ thống
        if ($user->isAdmin() || $user->isStaff()) {
            $orders = Order::with(['items', 'user']) // Load thêm thông tin user để biết đơn của ai
                ->orderBy('created_at', 'desc')
                ->paginate(15); // Nên dùng paginate cho chuyên nghiệp
        } else {
            // 2. Nếu là Khách hàng: CHỈ lấy đơn của chính mình
            $orders = Order::with('items')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }

    public function indexAdmin(Request $request): JsonResponse
    {
        // Load cả items và user để Admin biết đơn của ai
        $orders = Order::with(['items', 'user'])
            ->orderBy('created_at', 'desc')
            ->get(); // Nếu muốn dùng paginate thì đổi thành ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }

    /**
     * 🛑 DÀNH CHO ADMIN: Cập nhật trạng thái (Duyệt, Chuẩn bị, Hủy...)
     */
    // public function updateStatus(Request $request, $id): JsonResponse
    // {
    //     $request->validate([
    //         'status' => 'required|in:pending,confirmed,paid,preparing,completed,cancelled'
    //     ]);

    //     $order = Order::findOrFail($id);
    //     $order->status = $request->status;
    //     $order->save();

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Đã chuyển trạng thái đơn hàng sang ' . strtoupper($request->status) . ' rực rỡ!'
    //     ]);
    // }
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,paid,preparing,completed,cancelled'
        ]);

        return DB::transaction(function () use ($request, $id) {
            $order = Order::with('items')->findOrFail($id);
            $oldStatus = $order->status;
            $newStatus = $request->status;

            // 🛑 LOGIC TỰ ĐỘNG HOÀN KHO KHI HỦY ĐƠN
            // Nếu trạng thái cũ chưa phải là cancelled, mà trạng thái mới là cancelled
            if ($oldStatus !== 'cancelled' && $newStatus === 'cancelled') {
                foreach ($order->items as $item) {
                    Product::where('id', $item->product_id)->increment('stock', $item->quantity);
                }
            }

            $order->status = $newStatus;
            $order->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Đã chuyển trạng thái đơn hàng sang ' . strtoupper($newStatus) . ' rực rỡ!'
            ]);
        });
    }


    // public function store(StoreOrderRequest $request): JsonResponse
    // {
    //     return DB::transaction(function () use ($request) {
    //         $userId = auth('sanctum')->id();

    //         // 🛑 LOGIC PHÂN LUỒNG TRẠNG THÁI DỰA TRÊN THANH TOÁN
    //         // Nếu chọn QR thì coi như đã trả (hoặc đợi Webhook update), nếu chọn CASH thì để PENDING
    //         $initialStatus = ($request->payment_method === 'qr') ? 'paid' : 'pending';

    //         // 1. Tạo đơn hàng tổng
    //         $order = Order::create([
    //             'order_code'     => $request->order_code,
    //             'user_id'        => $userId,
    //             'customer_name'  => $request->customer_name,
    //             'phone'          => $request->phone,
    //             'email'          => $request->email,
    //             'total_amount'   => $request->total_amount,
    //             'payment_method' => $request->payment_method, // 'qr' hoặc 'cash'
    //             'status'         => $initialStatus,
    //             'order_type'     => 'online',
    //             'notes'          => $request->notes,
    //             'pickup_address' => 'Sân bóng Thanh Hóa Soccer, Ninh Thuận',
    //         ]);

    //         // 2. Lưu chi tiết & Trừ kho
    //         foreach ($request->items as $item) {
    //             $product = Product::find($item['id']);

    //             if (!$product || $product->stock < $item['quantity']) {
    //                 throw new \Exception("Sản phẩm {$item['name']} đã hết hàng hoặc không đủ số lượng!");
    //             }

    //             $product->decrement('stock', $item['quantity']);

    //             OrderItem::create([
    //                 'order_id'     => $order->id,
    //                 'product_id'   => $item['id'],
    //                 'product_name' => $item['name'],
    //                 'image'        => $item['image'] ?? null,
    //                 'unit'         => $item['unit'] ?? 'món',
    //                 'quantity'     => $item['quantity'],
    //                 'price'        => $item['price'],
    //                 'subtotal'     => $item['price'] * $item['quantity'],
    //             ]);
    //         }

    //         return response()->json([
    //             'status'  => 'success',
    //             'message' => 'Đã gửi đơn hàng rực rỡ! 🏆',
    //             'data'    => $order->load('items')
    //         ], 201);
    //     });
    // }
    public function store(StoreOrderRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $userId = auth('sanctum')->id();

            // 🛑 LOGIC PHÂN LUỒNG TRẠNG THÁI DỰA TRÊN THANH TOÁN
            $initialStatus = ($request->payment_method === 'qr') ? 'paid' : 'pending';

            // 1. Tạo đơn hàng tổng
            $order = Order::create([
                'order_code'     => $request->order_code,
                'user_id'        => $userId,
                'customer_name'  => $request->customer_name,
                'phone'          => $request->phone,
                'email'          => $request->email,
                'total_amount'   => $request->total_amount,
                'payment_method' => $request->payment_method, // 'qr' hoặc 'cash'
                'status'         => $initialStatus,
                'order_type'     => 'online',
                'notes'          => $request->notes,
                'pickup_address' => 'Sân bóng Thanh Hóa Soccer, Ninh Thuận',
            ]);

            // 🛑 LOGIC ĐỒNG BỘ SANG BẢNG CUSTOMERS RỰC RỠ
            // Tìm theo email, nếu không thấy thì tạo mới, thấy rồi thì lấy ra để update
            $customer = \App\Models\Customer::updateOrCreate(
                ['email' => $request->email], // Điều kiện tìm
                [
                    'name'  => $request->customer_name,
                    'phone' => $request->phone,
                    'status' => 'active',
                ]
            );

            // Cộng dồn chỉ số ngay khi đặt đơn
            $customer->increment('total_bookings'); // +1 lượt đặt
            $customer->total_spent += $request->total_amount; // Cộng dồn tiền tiêu
            $customer->last_booking = now(); // Cập nhật ngày đặt mới nhất

            // Logic tự động lên VIP nếu tiêu trên 5.000.000đ (tùy bro chỉnh số này)
            if ($customer->total_spent >= 5000000) {
                $customer->is_vip = true;
            }

            $customer->save();

            // 2. Lưu chi tiết & Trừ kho
            foreach ($request->items as $item) {
                $product = Product::find($item['id']);

                if (!$product || $product->stock < $item['quantity']) {
                    throw new \Exception("Sản phẩm {$item['name']} đã hết hàng hoặc không đủ số lượng!");
                }

                $product->decrement('stock', $item['quantity']);

                OrderItem::create([
                    'order_id'     => $order->id,
                    'product_id'   => $item['id'],
                    'product_name' => $item['name'],
                    'image'        => $item['image'] ?? null,
                    'unit'         => $item['unit'] ?? 'món',
                    'quantity'     => $item['quantity'],
                    'price'        => $item['price'],
                    'subtotal'     => $item['price'] * $item['quantity'],
                ]);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Đã gửi đơn hàng rực rỡ! 🏆',
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


    public function show(string $orderCode): JsonResponse
    {
        /** @var \App\Models\User $user */ // 🛑 THÊM DÒNG NÀY ĐỂ MÁY HIỂU BIẾN $user CÓ HÀM isAdmin
        $user = auth('sanctum')->user();

        // Tìm đơn hàng kèm sản phẩm
        $order = Order::with(['items'])->where('order_code', $orderCode)->first();

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy đơn hàng!'
            ], 404);
        }

        // 🛑 KIỂM TRA QUYỀN XEM:
        // Nếu KHÔNG PHẢI admin/staff VÀ đơn hàng KHÔNG THUỘC về user này thì chặn
        if (!$user->isAdmin() && !$user->isStaff() && $order->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bro không có quyền xem cực phẩm của người khác!'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $order
        ]);
    }

    public function showAdmin($id): \Illuminate\Http\JsonResponse
    {
        // Tìm đơn hàng theo ID (không phải Order Code nhé) kèm theo các items
        $order = Order::with(['items'])->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đơn hàng rực rỡ này!'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        try {
            $order = Order::with('items')->find($id);

            if (!$order) {
                return response()->json(['status' => 'error', 'message' => 'Không tìm thấy đơn hàng!'], 404);
            }

            return DB::transaction(function () use ($order) {
                // 🛑 HOÀN KHO TRƯỚC KHI XÓA (Chỉ hoàn nếu đơn chưa hoàn thành hoặc chưa hủy)
                if (!in_array($order->status, ['completed', 'cancelled'])) {
                    foreach ($order->items as $item) {
                        Product::where('id', $item->product_id)->increment('stock', $item->quantity);
                    }
                }

                $order->items()->delete();
                $order->delete();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Đã xóa đơn hàng và hoàn lại kho rực rỡ! 🗑️'
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }


    public function update(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        return DB::transaction(function () use ($request, $id) {
            // Load đơn kèm items để xử lý kho
            $order = Order::with('items')->findOrFail($id);

            // 🛑 1. CẬP NHẬT THÔNG TIN CƠ BẢN & TRẠNG THÁI
            $order->update([
                'customer_name' => $request->customer_name,
                'phone'         => $request->phone,
                'notes'         => $request->notes,
                'status'        => $request->status, // Thêm dòng này để cập nhật trạng thái
                'total_amount'  => $request->total_amount,
            ]);

            // 🛑 2. LOGIC CẬP NHẬT SẢN PHẨM & HOÀN KHO (Sạch sẽ rực rỡ)
            if ($request->has('items')) {
                // Hoàn lại kho cũ
                foreach ($order->items as $oldItem) {
                    Product::where('id', $oldItem->product_id)->increment('stock', $oldItem->quantity);
                }

                // Xóa items cũ để thay bằng list mới
                $order->items()->delete();

                foreach ($request->items as $item) {
                    $product = Product::findOrFail($item['product_id']);

                    // Kiểm tra kho mới
                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("Sản phẩm {$product->name} không đủ số lượng trong kho!");
                    }

                    $product->decrement('stock', $item['quantity']);

                    OrderItem::create([
                        'order_id'     => $order->id,
                        'product_id'   => $item['product_id'],
                        'product_name' => $product->name,
                        'price'        => $item['price'],
                        'quantity'     => $item['quantity'],
                        'subtotal'     => $item['price'] * $item['quantity'],
                        'image'        => $product->image,
                        'unit'         => $product->unit ?? 'món'
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật đơn hàng cực phẩm thành công!',
                'data' => $order->load('items')
            ]);
        });
    }
}
