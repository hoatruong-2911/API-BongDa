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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->integer('total_bookings')->default(0);      // Số lần đặt
            $table->decimal('total_spent', 15, 2)->default(0);  // Tổng chi tiêu
            $table->date('last_booking')->nullable();           // Ngày đặt gần nhất
            $table->enum('status', ['active', 'inactive'])->default('active'); // Trạng thái
            $table->boolean('is_vip')->default(false);          // Phân loại VIP
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
