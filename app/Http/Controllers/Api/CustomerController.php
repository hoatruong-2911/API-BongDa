<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Http\Requests\Api\Customer\CustomerRequest;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        // 1. Tìm kiếm theo tên hoặc email
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        // 2. Lọc theo trạng thái
        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $customers = $query->latest()->get();

        // 3. Tính toán 4 chỉ số thống kê rực rỡ
        $stats = [
            'totalCustomers'  => Customer::count(),
            'activeCustomers' => Customer::where('status', 'active')->count(),
            'vipCustomers'    => Customer::where('is_vip', true)->count(),
            'totalBookings'   => Customer::sum('total_bookings'),
        ];

        return response()->json([
            'success' => true,
            'data'    => $customers,
            'stats'   => $stats
        ]);
    }

    public function store(CustomerRequest $request)
    {
        $customer = Customer::create($request->validated());
        return response()->json(['success' => true, 'data' => $customer]);
    }

    public function show(Customer $customer)
    {
        return response()->json(['success' => true, 'data' => $customer]);
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return response()->json(['success' => true, 'message' => 'Xóa khách hàng thành công!']);
    }
}
