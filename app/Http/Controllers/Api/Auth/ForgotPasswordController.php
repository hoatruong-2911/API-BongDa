<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class ForgotPasswordController extends Controller
{
    // 1. Gửi mã OTP
    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email|exists:users,email'], [
            'email.exists' => 'Email này chưa có tài khoản ní ơi!'
        ]);

        $otp = rand(100000, 999999); // Tạo mã 6 số

        DB::table('password_reset_otps')->updateOrInsert(
            ['email' => $request->email],
            ['otp' => $otp, 'created_at' => now()]
        );

        // Gửi Mail thật bằng SMTP Google ní vừa cấu hình
        Mail::raw("Mã xác nhận của ní là: $otp. Đừng chia sẻ cho ai nhé!", function ($message) use ($request) {
            $message->to($request->email)->subject('MÃ XÁC NHẬN ĐỔI MẬT KHẨU - WESPORT');
        });

        return response()->json(['success' => true, 'message' => 'Mã OTP đã gửi vào mail ní rồi đó!']);
    }

    // 2. Xác thực OTP & Đổi mật khẩu
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.min' => 'Mật khẩu mới ít nhất 8 ký tự nha.',
            'password.confirmed' => 'Mật khẩu nhập lại không khớp kìa ní!'
        ]);

        $reset = DB::table('password_reset_otps')
            ->where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$reset || now()->diffInMinutes($reset->created_at) > 10) {
            return response()->json(['success' => false, 'message' => 'Mã OTP sai hoặc hết hạn (10p) rồi!'], 422);
        }

        // Cập nhật mật khẩu thật cho User
        User::where('email', $request->email)->update([
            'password' => Hash::make($request->password)
        ]);

        // Xóa mã OTP cho sạch DB
        DB::table('password_reset_otps')->where('email', $request->email)->delete();

        return response()->json(['success' => true, 'message' => 'Đổi mật khẩu thành công rực rỡ!']);
    }
}
