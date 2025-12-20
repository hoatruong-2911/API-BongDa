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
        Schema::create('fields', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->enum('type', ['f5', 'f7', 'f11'])->default('f5');// ⬅ THÊM DÒNG NÀY: Loại sân (f5, f7, f11)
            $table->decimal('price', 10, 2);           // Giá thuê cơ bản (Ví dụ: 500,000.00)
            $table->string('size', 50);                // Kích thước sân ('5 người', '7 người', '11 người')
            $table->string('surface', 100)->nullable(); // Loại mặt sân ('Cỏ nhân tạo', 'Cỏ tự nhiên')
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->json('features')->nullable();      // Các tính năng bổ sung (đèn, ghế ngồi, v.v.)
            $table->string('location')->nullable();
            $table->decimal('rating', 2, 1)->default(0); // Đánh giá (tối đa 5.0)
            $table->integer('reviews_count')->default(0);
            $table->boolean('available')->default(true); // Trạng thái sẵn sàng để đặt
            $table->boolean('is_vip')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fields');
    }
};
