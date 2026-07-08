<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HLCManajement extends Model
{
    protected $table = 'diklat_hlc';
    protected $fillable = [
        'program_id',
        'nama_diklat',
        'pengajar',
        'penyelenggara',
        'diklat',
        'tanggal_mulai',
        'tanggal_selesai',
        'nrp',
        'jam_diklat',
        'status',
        'dokumen',
        'bukti_hadir',
        'status_verifikasi',
        'uploaded_at',
        'catatan_verifikasi',
        'catatan_penolakan',
    ];

    public function kehadiran()
    {
        return $this->hasMany(HLCAbsenModel::class, 'diklat_hlc_id');
    }

    public function kehadiranHariIni()
    {
        return $this->hasOne(HLCAbsenModel::class,'diklat_hlc_id')->whereDate('tanggal', now());
    }
    public function hlc()
    {
        return $this->belongsTo(ProgramHlc::class, 'program_id');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawans::class, 'nrp', 'nrp');
    }

}
