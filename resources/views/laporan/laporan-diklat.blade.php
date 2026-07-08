<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excel</title>
</head>
<body>
    <table>
    <thead>
        <tr>
            <th colspan="15" style="font-weight: bold; text-align: center; font-size: 14pt;">
                Database Diklat Bulan {{ $namaBulan }}
            </th>
        </tr>
        <tr>
            <th>NO</th>
            <th>TIMESTAMP</th>
            <th>NAMA KARYAWAN</th>
            <th>TMT</th>
            <th>NRP</th>
            <th>BAGIAN</th>
            <th>UNIT KERJA</th>
            <th>POSISI JABATAN</th>
            <th>KLINIS/NON KLINIS</th>
            <th>JENIS KELAMIN</th>
            <th>JUDUL DIKLAT</th>
            <th>TANGGAL PELAKSANAAN DIKLAT</th>
            <th>TEMPAT DIKLAT</th>
            <th>JAM DIKLAT</th>
            <th>NAMA PENGAJAR</th>
        </tr>
    </thead>
    <tbody>
        @php $no = 1; @endphp
        @foreach($data as $karyawan)
            @foreach($karyawan->rekam_jejak as $diklat)
            <tr>
                <td>{{ $no++ }}</td>
                <td>{{ now()->format('Y-m-d H:i:s') }}</td> {{-- Timestamp --}}
                <td>{{ $karyawan->nama_karyawan }}</td>
                <td>{{ $karyawan->tmt }}</td>
                <td>{{ $karyawan->nrp }}</td>
                <td>{{ $karyawan->bagian }}</td>
                <td>{{ $karyawan->unit_kerja }}</td>
                <td>{{ $karyawan->posisi_jabatan }}</td>
                <td>{{ $karyawan->klinis_non_klinis }}</td>
                <td>{{ $karyawan->jenis_kelamin }}</td>
                {{-- Data dari Rekam Jejak --}}
                <td>{{ $diklat['nama_diklat'] }}</td>
                <td>{{ $diklat['tanggal'] }}</td>
                <td>{{ $diklat['tempat'] ?? '-' }}</td>
                <td>{{ $diklat['jam'] }}</td>
                <td>{{ $diklat['pengajar'] ?? '-' }}</td>
            </tr>
            @endforeach
        @endforeach
    </tbody>
</table>
</body>
</html>