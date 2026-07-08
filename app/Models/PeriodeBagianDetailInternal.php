<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodeBagianDetailInternal extends Model
{
    protected $table = 'periode_bagian_detail_internal';

    protected $fillable = [
        'detail_program_id',
        'periode_id',
        'nama_karyawan',
        'tmt',
        'nrp',
        'bagian',
        'unit_kerja',
        'posisi_jabatan',
        'klinis_non_klinis',
        'jenis_kelamin',
        'hadir_at',
        'post_done_at',
        'pree_done_at',
        'sertifikat_path',
        'sertifikat_generated_at',
    ];
    public function periode()
    {
        return $this->belongsTo(PeriodeUtama::class, 'periode_id');
    }

    public function presensi()
    {
        // Hubungkan berdasarkan detail_program_id
        return $this->hasOne(PresensiDetailDiklat::class, 'detail_program_id', 'detail_program_id');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawans::class, 'nrp', 'nrp');
    }


    public function detailProgram()
    {
        return $this->belongsTo(
            DetailInternal::class,
            'detail_program_id'
        );
    }
    public function aksi()
    {
        return $this->belongsTo(AksiDetailInternal::class, 'detail_program_id');
    }


}
