<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>

    <!-- ============================================== -->
    <!-- BAGIAN 1: PROGRAM DIKLAT EKSTERNAL             -->
    <!-- ============================================== -->
    <table>
        <tr>
            <td colspan="8" style="font-weight: bold; font-size: 14px; text-align: center;">
                REKAPITULASI PROGRAM DIKLAT EKSTERNAL
            </td>
        </tr>
    </table>

    @foreach($dataEksternal as $program)
        <!-- Cek apakah program ini memiliki peserta/detail -->
        @if($program->eksternal->count() > 0)
            <table>
                <tr>
                    <td colspan="8" style="font-weight: bold; background-color: #d1d5db;">
                        Program: {{ $program->nama_diklat }} (Tahun: {{ $program->tahun }})
                    </td>
                </tr>
                <tr>
                    <th style="font-weight: bold; background-color: #f3f4f6; border: 1px solid #000;">No</th>
                    <th style="font-weight: bold; background-color: #f3f4f6; border: 1px solid #000;">NRP</th>
                    <th style="font-weight: bold; background-color: #f3f4f6; border: 1px solid #000;">Nama Karyawan</th>
                    <th style="font-weight: bold; background-color: #f3f4f6; border: 1px solid #000;">Tgl Mulai</th>
                    <th style="font-weight: bold; background-color: #f3f4f6; border: 1px solid #000;">Tgl Selesai</th>
                    <th style="font-weight: bold; background-color: #f3f4f6; border: 1px solid #000;">Jam</th>
                    <th style="font-weight: bold; background-color: #f3f4f6; border: 1px solid #000;">Penyelenggara</th>
                    <th style="font-weight: bold; background-color: #f3f4f6; border: 1px solid #000;">Status</th>
                </tr>
                
                @foreach($program->eksternal as $index => $detail)
                    <tr>
                        <td style="border: 1px solid #000;">{{ $index + 1 }}</td>
                        <td style="border: 1px solid #000;">{{ $detail->nrp }}</td>
                        <td style="border: 1px solid #000;">{{ $detail->karyawan->nama_karyawan ?? $detail->nama_karyawan }}</td>
                        <td style="border: 1px solid #000;">{{ $detail->tanggal_mulai }}</td>
                        <td style="border: 1px solid #000;">{{ $detail->tanggal_selesai }}</td>
                        <td style="border: 1px solid #000;">{{ $detail->jam_diklat }}</td>
                        <td style="border: 1px solid #000;">{{ $detail->penyelenggara }}</td>
                        <td style="border: 1px solid #000;">{{ $detail->status }}</td>
                    </tr>
                @endforeach
            </table>
            <!-- Spasi antar program -->
            <table><tr><td></td></tr></table>
        @endif
    @endforeach

    <!-- Jarak pemisah antara Eksternal dan HLC -->
    <table>
        <tr><td colspan="8"></td></tr>
        <tr><td colspan="8"></td></tr>
    </table>


    

</body>
</html>