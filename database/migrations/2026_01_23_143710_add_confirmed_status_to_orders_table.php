<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL không hỗ trợ sửa ENUM qua Blueprint mặc định của Laravel dễ dàng
        // Nên chúng ta dùng DB::statement cho chuẩn xác
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'confirmed', 'paid', 'preparing', 'completed', 'cancelled') DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Quay lại trạng thái cũ nếu cần
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'paid', 'preparing', 'completed', 'cancelled') DEFAULT 'pending'");
    }
};