<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller {
    public function handleWebhook(Request $request) {
        // 🛑 Lấy dữ liệu từ SePay bắn sang
        $data = $request->all();

        // Log lại để kiểm tra nếu có lỗi (Dùng cho môi trường dev)
        Log::info('SePay Webhook Data: ', $data);

        // SePay gửi nội dung chuyển khoản ở trường 'content'
        // Chúng ta sẽ lưu vào bảng bank_transactions để hàm checkStatus đối soát
        DB::table('bank_transactions')->updateOrInsert(
            ['transaction_id' => $data['id'] ?? $data['reference_number']], // Mã giao dịch SePay
            [
                'amount'         => $data['amount'],
                'description'    => $data['content'], // Ví dụ: "ORD816213 THANH TOAN"
                'transaction_at' => $data['transaction_date'] ?? now(),
                'created_at'     => now(),
            ]
        );

        return response()->json(['success' => true]);
    }
}