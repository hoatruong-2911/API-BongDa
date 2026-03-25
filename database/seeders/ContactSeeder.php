<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        $contacts = [
            [
                'name' => 'Nguyễn Văn Hải',
                'email' => 'hai.nguyen@gmail.com',
                'phone' => '0912345678',
                'subject' => 'Hỏi về đặt sân cố định',
                'message' => 'Chào admin, tôi muốn đặt sân vào tối thứ 7 hàng tuần khung giờ 19h-21h thì giá cả như thế nào?',
                'status' => 0, // Chưa đọc
                'admin_note' => null,
            ],
            [
                'name' => 'Trần Thị Mai',
                'email' => 'mai.tran@yahoo.com',
                'phone' => '0987654321',
                'subject' => 'Tổ chức giải đấu công ty',
                'message' => 'Công ty chúng tôi muốn thuê trọn gói 4 sân vào chủ nhật tuần tới để làm giải nội bộ, vui lòng báo giá giúp.',
                'status' => 1, // Đang xử lý
                'admin_note' => 'Đã gọi điện tư vấn sơ bộ, đang chờ khách chốt số lượng sân.',
            ],
            [
                'name' => 'Lê Minh Quân',
                'email' => 'quan.le@platinum.vn',
                'phone' => '0905111222',
                'subject' => 'Phản hồi về chất lượng đèn',
                'message' => 'Tối qua sân số 2 hơi tối ở góc cuối sân, admin kiểm tra lại hệ thống đèn giúp nhé.',
                'status' => 2, // Hoàn tất
                'admin_note' => 'Đã thay bóng LED mới cho sân số 2 vào sáng nay.',
            ],
            [
                'name' => 'Phạm Hoàng Nam',
                'email' => 'nam.pham@outlook.com',
                'phone' => '0334555666',
                'subject' => 'Hợp tác quảng cáo',
                'message' => 'Tôi muốn đặt bảng hiệu quảng cáo tại sân, liên hệ với ai để trao đổi chi tiết?',
                'status' => 0, // Chưa đọc
                'admin_note' => null,
            ],
            [
                'name' => 'Hoàng Thanh Bình',
                'email' => 'binh.hoang@gmail.com',
                'phone' => '0778999000',
                'subject' => 'Quên đồ tại sân',
                'message' => 'Mình có để quên đôi giày Adidas màu trắng tại băng ghế chờ sân số 1 chiều nay, admin có nhặt được không?',
                'status' => 1, // Đang xử lý
                'admin_note' => 'Đã kiểm tra kho đồ thất lạc nhưng chưa thấy, đang check lại camera.',
            ],
        ];

        foreach ($contacts as $contact) {
            $contact['created_at'] = Carbon::now();
            $contact['updated_at'] = Carbon::now();
            DB::table('contacts')->insert($contact);
        }
    }
}