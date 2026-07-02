<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Api\BookingController;

class CancelExpiredDeposits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:cancel-expired-deposits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tự động quét và hủy các đơn đặt sân đã cọc 30% những trễ hạn thanh toán 70% còn lại (trước giờ đá 6 tiếng)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Bắt đầu quét các đơn đặt sân trễ hẹn thanh toán cọc...');
        BookingController::cancelExpiredDeposits();
        $this->info('Đã hoàn thành quét và hủy các đơn đặt sân trễ hạn thanh toán cọc!');
        return 0;
    }
}
