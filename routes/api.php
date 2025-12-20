<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Import Controllers
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FieldController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| 1. AUTHENTICATION (CÔNG KHAI)
|--------------------------------------------------------------------------
| Các route công khai, không cần Sanctum.
*/

// Route::post('login', [AuthController::class, 'login']);
// Route::post('register', [AuthController::class, 'register']);
// routes/api.php
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
});

// ⬅️ THÊM CÁC ROUTE PRODUCT CÔNG KHAI TẠI ĐÂY
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{product}', [ProductController::class, 'show']);

/*
|--------------------------------------------------------------------------
| 2. PROTECTED ROUTES (Cần Sanctum Auth)
|--------------------------------------------------------------------------
| Tất cả các route bên trong nhóm này yêu cầu người dùng phải đăng nhập
| và có token hợp lệ.
*/
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);
    
    Route::put('auth/user', [UserController::class, 'update']);


    /*
    |----------------------------------------------------------------------
    | 2.1. CUSTOMER ROUTES (Khách hàng & Cấp cao hơn)
    |----------------------------------------------------------------------
    | Cho phép vai trò 'customer', 'staff', 'admin'
    */
    Route::middleware('role:customer,staff,admin')->group(function () {

        // Quản lý Sân (Field): Xem danh sách (Tất cả vai trò)
        Route::get('fields', [FieldController::class, 'index']);
        Route::get('fields/{field}', [FieldController::class, 'show']);

        Route::get('fields/{field}/schedule', [FieldController::class, 'getSchedule']);

        // Đặt Sân (Booking): Khách hàng có thể tạo booking, xem booking của mình
        Route::apiResource('bookings', BookingController::class)->only(['index', 'store', 'show']);

        // Đơn Hàng (Order): Tạo đơn hàng (Bán hàng tại sân)
        Route::apiResource('orders', OrderController::class)->only(['store']);

        // Sản phẩm (Product): Xem danh sách sản phẩm (để bán kèm)
        // Route::get('products', [ProductController::class, 'index']);
        // Route::get('products/{product}', [ProductController::class, 'show']);
    });


    /*
    |----------------------------------------------------------------------
    | 2.2. STAFF ROUTES (Nhân viên & Cấp cao hơn)
    |----------------------------------------------------------------------
    | Chỉ cho phép vai trò 'staff' và 'admin'
    */
    Route::middleware('role:staff,admin')->group(function () {

        // Booking: Staff có thể duyệt hoặc hủy bỏ booking của người khác
        Route::put('bookings/{booking}/update-status', [BookingController::class, 'updateStatus']); // Tự tạo
        Route::apiResource('bookings', BookingController::class)->only(['update', 'destroy']);


        // Orders: Staff có thể quản lý trạng thái đơn hàng
        Route::put('orders/{order}/update-status', [OrderController::class, 'updateStatus']); // Tự tạo
        Route::apiResource('orders', OrderController::class)->only(['index', 'show', 'update', 'destroy']);


        // Chấm Công (Attendance): Staff có thể tự chấm công
        Route::post('attendance/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('attendance/check-out', [AttendanceController::class, 'checkOut']);
    });


    /*
    |----------------------------------------------------------------------
    | 2.3. ADMIN ROUTES (Chỉ Admin)
    |----------------------------------------------------------------------
    | Chỉ cho phép vai trò 'admin'
    */
    Route::middleware('role:admin')->group(function () {

        // Dashboard: Admin có quyền xem Dashboard tổng quan
        Route::get('dashboard/summary', [DashboardController::class, 'summary']); // Tự tạo


        // Quản lý Sân (Field)
        Route::apiResource('fields', FieldController::class)->except(['index', 'show']);

        // Quản lý Sản phẩm (Product)
        Route::apiResource('products', ProductController::class)->except(['index', 'show']);


        // Quản lý Nhân sự (Staff - CUD)
        Route::apiResource('staff', StaffController::class);

        // Quản lý Ca làm việc (Shift)
        Route::apiResource('shifts', ShiftController::class);

        // Quản lý Chấm công (Attendance - Tổng quan)
        Route::get('attendance', [AttendanceController::class, 'index']); // Xem tất cả
        Route::delete('attendance/{attendance}', [AttendanceController::class, 'destroy']); // Xóa chấm công
    });
});
