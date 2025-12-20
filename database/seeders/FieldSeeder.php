<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('fields')->insert([
            [
                'name' => 'Sân 7 - 7 người',
                'size' => 7,
                'location' => 'Khu A - Tầng 3',
                'price' => 750000.00,
                'type' => 'f7',
                'surface' => 'Cỏ nhân tạo tiêu chuẩn', // ⬅️ THÊM TRƯỜNG NÀY
                'description' => 'Sân 7 người tiêu chuẩn thi đấu, có mái che.',
                'rating' => 4.5,
                'reviews_count' => 76,
                'available' => true,
                'is_vip' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sân 5 - 5 người',
                'size' => 5,
                'location' => 'Khu C - Tầng 1',
                'price' => 480000.00,
                'type' => 'f5',
                'surface' => 'Cỏ nhân tạo 3G', // ⬅️ THÊM TRƯỜNG NÀY
                'description' => 'Sân 5 người có chất lượng cỏ tốt.',
                'rating' => 4.2,
                'reviews_count' => 92,
                'available' => true,
                'is_vip' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sân 9 - 7 người ngoài trời',
                'size' => 7,
                'location' => 'Khu D - Sân ngoài',
                'price' => 700000.00,
                'type' => 'f7',
                'surface' => 'Cỏ tự nhiên', // ⬅️ THÊM TRƯỜNG NÀY
                'description' => 'Sân 7 người mới được nâng cấp, ánh sáng tốt.',
                'rating' => 4.3,
                'reviews_count' => 105,
                'available' => true,
                'is_vip' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sân 10 - Sân 11 người ngoài trời',
                'size' => 11,
                'location' => 'Khu D - Sân chính',
                'price' => 1200000.00,
                'type' => 'f11',
                'surface' => 'Cỏ tự nhiên FIFA', // ⬅️ THÊM TRƯỜNG NÀY
                'description' => 'Sân 11 người lớn nhất khu vực.',
                'rating' => 4.6,
                'reviews_count' => 178,
                'available' => true,
                'is_vip' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sân 4 - Sân 5 người',
                'size' => 5,
                'location' => 'Khu C - Tầng 1',
                'price' => 450000.00,
                'type' => 'f5',
                'surface' => 'Cỏ nhân tạo', // ⬅️ THÊM TRƯỜNG NÀY
                'description' => 'Sân 5 người tiêu chuẩn, thường xuyên kín lịch.',
                'rating' => 4.2,
                'reviews_count' => 87,
                'available' => true,
                'is_vip' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sân 5 - Sân 7 người VIP',
                'size' => 7,
                'location' => 'Khu B - Tầng 3',
                'price' => 900000.00,
                'type' => 'f7',
                'surface' => 'Cỏ nhân tạo cao cấp', // ⬅️ THÊM TRƯỜNG NÀY
                'description' => 'Sân 7 người VIP, có điều hòa/quạt công suất lớn.',
                'rating' => 4.8,
                'reviews_count' => 142,
                'available' => true,
                'is_vip' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sân 6 - Sân 5 người trong nhà',
                'size' => 5,
                'location' => 'Khu C - Tầng 2',
                'price' => 550000.00,
                'type' => 'f5',
                'surface' => 'Thảm nhựa tổng hợp', // ⬅️ THÊM TRƯỜNG NÀY
                'description' => 'Sân 5 người trong nhà, không lo mưa nắng.',
                'rating' => 4.5,
                'reviews_count' => 113,
                'available' => true,
                'is_vip' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
