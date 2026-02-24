<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background-color: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { font-size: 24px; margin-bottom: 5px; }
        .header p { font-size: 14px; opacity: 0.9; }
        .content { padding: 40px 30px; }
        .greeting { font-size: 18px; color: #1f2937; margin-bottom: 20px; }
        .message { color: #4b5563; line-height: 1.6; margin-bottom: 25px; }
        .otp-container { background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 3px dashed #2563eb; border-radius: 12px; padding: 30px; text-align: center; margin: 30px 0; }
        .otp-code { font-size: 42px; font-weight: bold; color: #1e40af; letter-spacing: 8px; font-family: 'Courier New', monospace; }
        .otp-label { font-size: 14px; color: #6b7280; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 2px; }
        .info-box { background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 25px 0; border-radius: 4px; }
        .info-box strong { color: #92400e; }
        .warning { background-color: #fee2e2; border-left: 4px solid #ef4444; padding: 15px; margin: 25px 0; border-radius: 4px; font-size: 14px; color: #991b1b; }
        .footer { background-color: #f9fafb; padding: 25px 30px; text-align: center; border-top: 1px solid #e5e7eb; }
        .footer p { color: #6b7280; font-size: 13px; line-height: 1.6; }
        .footer .copyright { margin-top: 10px; font-weight: 600; color: #4b5563; }
        @media only screen and (max-width: 600px) {
            .container { border-radius: 0; }
            .otp-code { font-size: 32px; letter-spacing: 5px; }
            .content { padding: 30px 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Politeknik Negeri Jember</h1>
            <p>Sistem Peminjaman Laboratorium PSDKU Nganjuk</p>
        </div>

        <div class="content">
            <div class="greeting">Halo, {{ $userName ?: 'Pengguna' }}!</div>

            <div class="message">
                Anda menerima email ini karena ada permintaan <strong>reset password</strong> untuk akun Anda.
                Gunakan kode OTP berikut untuk melanjutkan proses reset password:
            </div>

            <div class="otp-container">
                <div class="otp-label">Kode OTP Anda</div>
                <div class="otp-code">{{ $otp }}</div>
            </div>

            <div class="info-box">
                <strong>⏰ Informasi Penting:</strong><br>
                • Kode OTP berlaku selama <strong>{{ $expiresIn }} menit</strong><br>
                • Jangan berikan kode ini kepada siapapun<br>
                • Kode hanya bisa digunakan satu kali
            </div>

            <div class="warning">
                <strong>⚠️ Keamanan:</strong><br>
                Jika Anda <strong>TIDAK</strong> meminta reset password, abaikan email ini.
                Password Anda akan tetap aman.
            </div>
        </div>

        <div class="footer">
            <p>
                Email ini dikirim secara otomatis. Mohon untuk tidak membalas email ini.<br>
                Jika Anda membutuhkan bantuan, hubungi tim IT Support Polije.<br>
                <strong>Layanan ini hanya untuk email domain Polije (@student.polije.ac.id / @polije.ac.id)</strong>
            </p>
            <p class="copyright">
                © {{ date('Y') }} Politeknik Negeri Jember<br>
                <em>Sistem Informasi Akademik</em>
            </p>
        </div>
    </div>
</body>
</html>
