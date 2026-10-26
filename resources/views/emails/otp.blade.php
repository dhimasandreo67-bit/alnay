<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Kode OTP</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f8f3e8; padding: 40px; margin: 0;">
    <div style="max-width: 480px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 32px; box-shadow: 0 4px 20px rgba(23,20,15,0.08);">
        <div style="text-align: center; margin-bottom: 24px;">
            <span style="display: inline-block; width: 48px; height: 48px; background: #B8862B; color: #17140F; border-radius: 8px; font-weight: bold; font-size: 24px; line-height: 48px;">A</span>
            <p style="font-family: Georgia, serif; font-weight: bold; color: #B8862B; margin: 8px 0 0; letter-spacing: 0.1em;">ALNAY.ID</p>
        </div>

        <h2 style="color: #17140F; font-size: 20px; margin: 0 0 8px;">Kode OTP Anda</h2>
        <p style="color: #666; font-size: 14px; line-height: 1.6; margin: 0 0 24px;">
            Masukkan kode berikut untuk melanjutkan:
        </p>

        <div style="background: #f8f3e8; border: 1px solid #eee5d2; border-radius: 8px; padding: 20px; text-align: center; margin-bottom: 24px;">
            <p style="font-family: monospace; font-size: 32px; font-weight: bold; letter-spacing: 0.3em; color: #17140F; margin: 0;">
                {{ $code }}
            </p>
        </div>

        <p style="color: #999; font-size: 12px; line-height: 1.6; margin: 0;">
            Kode berlaku 5 menit. Jika Anda tidak meminta kode ini, abaikan email ini.
        </p>
    </div>
</body>
</html>
