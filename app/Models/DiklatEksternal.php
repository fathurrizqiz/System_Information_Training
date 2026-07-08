<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiklatEksternal extends Model
{
    protected $table = 'diklat_eksternal';
    protected $fillable = [
        'program_id',
        'nama_karyawan',
        'nrp',
        'tanggal_mulai',
        'tanggal_selesai',
        'jam_diklat',
        'penyelenggara',
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
        return $this->hasMany(EksternalAbsenModel::class, 'diklat_eksternal_id');
    }
    public function kehadiranHariIni()
    {
        return $this->hasOne(EksternalAbsenModel::class,'diklat_eksternal_id')->whereDate('tanggal', now());
    }
    public function program()
    {
        return $this->belongsTo(ProgramEksternal::class, 'program_id');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawans::class, 'nrp', 'nrp');
    }
    protected $appends = ['nama_diklat'];

    public function getNamaDiklatAttribute()
    {
        return $this->program->nama_diklat ?? null;
    }

}
