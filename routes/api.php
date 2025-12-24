<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    AuthController,
    FieldController,
    BookingController,
    ProductController,
    OrderController,
    StaffController,
    ShiftController,
    AttendanceController,
    DashboardController,
    UserController,
    BrandController,
    CategoryController
};
use App\Models\Category;

//------------------------------------------------------

/* --- 1. PUBLIC ROUTES --- */

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
});

Route::get('products', [ProductController::class, 'index']);
Route::get('products/{product}', [ProductController::class, 'show']);

//------------------------------------------------------

/* --- 2. PROTECTED ROUTES (Yêu cầu Đăng nhập) --- */
Route::middleware('auth:sanctum')->group(function () {

    // Thông tin cá nhân
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/user', [UserController::class, 'updateProfile']); // User tự cập nhật profile
    Route::post('/auth/change-password', [UserController::class, 'changePassword']);

    //------------------------------------------------------

    /* --- 2.1. CUSTOMER & ABOVE (Customer, Staff, Admin) --- */
    Route::middleware('role:customer,staff,admin')->group(function () {
        Route::get('fields', [FieldController::class, 'index']);
        Route::get('fields/{field}', [FieldController::class, 'show']);
        Route::get('fields/{field}/schedule', [FieldController::class, 'getSchedule']);

        // Booking cơ bản
        Route::apiResource('bookings', BookingController::class)->only(['index', 'store', 'show']);
        Route::apiResource('orders', OrderController::class)->only(['store']);
    });


    //------------------------------------------------------

    /* --- 2.2. STAFF & ABOVE (Staff, Admin) --- */
    Route::middleware('role:staff,admin')->group(function () {
        // Quản lý Booking nâng cao
        Route::patch('bookings/{booking}/status', [BookingController::class, 'changeStatus']);
        Route::apiResource('bookings', BookingController::class)->only(['update', 'destroy']);

        // Quản lý Order
        Route::put('orders/{order}/update-status', [OrderController::class, 'updateStatus']);
        Route::apiResource('orders', OrderController::class)->only(['index', 'show', 'update', 'destroy']);

        // Chấm công
        Route::post('attendance/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('attendance/check-out', [AttendanceController::class, 'checkOut']);
    });

    //------------------------------------------------------

    /* --- 2.3. ADMIN ONLY --- */
    Route::middleware('role:admin,staff')->group(function () {
        // Quản lý User (Admin quản lý tài khoản người khác)
        // URL: /api/admin/users
        Route::prefix('admin')->group(function () {
            Route::get('users', [UserController::class, 'index']);
            Route::post('users/register', [UserController::class, 'store']); // Tạo mới tài khoản
            Route::put('users/{id}', [UserController::class, 'update']); // Sửa theo ID truyền vào
            Route::get('users/{id}', [UserController::class, 'show']); //(Để lấy dữ liệu sửa)
            Route::delete('users/{user}', [UserController::class, 'destroy']);
            Route::patch('users/{id}/status', [UserController::class, 'toggleStatus']); //(Để lấy dữ liệu sửa)

            // --- QUẢN LÝ BRAND (Thương hiệu) ---
            // Sử dụng apiResource sẽ tự tạo: index, store, show, update, destroy
            Route::apiResource('brands', BrandController::class);
            Route::post('brands/add', [BrandController::class, 'store']); // Tạo mới 
            Route::get('brands/{id}', [BrandController::class, 'show']); //(Để lấy dữ liệu )
            Route::patch('brands/{id}/toggle-status', [BrandController::class, 'toggleStatus']); //(Để lấy dữ liệu sửa)




            // --- QUẢN LÝ CATEGORY (Danh mục) ---
            Route::apiResource('categories', CategoryController::class);
            Route::post('categories/add', [CategoryController::class, 'store']); // Tạo mới 
            Route::get('categories/{id}', [CategoryController::class, 'show']); //(Để lấy dữ liệu )
            Route::patch('categories/{id}/toggle-status', [CategoryController::class, 'toggleStatus']); //(Để lấy dữ liệu sửa)

            // --- QUẢN LÝ Product (Danh mục) ---
            Route::apiResource('products', ProductController::class);
            Route::post('products/add', [ProductController::class, 'store']); // Tạo mới
            Route::get('products/{id}', [ProductController::class, 'show']); //(Để lấy dữ liệu )
            Route::patch('products/{id}/toggle-status', [ProductController::class, 'toggleStatus']);
            

        });

        Route::get('dashboard/summary', [DashboardController::class, 'summary']);

        // Quản lý Sân & Sản phẩm (CUD)
        Route::apiResource('fields', FieldController::class)->except(['index', 'show']);
        Route::apiResource('products', ProductController::class)->except(['index', 'show']);

        // Quản lý Nhân sự
        Route::apiResource('staff', StaffController::class);
        Route::apiResource('shifts', ShiftController::class);
        Route::get('attendance', [AttendanceController::class, 'index']);
        Route::delete('attendance/{attendance}', [AttendanceController::class, 'destroy']);
    });
});
