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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            // THÊM: Loại sản phẩm (ENUM)
            $table->enum('category', ['food', 'drink', 'apparel', 'accessories']);
            $table->decimal('price', 10, 2);
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->integer('stock')->default(0);      // Số lượng tồn kho
            $table->string('unit', 50)->default('cái'); // Đơn vị tính
            $table->boolean('available')->default(true); // Trạng thái sẵn sàng bán

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
