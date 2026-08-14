<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <!-- ============================================== -->
    <!-- BAGIAN 2: PROGRAM DIKLAT HLC                   -->
    <!-- ============================================== -->
    <table>
        <tr>
            <td colspan="8" style="font-weight: bold; font-size: 14px; text-align: center;">
                REKAPITULASI PROGRAM DIKLAT HLC
            </td>
        </tr>
    </table>

    @foreach($dataHlc as $programHlc)
        @if($programHlc->hlc->count() > 0)
            <table>
                <tr>
                    <td colspan="8" style="font-weight: bold; background-color: #d1d5db;">
                        Program: {{ $programHlc->nama_program }} (Tahun: {{ $programHlc->tahun }})
                    </td>
                </tr>
                <tr>
                    <th style="font-weight: bold; background-color: #f3f4f6; border: 1px solid #000;">No</th>
                    <th style="font-weight: bold; background-color: #f3f4f6; border: 1px solid #000;">NRP</th>
                    <th style="font-weight: bold; background-color: #f3f4f6; border: 1px solid #000;">Nama Karyawan</th>
                    <th style="font-weight: bold; background-color: #f3f4f6; border: 1px solid #000;">Tgl Mulai</th>
                    <th style="font-weight: bold; background-color: #f3f4f6; border: 1px solid #000;">Tgl Selesai</th>
                    <th style="font-weight: bold; background-color: #f3f4f6; border: 1px solid #000;">Jam</th>
                    <th style="font-weight: bold; background-color: #f3f4f6; border: 1px solid #000;">Pengajar / Penyelenggara</th>
                    <th style="font-weight: bold; background-color: #f3f4f6; border: 1px solid #000;">Status</th>
                </tr>
                
                @foreach($programHlc->hlc as $idx => $detailHlc)
                    <tr>
                        <td style="border: 1px solid #000;">{{ $idx + 1 }}</td>
                        <td style="border: 1px solid #000;">{{ $detailHlc->nrp }}</td>
                        <td style="border: 1px solid #000;">{{ $detailHlc->karyawan->nama_karyawan ?? '-' }}</td>
                        <td style="border: 1px solid #000;">{{ $detailHlc->tanggal_mulai }}</td>
                        <td style="border: 1px solid #000;">{{ $detailHlc->tanggal_selesai }}</td>
                        <td style="border: 1px solid #000;">{{ $detailHlc->jam_diklat }}</td>
                        <td style="border: 1px solid #000;">{{ $detailHlc->pengajar ?? $detailHlc->penyelenggara }}</td>
                        <td style="border: 1px solid #000;">{{ $detailHlc->status }}</td>
                    </tr>
                @endforeach
            </table>
            <table><tr><td></td></tr></table>
        @endif
    @endforeach
</body>
</html>