<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // 1. Mã đơn hàng (Dùng để đối soát thanh toán tự động)
            $table->string('order_code')->unique();

            // 2. Liên kết người dùng/nhân viên
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('staff_id')->nullable()->constrained('users')->onDelete('set null');

            // 3. Thông tin người nhận (Đầy đủ để liên lạc)
            $table->string('customer_name');
            $table->string('phone');
            $table->string('email')->nullable();

            // 4. Địa chỉ nhận hàng tĩnh (Lưu lại để in hóa đơn sau này nếu cần)
            $table->string('pickup_address')->default('Sân bóng Thanh Hóa Soccer, Văn Lâm 3, Phước Nam, Thuận Nam, Ninh Thuận');

            // 5. Tài chính
            $table->decimal('total_amount', 12, 2);
            $table->decimal('service_fee', 10, 2)->default(0); // Phí dịch vụ 2% bro tính bên Frontend

            // 6. Trạng thái và Phương thức
            // Thêm trạng thái 'paid' để hệ thống tự nhảy trang khi nhận được tiền
            $table->enum('status', ['pending', 'paid', 'preparing', 'completed', 'cancelled'])->default('pending');
            $table->enum('order_type', ['online', 'counter'])->default('online');
            $table->enum('payment_method', ['qr', 'cash'])->default('qr');

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
