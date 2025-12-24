<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Xóa dữ liệu cũ để tránh trùng lặp khi chạy lại lệnh
        DB::table('products')->truncate();

        DB::table('products')->insert([
            // --- GIÀY ĐÁ BÓNG (Category 1) ---
            [
                'name' => 'Nike Mercurial Vapor 15',
                'category_id' => 1,
                'brand_id' => 1,
                'price' => 1250000,
                'stock' => 10,
                'unit' => 'đôi',
                'available' => true,
                'description' => 'Giày đá bóng cỏ nhân tạo cao cấp',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Adidas Predator Accuracy',
                'category_id' => 1,
                'brand_id' => 2,
                'price' => 1100000,
                'stock' => 8,
                'unit' => 'đôi',
                'available' => true,
                'description' => 'Kiểm soát bóng tối ưu',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Mizuno Morelia Neo III',
                'category_id' => 1,
                'brand_id' => 4,
                'price' => 1550000,
                'stock' => 5,
                'unit' => 'đôi',
                'available' => true,
                'description' => 'Giày da thật siêu nhẹ từ Nhật Bản',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Kamito Velocidad 3',
                'category_id' => 1,
                'brand_id' => 5,
                'price' => 650000,
                'stock' => 20,
                'unit' => 'đôi',
                'available' => true,
                'description' => 'Giày bóng đá thuần Việt',
                'created_at' => now(),
                'updated_at' => now()
            ],

            // --- ÁO ĐẤU (Category 2) ---
            [
                'name' => 'Áo MU Sân Nhà 2024',
                'category_id' => 2,
                'brand_id' => 2,
                'price' => 150000,
                'stock' => 50,
                'unit' => 'cái',
                'available' => true,
                'description' => 'Áo thun lạnh thấm hút mồ hôi',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Áo Real Madrid 2024',
                'category_id' => 2,
                'brand_id' => 2,
                'price' => 150000,
                'stock' => 45,
                'unit' => 'cái',
                'available' => true,
                'description' => 'Phiên bản Player chuẩn form',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Áo Tuyển Việt Nam Kamito',
                'category_id' => 2,
                'brand_id' => 5,
                'price' => 350000,
                'stock' => 30,
                'unit' => 'cái',
                'available' => true,
                'description' => 'Hàng chính hãng Kamito',
                'created_at' => now(),
                'updated_at' => now()
            ],

            // --- NƯỚC GIẢI KHÁT & TĂNG LỰC (Category 3 & 4) ---
            [
                'name' => 'Coca Cola 330ml',
                'category_id' => 3,
                'brand_id' => 6,
                'price' => 15000,
                'stock' => 100,
                'unit' => 'lon',
                'available' => true,
                'description' => 'Nước ngọt giải khát có ga',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Pepsi Vị Chanh',
                'category_id' => 3,
                'brand_id' => 7,
                'price' => 15000,
                'stock' => 80,
                'unit' => 'lon',
                'available' => true,
                'description' => 'Không calo, vị chanh sảng khoái',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Red Bull Thái',
                'category_id' => 4,
                'brand_id' => 8,
                'price' => 20000,
                'stock' => 60,
                'unit' => 'lon',
                'available' => true,
                'description' => 'Tăng cường năng lượng tức thì',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Nước Suối Aquafina 500ml',
                'category_id' => 3,
                'brand_id' => 9,
                'price' => 10000,
                'stock' => 200,
                'unit' => 'chai',
                'available' => true,
                'description' => 'Nước tinh khiết',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Revive Chanh Muối',
                'category_id' => 3,
                'brand_id' => 10,
                'price' => 12000,
                'stock' => 150,
                'unit' => 'chai',
                'available' => true,
                'description' => 'Bù khoáng và điện giải',
                'created_at' => now(),
                'updated_at' => now()
            ],

            // --- PHỤ KIỆN (Category 6, 7, 8...) ---
            [
                'name' => 'Găng tay Adidas Predator Pro',
                'category_id' => 6,
                'brand_id' => 2,
                'price' => 850000,
                'stock' => 12,
                'unit' => 'đôi',
                'available' => true,
                'description' => 'Găng tay thủ môn chuyên nghiệp',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Tất chống trượt Nike',
                'category_id' => 7,
                'brand_id' => 1,
                'price' => 45000,
                'stock' => 100,
                'unit' => 'đôi',
                'available' => true,
                'description' => 'Hỗ trợ bám giày cực tốt',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Quả bóng Mitre 1811',
                'category_id' => 8,
                'brand_id' => 12,
                'price' => 450000,
                'stock' => 15,
                'unit' => 'quả',
                'available' => true,
                'description' => 'Bóng thi đấu sân 7',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Quả bóng Molten Vantaggio',
                'category_id' => 8,
                'brand_id' => 13,
                'price' => 550000,
                'stock' => 10,
                'unit' => 'quả',
                'available' => true,
                'description' => 'Bóng đạt tiêu chuẩn FIFA',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Băng cuốn cổ chân Zocker',
                'category_id' => 9,
                'brand_id' => 15,
                'price' => 25000,
                'stock' => 200,
                'unit' => 'cuộn',
                'available' => true,
                'description' => 'Hỗ trợ bảo vệ dây chằng',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Túi đựng giày Grand Sport',
                'category_id' => 10,
                'brand_id' => 11,
                'price' => 120000,
                'stock' => 25,
                'unit' => 'cái',
                'available' => true,
                'description' => 'Chất liệu chống thấm nước',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Bình nước thể thao Puma',
                'category_id' => 12,
                'brand_id' => 3,
                'price' => 180000,
                'stock' => 20,
                'unit' => 'cái',
                'available' => true,
                'description' => 'Bình nhựa BPA free cao cấp',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Áo khoác gió Nike Academy',
                'category_id' => 13,
                'brand_id' => 1,
                'price' => 650000,
                'stock' => 15,
                'unit' => 'cái',
                'available' => true,
                'description' => 'Chống mưa nhẹ, giữ ấm khi khởi động',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
