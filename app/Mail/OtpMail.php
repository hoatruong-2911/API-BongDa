<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp; // ✅ Khai báo biến otp

    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    public function build()
    {
        return $this->subject('MÃ XÁC NHẬN ĐỔI MẬT KHẨU - WESPORT')
            ->view('emails.otp'); // ✅ Chỉ định file HTML vừa tạo
    }
}
