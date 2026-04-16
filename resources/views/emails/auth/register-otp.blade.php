<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email</title>
</head>
<body style="margin:0; padding:24px; background:#f5f7fb; font-family:Arial, sans-serif; color:#1f2937;">
    <div style="max-width:560px; margin:0 auto; background:#ffffff; border-radius:20px; overflow:hidden; box-shadow:0 18px 40px rgba(15, 23, 42, 0.08);">
        <div style="padding:32px; background:linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%); color:#ffffff;">
            @if ($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $appName }}" style="height:40px; width:auto; display:block; margin-bottom:16px;">
            @endif

            <div style="font-size:14px; letter-spacing:0.12em; text-transform:uppercase; opacity:0.8;">{{ $appName }}</div>
            <h1 style="margin:12px 0 0; font-size:28px; line-height:1.2;">Verifikasi Email Pendaftaran</h1>
        </div>

        <div style="padding:32px;">
            <p style="margin:0 0 16px; font-size:16px;">Halo,</p>
            <p style="margin:0 0 20px; font-size:15px; line-height:1.7;">
                Gunakan kode OTP berikut untuk memverifikasi alamat email <strong>{{ $recipientEmail }}</strong> dan menyelesaikan proses pendaftaran akun Anda.
            </p>

            <div style="margin:0 0 20px; padding:18px 24px; border:1px dashed #93c5fd; border-radius:16px; background:#eff6ff; text-align:center;">
                <div style="font-size:13px; text-transform:uppercase; letter-spacing:0.14em; color:#2563eb; margin-bottom:10px;">Kode OTP</div>
                <div style="font-size:32px; font-weight:700; letter-spacing:0.32em; color:#0f172a;">{{ $code }}</div>
            </div>

            <p style="margin:0 0 8px; font-size:14px; line-height:1.7; color:#4b5563;">
                Kode ini berlaku selama <strong>{{ $expiresInMinutes }} menit</strong>.
            </p>
            <p style="margin:0; font-size:14px; line-height:1.7; color:#4b5563;">
                Jika Anda tidak merasa melakukan pendaftaran, abaikan email ini.
            </p>
        </div>
    </div>
</body>
</html>
