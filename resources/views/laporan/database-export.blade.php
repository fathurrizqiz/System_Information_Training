<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan</title>
</head>
<body>
    <table>
    <thead>
        <tr>
            <!-- Urutan Kolom Sesuai Gambar -->
            <th>Bagian</th>
            <th>Unit Kerja</th>
            <th>Nama</th>
            <th>Pelatihan</th>
            <th>Tanggal</th>
            <th>Durasi</th>
            <th>Sumber</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
            <tr>
                <td>{{ $row['bagian'] }}</td>
                <td>{{ $row['unit_kerja'] }}</td>
                <td>{{ $row['nama_karyawan'] }}</td>
                <td>{{ $row['pelatihan'] }}</td>
                <td>{{ $row['tanggal'] }}</td>
                <td>{{ $row['durasi'] }}</td>
                <td>{{ $row['sumber'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>