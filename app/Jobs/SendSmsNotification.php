<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // ⬅️ THÊM NÀY
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log; // Dùng để ghi log

class SendSmsNotification implements ShouldQueue // ⬅️ IMPLEMENT NÀY
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $phoneNumber;
    protected string $message;

    /**
     * Khởi tạo Job với dữ liệu cần thiết.
     * @param string $phoneNumber
     * @param string $message
     */
    public function __construct(string $phoneNumber, string $message)
    {
        $this->phoneNumber = $phoneNumber;
        $this->message = $message;
        // Tùy chọn: Đặt job vào queue riêng biệt
        $this->onQueue('sms');
    }

    /**
     * Thực hiện logic gửi SMS khi Job được xử lý bởi worker.
     *
     * @return void
     */
    public function handle(): void
    {
        // Đây là nơi bạn sẽ tích hợp dịch vụ gửi SMS thực tế (Twilio/local provider API)

        // Hiện tại, chúng ta mô phỏng bằng cách ghi log:
        Log::info("QUEUE: Đã xử lý yêu cầu gửi SMS. Đến số: {$this->phoneNumber} - Nội dung: {$this->message}");

        // Sau khi tích hợp:
        // try {
        //     SmsService::send($this->phoneNumber, $this->message);
        // } catch (\Exception $e) {
        //     // Xử lý lỗi (ví dụ: gửi lại sau, ghi vào failed_jobs)
        //     throw $e;
        // }
    }
}
