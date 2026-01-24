<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User; // 🛑 Dùng Model User vì khách hàng là User
use App\Models\Order; // Để tính tổng chi tiêu
use App\Models\Booking; // Để tính tổng lần đặt sân
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query(); // 🛑 Dùng đúng Model Customer

        // 1. Tìm kiếm
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }

        // 2. Lọc trạng thái
        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $customers = $query->latest()->get();

        // 3. Thống kê (Lấy từ bảng customers)
        $stats = [
            'totalCustomers'  => Customer::count(),
            'activeCustomers' => Customer::where('status', 'active')->count(),
            'vipCustomers'    => Customer::where('total_spent', '>=', 5000000)->count(),
            'totalBookings'   => Customer::sum('total_bookings'),
        ];

        return response()->json([
            'success' => true,
            'data'    => $customers,
            'stats'   => $stats
        ]);
    }

    public function destroy($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Khách không tồn tại!'], 404);
        }

        // 🛑 TÌM DỮ LIỆU THAM CHIẾU
        $relatedOrders = Order::where('email', $customer->email)
            ->orWhere('phone', $customer->phone)
            ->get(['id', 'order_code', 'status']);

        $relatedBookings = \App\Models\Booking::where('customer_phone', $customer->phone)
            ->get(['id', 'booking_date', 'status']);

        // Nếu còn dính líu, trả về 422 kèm debug_info
        if ($relatedOrders->isNotEmpty() || $relatedBookings->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa vì còn dữ liệu tham chiếu!',
                'debug_info' => [
                    'orders' => $relatedOrders,
                    'bookings' => $relatedBookings
                ]
            ], 422);
        }

        $customer->delete();
        return response()->json(['success' => true, 'message' => 'Xóa thành công!']);
    }

    public function show($id)
{
    try {
        // 1. Tìm khách hàng
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'success' => false, 
                'message' => 'Khách hàng này không tồn tại bro ơi!'
            ], 404);
        }

        // 2. Lấy lịch sử đặt sân (Kiểm tra kỹ tên cột customer_phone trong DB của bro)
        $bookings = Booking::where('customer_phone', $customer->phone)
                    ->orderBy('created_at', 'desc')
                    ->get();

        // 3. Lấy thêm lịch sử mua hàng (nếu bro muốn làm Tab 2)
        $orders = \App\Models\Order::where('phone', $customer->phone)
                    ->orWhere('email', $customer->email)
                    ->orderBy('created_at', 'desc')
                    ->get();

        return response()->json([
            'success' => true,
            'data'    => $customer,
            'bookings' => $bookings,
            'orders'   => $orders // Trả về luôn cho rực rỡ
        ]);
    } catch (\Exception $e) {
        // Nếu có lỗi, nó sẽ báo rõ lỗi gì thay vì chỉ hiện 500 chung chung
        return response()->json([
            'success' => false,
            'message' => 'Lỗi hệ thống: ' . $e->getMessage()
        ], 500);
    }
}
}
