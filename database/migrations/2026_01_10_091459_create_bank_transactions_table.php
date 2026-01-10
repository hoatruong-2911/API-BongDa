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
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique(); // Mã giao dịch từ cổng thanh toán
            $table->decimal('amount', 15, 2);           // Số tiền nhận được
            $table->string('description');              // Nội dung chuyển khoản (chứa mã ORD...)
            $table->timestamp('transaction_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
