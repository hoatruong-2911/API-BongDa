<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use Carbon\Carbon;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Nguyễn Văn Anh',
                'email' => 'vananh.nguyen@example.com',
                'phone' => '0901234567',
                'total_bookings' => 25,
                'total_spent' => 15500000.00,
                'last_booking' => Carbon::now()->subDays(2),
                'status' => 'active',
                'is_vip' => true,
            ],
            [
                'name' => 'Trần Thị Bảo',
                'email' => 'baobao.tran@example.com',
                'phone' => '0912345678',
                'total_bookings' => 18,
                'total_spent' => 12800000.00,
                'last_booking' => Carbon::now()->subDays(5),
                'status' => 'active',
                'is_vip' => true,
            ],
            [
                'name' => 'Lê Hoàng Long',
                'email' => 'long.le@example.com',
                'phone' => '0988777666',
                'total_bookings' => 5,
                'total_spent' => 3200000.00,
                'last_booking' => Carbon::now()->subMonth(),
                'status' => 'active',
                'is_vip' => false,
            ],
            [
                'name' => 'Phạm Minh Đức',
                'email' => 'ducpham@example.com',
                'phone' => '0933444555',
                'total_bookings' => 2,
                'total_spent' => 1500000.00,
                'last_booking' => Carbon::now()->subMonths(2),
                'status' => 'inactive',
                'is_vip' => false,
            ],
            [
                'name' => 'Đặng Thu Thảo',
                'email' => 'thuthao.dang@example.com',
                'phone' => '0977888999',
                'total_bookings' => 12,
                'total_spent' => 8900000.00,
                'last_booking' => Carbon::now()->subDays(10),
                'status' => 'active',
                'is_vip' => true,
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}
