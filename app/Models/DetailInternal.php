<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailInternal extends Model
{
    protected $table = 'detail_internal';
    protected $fillable = [
        'program_id',
        'nama_diklat',
        'keterangan',
        'pengajar',
    ];

    public function program()
    {
        return $this->belongsTo(PendidikanFormalModels::class, 'program_id');
    }
    public function periodes()
    {
        return $this->hasMany(PeriodeUtama::class, 'detail_id');
    }
    public function evaluasi()
    {
        return $this->hasMany(EvaluasiDetailInternal::class, 'detail_id');
    }

    // App\Models\DetailInternal.php

    public function aksi()
    {
        return $this->hasOneThrough(
            AksiDetailInternal::class,
            PeriodeUtama::class,
            'detail_id',
            'periode_id',
            'id',
            'id'
        )->latest('aksi_detail_internal.created_at'); // Ambil aksi yang paling baru
    }

}
