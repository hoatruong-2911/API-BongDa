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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Khóa ngoại liên kết với orders và bookings (cả hai đều nullable)
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('booking_id')->nullable()->constrained()->onDelete('set null');

            $table->decimal('amount', 12, 2);
            // Phương thức thanh toán
            $table->enum('method', ['cash', 'transfer', 'momo', 'vnpay', 'card']);
            // Trạng thái thanh toán
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');

            $table->string('transaction_id')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
