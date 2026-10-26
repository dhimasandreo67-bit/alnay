<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP ALNAY.ID</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f8f3e8; padding: 40px 10px; margin: 0;">
    
    <!-- Preheader Text (Muncul di preview inbox) -->
    <div style="display: none; max-height: 0; overflow: hidden;">
        Kode OTP Anda adalah {{ $code }}. Berlaku selama 5 menit.
    </div>

    <!-- Wrapper Table untuk Layout -->
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td align="center">
                <!-- Main Card Table -->
                <table width="100%" max-width="480" cellpadding="0" cellspacing="0" border="0" style="max-width: 480px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 20px rgba(23,20,15,0.08);">
                    <tr>
                        <td style="padding: 32px;">
                            
                            <!-- Header -->
                            <div style="text-align: center; margin-bottom: 24px;">
                                <span style="display: inline-block; width: 48px; height: 48px; background-color: #B8862B; color: #17140F; border-radius: 8px; font-weight: bold; font-size: 24px; line-height: 48px;">A</span>
                                <p style="font-family: Georgia, serif; font-weight: bold; color: #B8862B; margin: 8px 0 0; letter-spacing: 0.1em; font-size: 16px;">ALNAY.ID</p>
                            </div>

                            <!-- Content -->
                            <h2 style="color: #17140F; font-size: 20px; margin: 0 0 8px; font-family: Arial, sans-serif;">Kode OTP Anda</h2>
                            <p style="color: #666666; font-size: 14px; line-height: 1.6; margin: 0 0 24px; font-family: Arial, sans-serif;">
                                Masukkan kode berikut untuk melanjutkan:
                            </p>

                            <!-- OTP Box -->
                            <div style="background-color: #f8f3e8; border: 1px solid #eee5d2; border-radius: 8px; padding: 20px; text-align: center; margin-bottom: 24px;">
                                <p style="font-family: Georgia, serif; font-size: 36px; font-weight: bold; letter-spacing: 0.2em; color: #17140F; margin: 0; padding-left: 0.2em;">
                                    {{ $code }}
                                </p>
                            </div>

                            <!-- Footer / Disclaimer -->
                            <p style="color: #999999; font-size: 12px; line-height: 1.6; margin: 0; font-family: Arial, sans-serif; text-align: center;">
                                Kode berlaku <strong>5 menit</strong>.<br>Jika Anda tidak meminta kode ini, abaikan email ini.
                            </p>

                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>