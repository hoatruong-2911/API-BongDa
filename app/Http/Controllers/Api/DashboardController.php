<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Order;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;


class DashboardController extends Controller
{
    // public function index()
    // {
    //     try {
    //         $today = Carbon::today();
    //         $currentYear = date('Y');

    //         // 1. Thống kê 4 số liệu đầu trang (Chỉ tính đơn đã hoàn thành cho Doanh thu)
    //         $dailyBookingRevenue = (float)Booking::whereDate('booking_date', $today)->where('status', 'completed')->sum('total_amount');
    //         $dailyOrderRevenue = (float)Order::whereDate('created_at', $today)->where('status', 'completed')->sum('total_amount');

    //         $stats = [
    //             'revenue' => $dailyBookingRevenue + $dailyOrderRevenue,
    //             'revenueGrowth' => 12.5,
    //             'bookings' => Booking::whereDate('booking_date', $today)->count(),
    //             'bookingsGrowth' => 8.2,
    //             'customers' => Customer::count(),
    //             'newCustomers' => Customer::whereDate('created_at', $today)->count(),
    //             'products' => (int)Order::whereDate('created_at', $today)->count(),
    //             'productsGrowth' => 15.3,
    //         ];

    //         // 2. Doanh thu theo tháng (Tổng hợp Sân + Sản phẩm - Chỉ 'completed')
    //         $revenueData = [];
    //         for ($i = 1; $i <= 12; $i++) {
    //             $monthBookingRev = (float)Booking::whereMonth('booking_date', $i)
    //                 ->whereYear('booking_date', $currentYear)
    //                 ->where('status', 'completed')
    //                 ->sum('total_amount');

    //             $monthOrderRev = (float)Order::whereMonth('created_at', $i)
    //                 ->whereYear('created_at', $currentYear)
    //                 ->where('status', 'completed')
    //                 ->sum('total_amount');

    //             $totalMonth = $monthBookingRev + $monthOrderRev;

    //             $revenueData[] = [
    //                 'name' => "T$i",
    //                 'revenue' => (int)$totalMonth,
    //                 'bookings' => Booking::whereMonth('booking_date', $i)->whereYear('booking_date', $currentYear)->count()
    //             ];
    //         }

    //         // Tính tổng doanh thu cả năm rực rỡ cho dòng tổng cộng
    //         $yearlyTotal = array_sum(array_column($revenueData, 'revenue'));

    //         // 3. Tỷ lệ sử dụng sân (PieChart)
    //         $fieldUsage = Booking::select('fields.name', DB::raw('count(*) as value'))
    //             ->join('fields', 'bookings.field_id', '=', 'fields.id')
    //             ->groupBy('fields.name')
    //             ->get()
    //             ->map(function ($item, $index) {
    //                 $colors = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'];
    //                 return [
    //                     'name' => $item->name,
    //                     'value' => (int)$item->value,
    //                     'color' => $colors[$index % count($colors)]
    //                 ];
    //             });

    //         // 4. Giao dịch đặt sân mới nhất
    //         $recentBookings = Booking::with('field')
    //             ->orderBy('created_at', 'desc')
    //             ->limit(5)
    //             ->get()
    //             ->map(function ($b) {
    //                 return [
    //                     'id' => $b->id,
    //                     'booking_code' => $b->booking_code,
    //                     'customer_name' => $b->customer_name,
    //                     'field_name' => $b->field->name ?? 'N/A',
    //                     'time_slot' => Carbon::parse($b->start_time)->format('H:i') . ' - ' . Carbon::parse($b->end_time)->format('H:i'),
    //                     'total_amount' => (float)$b->total_amount,
    //                     'status' => $b->status,
    //                 ];
    //             });

    //         // 5. Giao dịch sản phẩm mới nhất
    //         $recentOrders = Order::orderBy('created_at', 'desc')
    //             ->limit(5)
    //             ->get()
    //             ->map(function ($o) {
    //                 return [
    //                     'id' => $o->id,
    //                     'order_code' => $o->order_code,
    //                     'customer_name' => $o->name ?? ($o->user->name ?? 'Khách vãng lai'),
    //                     'total_amount' => (float)$o->total_amount,
    //                     'status' => $o->status,
    //                     'created_at' => $o->created_at->format('d/m/Y H:i'),
    //                 ];
    //             });

    //         return response()->json([
    //             'success' => true,
    //             'stats' => $stats,
    //             'revenueData' => $revenueData,
    //             'fieldUsage' => $fieldUsage,
    //             'recentBookings' => $recentBookings,
    //             'recentOrders' => $recentOrders,
    //             'yearlyTotal' => (float)$yearlyTotal
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    //     }
    // }

    public function index()
    {
        try {
            $today = Carbon::today();
            $currentYear = date('Y');

            // 🛑 LẤY MỐC THỜI GIAN TUẦN NÀY (Thứ 2 -> Chủ Nhật)
            $startOfWeek = Carbon::now()->startOfWeek();
            $endOfWeek = Carbon::now()->endOfWeek();

            // 1. Thống kê số liệu THEO TUẦN (Chỉ tính đơn đã hoàn thành cho Doanh thu)
            // Tổng doanh thu đặt sân trong tuần
            $weeklyBookingRevenue = (float)Booking::whereBetween('booking_date', [$startOfWeek, $endOfWeek])
                ->where('status', 'completed')
                ->sum('total_amount');

            // Tổng doanh thu bán hàng trong tuần
            $weeklyOrderRevenue = (float)Order::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->where('status', 'completed')
                ->sum('total_amount');

            $stats = [
                'revenue' => $weeklyBookingRevenue + $weeklyOrderRevenue, // Tổng doanh thu tuần này
                'revenueGrowth' => 12.5, // Có thể tính toán so với tuần trước nếu muốn
                'bookings' => Booking::whereBetween('booking_date', [$startOfWeek, $endOfWeek])->count(), // Tổng lượt đặt trong tuần
                'bookingsGrowth' => 8.2,
                'customers' => Customer::count(),
                'newCustomers' => Customer::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count(), // Khách mới trong tuần
                'products' => (int)Order::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count(), // Đơn hàng mới trong tuần
                'productsGrowth' => 15.3,
            ];

            // 2. Doanh thu theo tháng (Giữ nguyên logic 12 tháng rực rỡ)
            $revenueData = [];
            for ($i = 1; $i <= 12; $i++) {
                $monthBookingRev = (float)Booking::whereMonth('booking_date', $i)
                    ->whereYear('booking_date', $currentYear)
                    ->where('status', 'completed')
                    ->sum('total_amount');

                $monthOrderRev = (float)Order::whereMonth('created_at', $i)
                    ->whereYear('created_at', $currentYear)
                    ->where('status', 'completed')
                    ->sum('total_amount');

                $totalMonth = $monthBookingRev + $monthOrderRev;

                $revenueData[] = [
                    'name' => "T$i",
                    'revenue' => (int)$totalMonth,
                    'bookings' => Booking::whereMonth('booking_date', $i)
                        ->whereYear('booking_date', $currentYear)
                        ->count()
                ];
            }

            // Tính tổng doanh thu cả năm rực rỡ cho dòng tổng cộng
            $yearlyTotal = array_sum(array_column($revenueData, 'revenue'));

            // 3. Tỷ lệ sử dụng sân (PieChart - Giữ nguyên)
            $fieldUsage = Booking::select('fields.name', DB::raw('count(*) as value'))
                ->join('fields', 'bookings.field_id', '=', 'fields.id')
                ->groupBy('fields.name')
                ->get()
                ->map(function ($item, $index) {
                    $colors = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'];
                    return [
                        'name' => $item->name,
                        'value' => (int)$item->value,
                        'color' => $colors[$index % count($colors)]
                    ];
                });

            // 4. Giao dịch đặt sân mới nhất (Giữ nguyên)
            $recentBookings = Booking::with('field')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($b) {
                    return [
                        'id' => $b->id,
                        'booking_code' => $b->booking_code,
                        'customer_name' => $b->customer_name,
                        'field_name' => $b->field->name ?? 'N/A',
                        'time_slot' => Carbon::parse($b->start_time)->format('H:i') . ' - ' . Carbon::parse($b->end_time)->format('H:i'),
                        'total_amount' => (float)$b->total_amount,
                        'status' => $b->status,
                    ];
                });

            // 5. Giao dịch sản phẩm mới nhất (Giữ nguyên)
            $recentOrders = Order::orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($o) {
                    return [
                        'id' => $o->id,
                        'order_code' => $o->order_code,
                        'customer_name' => $o->name ?? ($o->user->name ?? 'Khách vãng lai'),
                        'total_amount' => (float)$o->total_amount,
                        'status' => $o->status,
                        'created_at' => $o->created_at->format('d/m/Y H:i'),
                    ];
                });

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'revenueData' => $revenueData,
                'fieldUsage' => $fieldUsage,
                'recentBookings' => $recentBookings,
                'recentOrders' => $recentOrders,
                'yearlyTotal' => (float)$yearlyTotal
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function index2(\Illuminate\Http\Request $request)
    {
        try {
            $period = $request->query('period', 'week');
            $startDate = \Carbon\Carbon::now()->startOfWeek();
            $endDate = \Carbon\Carbon::now()->endOfWeek();

            if ($period === 'month') {
                $startDate = \Carbon\Carbon::now()->startOfMonth();
                $endDate = \Carbon\Carbon::now()->endOfMonth();
            } elseif ($period === 'year') {
                $startDate = \Carbon\Carbon::now()->startOfYear();
                $endDate = \Carbon\Carbon::now()->endOfYear();
            }

            // --- 1. Thống kê tổng hợp (Stats Cards) ---
            $totalBookingRev = (float)\App\Models\Booking::whereBetween('booking_date', [$startDate, $endDate])
                ->where('status', 'completed')->sum('total_amount');

            $totalOrderRev = (float)\App\Models\Order::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'completed')->sum('total_amount');

            // --- 2. Pie Chart (Doanh thu theo danh mục) ---
            $categoryData = \Illuminate\Support\Facades\DB::table('order_items')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->select('categories.name', \Illuminate\Support\Facades\DB::raw('SUM(order_items.price * order_items.quantity) as total_value'))
                ->groupBy('categories.id', 'categories.name')
                ->orderBy(\Illuminate\Support\Facades\DB::raw('SUM(order_items.price * order_items.quantity)'), 'desc')
                ->get()
                ->map(function ($item, $index) {
                    $colors = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'];
                    return [
                        'name' => $item->name,
                        'value' => (float)$item->total_value,
                        'color' => $colors[$index % count($colors)]
                    ];
                });

            // --- 3. Top 5 sản phẩm bán chạy nhất ---
            $topProducts = \Illuminate\Support\Facades\DB::table('order_items')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->select(
                    'products.id',
                    'products.name',
                    \Illuminate\Support\Facades\DB::raw('SUM(order_items.quantity) as total_qty'),
                    \Illuminate\Support\Facades\DB::raw('SUM(order_items.price * order_items.quantity) as total_rev')
                )
                ->groupBy('products.id', 'products.name')
                // 🛑 FIX: Dùng trực tiếp SUM trong orderBy để tránh lỗi 1054
                ->orderBy(\Illuminate\Support\Facades\DB::raw('SUM(order_items.quantity)'), 'desc')
                ->limit(5)
                ->get()
                ->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'quantity' => (int)$p->total_qty,
                    'revenue' => (float)$p->total_rev
                ]);

            // --- 4. Top 5 khách hàng chi tiêu nhiều nhất (Đã Join bảng Users) ---
            $topCustomers = \Illuminate\Support\Facades\DB::table('orders')
                ->join('users', 'orders.user_id', '=', 'users.id') // 🛑 Join để lấy tên từ bảng users
                ->select(
                    'users.name', // 🛑 Lấy name từ bảng users
                    \Illuminate\Support\Facades\DB::raw('COUNT(orders.id) as total_orders'),
                    \Illuminate\Support\Facades\DB::raw('SUM(orders.total_amount) as total_spent')
                )
                ->where('orders.status', 'completed')
                ->groupBy('users.id', 'users.name')
                ->orderBy(\Illuminate\Support\Facades\DB::raw('SUM(orders.total_amount)'), 'desc')
                ->limit(5)
                ->get()
                ->map(fn($c) => [
                    'name' => $c->name,
                    'orders' => (int)$c->total_orders,
                    'totalSpent' => (float)$c->total_spent
                ]);

            // --- 5. Daily Data cho biểu đồ cột/vùng ---
            $dailyData = [];
            $tempStart = $startDate->copy();
            while ($tempStart <= $endDate) {
                $dateStr = $tempStart->format('Y-m-d');
                $dailyData[] = [
                    'date' => $tempStart->format('d/m'),
                    'orders' => \App\Models\Order::whereDate('created_at', $dateStr)->count(),
                    'bookings' => \App\Models\Booking::whereDate('booking_date', $dateStr)->count(),
                    'revenue' => (float)\App\Models\Booking::whereDate('booking_date', $dateStr)->where('status', 'completed')->sum('total_amount')
                        + (float)\App\Models\Order::whereDate('created_at', $dateStr)->where('status', 'completed')->sum('total_amount')
                ];
                $tempStart->addDay();
            }

            return response()->json([
                'success' => true,
                'totalRevenue' => $totalBookingRev + $totalOrderRev,
                'totalOrders' => \App\Models\Order::whereBetween('created_at', [$startDate, $endDate])->count(),
                'totalBookings' => \App\Models\Booking::whereBetween('booking_date', [$startDate, $endDate])->count(),
                'dailyData' => $dailyData,
                'categoryData' => $categoryData,
                'topProducts' => $topProducts,
                'topCustomers' => $topCustomers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi SQL: ' . $e->getMessage()
            ], 500);
        }
    }
}
