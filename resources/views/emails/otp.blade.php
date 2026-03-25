<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .container {
            font-family: 'Segoe UI', Helvetica, Arial, sans-serif;
            max-width: 500px;
            margin: 20px auto;
            border: 1px solid #f1f5f9;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .header {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
            position: relative;
        }

        .header h1 {
            margin: 0;
            font-style: italic;
            font-size: 28px;
            font-weight: 900;
            letter-spacing: -1px;
            text-transform: uppercase;
        }

        .header p {
            margin: 5px 0 0;
            font-size: 12px;
            opacity: 0.9;
            letter-spacing: 2px;
            font-weight: 600;
        }

        .content {
            padding: 40px 30px;
            text-align: center;
            background: #ffffff;
        }

        .content h2 {
            color: #0f172a;
            font-size: 20px;
            margin-bottom: 10px;
            font-weight: 800;
        }

        .content p {
            color: #475569;
            line-height: 1.6;
            font-size: 15px;
            margin-bottom: 30px;
        }

        .otp-box {
            position: relative;
            display: inline-block;
            margin: 10px 0;
        }

        .otp-code {
            font-size: 42px;
            font-weight: 900;
            color: #059669;
            letter-spacing: 10px;
            padding: 20px 40px;
            background: #f0fdf4;
            border-radius: 20px;
            border: 2px solid #d1fae5;
            text-shadow: 0 2px 4px rgba(5, 150, 105, 0.1);
        }

        .timer {
            margin-top: 25px;
            padding: 8px 16px;
            background: #fef2f2;
            color: #dc2626;
            display: inline-block;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .footer {
            padding: 30px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            background: #f8fafc;
            border-top: 1px solid #f1f5f9;
            line-height: 1.8;
        }

        .social-links {
            margin-bottom: 15px;
            color: #64748b;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>WESPORT PLATINUM</h1>
            <p>SOCCER MANAGEMENT SYSTEM</p>
        </div>
        <div class="content">
            <h2>Xác thực thay đổi mật khẩu</h2>
            <p>Chào ní! Chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản của ní. Vui lòng sử dụng mã bảo
                mật bên dưới để hoàn tất:</p>

            <div class="otp-box">
                <div class="otp-code">{{ $otp }}</div>
            </div>

            <br>
            <div class="timer">Hiệu lực trong 10 phút</div>

            <p style="margin-top: 30px; font-size: 13px; color: #94a3b8;">Nếu ní không thực hiện yêu cầu này, vui lòng bỏ
                qua email hoặc liên hệ CSKH để được hỗ trợ.</p>
        </div>
        <div class="footer">
            <div class="social-links">★ ★ ★ ★ ★</div>
            <strong>THANH HOA SOCCER PLATINUM SYSTEM</strong><br>
            📍 123 Stadium Drive, Phan Rang, Ninh Thuận<br>
            © {{ date('Y') }} WESPORT. Nâng tầm trải nghiệm bóng đá phong trào.
        </div>
    </div>
</body>

</html>
