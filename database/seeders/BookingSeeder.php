<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        // Xóa dữ liệu cũ
        DB::table('bookings')->delete();

        // Lấy danh sách ID hiện có để tránh lỗi khóa ngoại
        $userIds = DB::table('users')->pluck('id')->toArray();
        $fieldIds = DB::table('fields')->pluck('id')->toArray();

        if (empty($userIds) || empty($fieldIds)) {
            $this->command->warn("Cần có dữ liệu trong bảng users và fields trước khi seed bookings!");
            return;
        }

        $bookings = [
            [
                'customer_name' => 'Nguyễn Văn Nam',
                'customer_phone' => '0901234567',
                'status' => 'completed',
                'days_offset' => -2, // Cách đây 2 ngày
                'start_hour' => 17,
                'duration' => 1.5,
            ],
            [
                'customer_name' => 'Trần Minh Quang',
                'customer_phone' => '0988776655',
                'status' => 'approved',
                'days_offset' => 0, // Hôm nay
                'start_hour' => 19,
                'duration' => 2,
            ],
            [
                'customer_name' => 'Lê Hồng Phúc',
                'customer_phone' => '0933445566',
                'status' => 'pending',
                'days_offset' => 1, // Ngày mai
                'start_hour' => 18,
                'duration' => 1,
            ],
            [
                'customer_name' => 'Hoàng Anh Tuấn',
                'customer_phone' => '0977112233',
                'status' => 'cancelled',
                'days_offset' => -1, // Hôm qua
                'start_hour' => 20,
                'duration' => 1.5,
            ],
        ];

        foreach ($bookings as $data) {
            $bookingDate = Carbon::now()->addDays($data['days_offset']);
            $startTime = $bookingDate->copy()->setTime($data['start_hour'], 0, 0);
            $endTime = $startTime->copy()->addMinutes($data['duration'] * 60);

            // Giả định giá sân là 500k/h để tính total_amount
            $totalAmount = $data['duration'] * 500000;

            DB::table('bookings')->insert([
                'user_id' => $userIds[array_rand($userIds)],
                'field_id' => $fieldIds[array_rand($fieldIds)],
                'booking_date' => $bookingDate->format('Y-m-d'),
                'start_time' => $startTime->format('Y-m-d H:i:s'),
                'end_time' => $endTime->format('Y-m-d H:i:s'),
                'duration' => $data['duration'],
                'total_amount' => $totalAmount,
                'status' => $data['status'],
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'notes' => 'Khách hàng quen thuộc, cần chuẩn bị thêm nước uống.',
                'approved_by' => ($data['status'] !== 'pending') ? $userIds[0] : null,
                'approved_at' => ($data['status'] !== 'pending') ? Carbon::now() : null,
                'confirmed_by' => ($data['status'] === 'completed') ? $userIds[0] : null,
                'confirmed_at' => ($data['status'] === 'completed') ? Carbon::now() : null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
