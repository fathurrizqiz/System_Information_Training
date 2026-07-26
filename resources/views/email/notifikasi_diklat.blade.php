<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            padding: 20px;
        }

        .card {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            padding: 25px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .header {
            background: linear-gradient(to right,
                    #2563eb,
                    /* from-blue-600 */
                    #06b6d4,
                    /* via-cyan-500 */
                    #34d399
                    /* to-emerald-400 */
                );
            color: #ffffff;
            padding: 15px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }

        .content {
            margin-top: 20px;
        }

        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="header">
            <img src="{{ url('diklat_template/logo_sistem/icon_baru.png') }}" alt="">
            <h2>Notifikasi Diklat</h2>
        </div>
        <div class="content">
            <p>Halo, <strong>{{ $karyawan->nama }}</strong>!</p>

            <p>{!! nl2br(e($pesanTemplate)) !!}</p>

            <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

            <h3>Detail Jadwal:</h3>
            <ul>
                <li><strong>Materi Diklat:</strong> {{ $jadwal->diklat ?? 'Diklat' }}</li>
                <li><strong>Tanggal:</strong> {{ $tanggal }}</li>
                <li><strong>Lokasi:</strong> {{ $jadwal->tempat ?? 'Buka eichar-diklat.my.id' }}</li>
            </ul>
        </div>
        <div class="footer">
            <p>Email ini dikirim otomatis oleh Sistem Diklat. Mohon tidak membalas email ini.</p>
        </div>
    </div>
</body>

</html>
