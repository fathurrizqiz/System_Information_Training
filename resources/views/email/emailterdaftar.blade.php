<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Registrasi Berhasil</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f9; font-family:Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f9; padding:40px 0;">
    <tr>
        <td align="center">

            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,.08);">

                <!-- Header -->
                <tr>
                    <td align="center" style="background:#0d6efd; color:#ffffff; padding:25px;">
                        <h1 style="margin:0;">Sistem Diklat</h1>
                        <p style="margin:8px 0 0 0;">Notifikasi Registrasi</p>
                    </td>
                </tr>

                <!-- Body -->
                <tr>
                    <td style="padding:35px; color:#333333;">

                        <h2 style="margin-top:0;">
                            Halo, {{ $data->nama }} 
                        </h2>

                        <p style="line-height:1.8;">
                            Selamat! Data Anda telah berhasil didaftarkan ke dalam
                            <strong>Sistem Diklat</strong>.
                        </p>

                        <table width="100%" cellpadding="8" cellspacing="0" style="margin:25px 0; border-collapse:collapse;">
                            <tr style="background:#f8f9fa;">
                                <td width="35%"><strong>Nama</strong></td>
                                <td>{{ $data->nama }}</td>
                            </tr>

                            <tr>
                                <td><strong>NRP</strong></td>
                                <td>{{ $data->nrp ?? '-' }}</td>
                            </tr>

                            <tr style="background:#f8f9fa;">
                                <td><strong>Bagian</strong></td>
                                <td>{{ $data->bagian ?? '-' }}</td>
                            </tr>

                            <tr>
                                <td><strong>Email</strong></td>
                                <td>{{ $data->email }}</td>
                            </tr>
                        </table>

                        <div style="background:#e8f5e9; border-left:5px solid #28a745; padding:15px; border-radius:5px;">
                             Registrasi berhasil dilakukan.
                        </div>

                        <p style="margin-top:30px; line-height:1.8;">
                            Silakan simpan email ini sebagai bukti bahwa data Anda telah berhasil terdaftar.
                        </p>

                        <p>
                            Terima kasih.<br>
                            <strong>Admin Sistem Diklat</strong>
                        </p>

                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td align="center" style="background:#f8f9fa; color:#777777; padding:18px; font-size:13px;">
                        © {{ date('Y') }} Sistem Diklat<br>
                        Email ini dikirim secara otomatis, mohon tidak membalas email ini.
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>