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
    CategoryController,
    CustomerController,
    DepartmentController
};
use App\Models\Category;
use App\Models\Department;

//------------------------------------------------------

/* --- 1. PUBLIC ROUTES (Ai cũng xem được) --- */

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
});

// Các Route công khai cho Sân bóng - THÊM DÒNG NÀY VÀO ĐÂY:
Route::get('fields', [FieldController::class, 'index']); // Khách xem danh sách sân
Route::get('fields/{field}', [FieldController::class, 'show']); // Khách xem chi tiết sân
Route::get('fields/{field}/schedule', [FieldController::class, 'getSchedule']); // Khách xem lịch sân

Route::get('/products/customer', [ProductController::class, 'listForCustomer']);
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{product}', [ProductController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/brands', [BrandController::class, 'index']);
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
        // Route::get('fields', [FieldController::class, 'index']);
        // Route::get('fields/{field}', [FieldController::class, 'show']);
        // Route::get('fields/{field}/schedule', [FieldController::class, 'getSchedule']);

        // Booking cơ bản
        Route::apiResource('bookings', BookingController::class)->only(['index', 'store', 'show']);
        Route::apiResource('orders', OrderController::class)->only(['store']);
    });


    //------------------------------------------------------

    /* --- 2.2. ADMIN ONLY, staff --- */
    Route::middleware('role:admin,staff')->group(function () {
        // Quản lý User (Admin quản lý tài khoản người khác)
        // URL: /api/admin/users

        // Quản lý Booking nâng cao
        Route::patch('bookings/{booking}/status', [BookingController::class, 'changeStatus']);
        Route::apiResource('bookings', BookingController::class)->only(['update', 'destroy']);

        // Quản lý Order
        Route::put('orders/{order}/update-status', [OrderController::class, 'updateStatus']);
        Route::apiResource('orders', OrderController::class)->only(['index', 'show', 'update', 'destroy']);

        // Chấm công
        Route::post('attendance/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('attendance/check-out', [AttendanceController::class, 'checkOut']);


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
            // Route::post('brands/add', [BrandController::class, 'store']); // Tạo mới 
            // Route::get('brands/{id}', [BrandController::class, 'show']); //(Để lấy dữ liệu )
            Route::patch('brands/{id}/toggle-status', [BrandController::class, 'toggleStatus']); //(Để lấy dữ liệu sửa)


            // --- QUẢN LÝ CATEGORY (Danh mục) ---
            Route::apiResource('categories', CategoryController::class);
            // Route::post('categories/add', [CategoryController::class, 'store']); // Tạo mới 
            // Route::get('categories/{id}', [CategoryController::class, 'show']); //(Để lấy dữ liệu )
            Route::patch('categories/{id}/toggle-status', [CategoryController::class, 'toggleStatus']); //(Để lấy dữ liệu sửa)

            // --- QUẢN LÝ Product (Danh mục) ---
            Route::apiResource('products', ProductController::class);
            // Route::post('products/add', [ProductController::class, 'store']); // Tạo mới
            // Route::get('products/{id}', [ProductController::class, 'show']); //(Để lấy dữ liệu )
            Route::patch('products/{id}/toggle-status', [ProductController::class, 'toggleStatus']);

            // quản lý phòng ban 
            Route::apiResource('departments', DepartmentController::class);
            // Route::post('departments/add', [DepartmentController::class, 'store']); // Tạo mới
            // Route::get('departments/{id}', [DepartmentController::class, 'show']); //(Để lấy dữ liệu )
            Route::patch('departments/{id}/toggle-status', [DepartmentController::class, 'toggleStatus']);

            // quản lý nhân viên 
            Route::apiResource('staff', StaffController::class);
            // Route::post('staff/add', [StaffController::class, 'store']); // Tạo mới
            // Route::get('staff/{id}', [StaffController::class, 'show']); //(Để lấy dữ liệu )
            Route::patch('staff/{id}/toggle-status', [StaffController::class, 'toggleStatus']);


            // --- QUẢN LÝ LỊCH CA LÀM ---

            // 1. Quản lý danh mục loại ca (Sáng, Chiều, Tối...)
            // Route này tạo ra các URL: /shifts (GET, POST), /shifts/{id} (GET, PUT, DELETE)
            Route::apiResource('shifts', ShiftController::class);

            // 2. Quản lý lịch phân ca (Shift Assignments)
            // Lấy danh sách tổng quát theo tuần
            Route::get('shift-assignments', [ShiftController::class, 'getAssignments']);

            // Gán ca làm mới (POST)
            Route::post('shift-assignments', [ShiftController::class, 'assignShift']);

            // Lấy chi tiết MỘT bản ghi phân ca cụ thể để đổ vào Form Edit (GET)
            // Phải đặt dòng này rõ ràng để Frontend gọi đúng dữ liệu cần sửa
            Route::get('shift-assignments/{id}', [ShiftController::class, 'getAssignmentDetail']);

            // Cập nhật bản ghi phân ca đó (PUT)
            Route::put('shift-assignments/{id}', [ShiftController::class, 'updateAssignment']);

            // Xóa một bản ghi phân ca (DELETE)
            Route::delete('shift-assignments/{id}', [ShiftController::class, 'removeAssignment']);

            // 3. Lấy chi tiết toàn bộ lịch sử ca của một nhân viên (Trang ShiftDetail)
            // Route này dùng cho nút "Xem chi tiết" (icon con mắt)
            Route::get('staff-shifts/{id}', [ShiftController::class, 'getStaffDetail']);


            // xóa
            Route::delete('staff-assignments/bulk/{staffId}', [ShiftController::class, 'removeStaffWeeklyAssignments']);

            // chấm công
            Route::get('attendances', [AttendanceController::class, 'index']);
            Route::post('attendances', [AttendanceController::class, 'store']);
            Route::get('attendances/{id}', [AttendanceController::class, 'show']);    // THIẾU CÁI NÀY NÊN LỖI 404
            Route::delete('attendances/{id}', [AttendanceController::class, 'destroy']); // CẦN CHO NÚT XÓA


            // quan ly khach hang
            Route::apiResource('customers', CustomerController::class);
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
