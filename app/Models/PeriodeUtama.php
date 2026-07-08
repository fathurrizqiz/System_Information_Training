<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodeUtama extends Model
{
    protected $table = 'periode_detail_internal';
    protected $fillable = [
        'detail_id',
        'tanggal',
        'nama_pengajar',
        'tempat'
    ];

    public function peserta()
    {
        return $this->hasMany(PeriodeBagianDetailInternal::class, 'periode_id');
    }


    public function detailProgram()
    {
        return $this->belongsTo(
            DetailInternal::class,
            'detail_program_id'
        );
    }

    public function detail()
    {
        return $this->belongsTo(DetailInternal::class, 'detail_id');
    }

    public function pembahasan()
    {
        return $this->hasMany(TemplatePembahasanSertifikat::class, 'periode_id');
    }

    public function detailBagian()
    {
        return $this->hasMany(PeriodeBagianDetailInternal::class, 'periode_id');
    }
    // PeriodeUtama.php

    // datamaster
    public function aksi()
    {
        // Karena satu periode biasanya punya satu setting aksi/jam diklat
        return $this->hasOne(AksiDetailInternal::class, 'periode_id');
    }


}
