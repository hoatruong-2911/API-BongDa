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

            // user_id có thể NULL nếu là khách vãng lai
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            // staff_id có thể NULL nếu là đơn hàng online (sau này sẽ dùng hệ thống thanh toán)
            $table->foreignId('staff_id')->nullable()->constrained('users')->onDelete('set null');

            $table->string('customer_name')->nullable();
            $table->decimal('total_amount', 12, 2);

            // Trạng thái đơn hàng
            $table->enum('status', ['pending', 'preparing', 'completed', 'cancelled'])->default('pending');
            // Loại đơn hàng: online (khách tự đặt) hoặc counter (Staff tạo tại quầy)
            $table->enum('order_type', ['online', 'counter'])->default('online');

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
