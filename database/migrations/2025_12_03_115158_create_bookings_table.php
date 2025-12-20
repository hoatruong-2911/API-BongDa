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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            // Khóa ngoại liên kết với Khách hàng và Sân bóng
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Khách hàng đặt
            $table->foreignId('field_id')->constrained()->onDelete('cascade'); // Sân được đặt

            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('duration'); // Số giờ (Ví dụ: 2)
            $table->decimal('total_amount', 12, 2);

            // THÊM: Trạng thái booking
            $table->enum('status', ['pending', 'approved', 'rejected', 'playing', 'completed', 'cancelled'])->default('pending');

            $table->string('customer_name')->nullable();
            $table->string('customer_phone', 20)->nullable();
            $table->text('notes')->nullable();

            // Khóa ngoại cho người duyệt/xác nhận (Dùng cho Staff/Admin)
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->onDelete('set null'); // Staff xác nhận khách đến
            $table->timestamp('confirmed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
