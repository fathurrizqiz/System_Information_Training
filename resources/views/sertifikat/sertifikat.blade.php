<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Sertifikat Diklat</title>
    <style>
        @font-face {
            font-family: 'ScriptMTBold';
            src: url('{{ $fonts['ScriptMTBold']['mime'] }};base64,{{ $fonts['ScriptMTBold']['base64'] }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
    font-family: 'ArialBoldMT';
    /* Gunakan format yang sesuai dengan file aslinya */
    src: url('{{ $fonts['ARIALBOLDMT']['mime'] }};base64,{{ $fonts['ARIALBOLDMT']['base64'] }}') 
         format('{{ Str::contains($fonts['ARIALBOLDMT']['mime'], "otf") ? "opentype" : "truetype" }}');
    font-weight: bold;
    font-style: normal;
}

@font-face {
    font-family: 'ArialLight';
    /* Karena di controller Anda menggunakan ARIALBOLDMT untuk ArialLight juga, kita samakan */
    src: url('{{ $fonts['ARIALBOLDMT']['mime'] }};base64,{{ $fonts['ARIALBOLDMT']['base64'] }}') 
         format('{{ Str::contains($fonts['ARIALBOLDMT']['mime'], "otf") ? "opentype" : "truetype" }}');
    font-weight: normal;
    font-style: normal;
}

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
        }

        .page {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: auto;
        }

        /* Background simulasi */
        .bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
        }

        /* PAGE 1 CONTENT */
        .logo {
            position: absolute;
            top: 22%;
            left: 9.4%;
            z-index: 1;
        }

        .logo img {
            width: 90px;
            height: auto;
        }

        .text-sertifikat {
            font-family: 'ScriptMTBold', cursive;
            font-size: 130px;
            position: absolute;
            top: 2%;
            left: 50%;
            transform: translateX(-50%);
            color: #fff;
            white-space: nowrap;
            z-index: 1;
        }

        .p2 {
            font-family: 'ArialBoldMT', Arial, sans-serif;
            font-size: 20px;
            position: absolute;
            top: 36.8%;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
            z-index: 1;
        }

        .text-nama {
            font-family: 'ScriptMTBold', cursive;
            font-size: 55px;
            position: absolute;
            top: 40%;
            left: 50%;
            transform: translateX(-50%);
            color: #000;
            white-space: nowrap;
            z-index: 1;
        }

        .p3 {
            font-family: 'ArialBoldMT', Arial, sans-serif;
            font-weight: bold;
            font-size: 16px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
            z-index: 1;
        }

        .nama_diklat {
            font-family: 'ArialBoldMT', Arial, sans-serif;
            font-weight: bold;
            font-size: 25px;
            position: absolute;
            top: 57%;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
            z-index: 1;
        }

        .tanggal {
            font-family: 'ArialBoldMT', Arial, sans-serif;
            font-weight: bold;
            font-size: 18px;
            position: absolute;
            top: 60%;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
            z-index: 1;
        }

        .p4 {
            font-family: 'ArialBoldMT', Arial, sans-serif;
            font-weight: bold;
            font-size: 16px;
            position: absolute;
            top: 71%;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
            z-index: 1;
        }

        .ttd {
            position: absolute;
            top: 80%;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
            z-index: 1;
        }

        .ttd img {
            width: 120px;
            height: auto;
        }

        .p5 {
            font-family: 'ArialBoldMT', Arial, sans-serif;
            font-weight: bold;
            font-size: 16px;
            position: absolute;
            top: 88%;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
            z-index: 1;
        }

        /* PAGE 2 */
        .nama_diklat2 {
            font-family: 'ArialBoldMT', Arial, sans-serif;
            font-weight: bold;
            font-size: 25px;
            position: absolute;
            top: 6%;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            text-align: center;
            white-space: nowrap;
            z-index: 1;
        }

        .table-wrapper {
            position: absolute;
            top: 13%;
            width: 90%;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 16px;
        }

        th, td {
            border: 1px solid #000;
            padding: 8px;
        }

        th {
            font-family: 'ArialBoldMT', Arial, sans-serif;
            font-weight: bold;
            text-align: center;
            font-size: 16px;
        }

        td {
            font-family: 'ArialLight', Arial, sans-serif;
            font-size: 16px;
        }

        p {
            margin: 0;
        }
    </style>
</head>

<body>

    {{-- PAGE 1 --}}
    <div class="page">
        <img src="data:image/png;base64,{{ $assets['bg1'] }}" class="bg" alt="">

        <div class="logo">
            <img src="data:image/png;base64,{{ $assets['logo'] }}" alt="Logo" />
        </div>

        <div class="text-sertifikat">Sertifikat</div>

        <div class="p2"><p>Diberikan kepada:</p></div>

        <div class="text-nama">{{ $nama }}</div>

        <div class="p3"><p>Sebagai Peserta pada Diklat:</p></div>

        <div class="nama_diklat">{{ $nama_diklat }}</div>

        <div class="tanggal"><p>Pada Tanggal {{ $tanggal }}</p></div>

        <div class="p4"><p>Direktur RS Hermina Daan Mogot</p></div>

        <div class="ttd">
            <img src="data:image/png;base64,{{ $assets['ttd'] }}" alt="Tanda Tangan" />
        </div>

        <div class="p5"><p>{{ $direktur }}</p></div>
    </div>

    {{-- PAGE 2+ --}}
    @php
        $rowsPerPage = 12;
        $materiChunks = array_chunk($materi, $rowsPerPage);
    @endphp

    @foreach ($materiChunks as $chunk)
        <div class="page">
            <img src="data:image/png;base64,{{ $assets['bg2'] }}" class="bg" alt="">

            <p class="nama_diklat2">{{ $nama_diklat }}</p>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th style="width:10%;">NO</th>
                            <th>MATERI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($chunk as $m)
                            <tr>
                                <td style="text-align:center;">{{ $m['no'] }}</td>
                                <td>{{ $m['materi'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

</body>

</html>