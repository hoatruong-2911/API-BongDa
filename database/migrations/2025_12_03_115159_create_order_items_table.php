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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            // Khóa ngoại liên kết với bảng orders (ON DELETE CASCADE)
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            // Khóa ngoại liên kết với bảng products
            $table->foreignId('product_id')->constrained()->onDelete('restrict');

            $table->string('product_name');     // Tên sản phẩm (lưu lại tại thời điểm mua)
            $table->integer('quantity');        // Số lượng
            $table->decimal('price', 10, 2);    // Giá tại thời điểm mua
            $table->decimal('subtotal', 12, 2); // Tổng phụ (quantity * price)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
