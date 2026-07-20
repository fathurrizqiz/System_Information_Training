<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluasiDetailInternal extends Model
{
    protected $table = 'evaluasi_detail_internal';
    protected $fillable = [
        'detail_id',
        'evaluasimateri',
        'evaluasipengajar',
        'sentimen_materi', // Tambahkan ini
        'sentimen_pengajar' // Tambahkan ini
    ];
    public function detail()
    {
        return $this->belongsTo(DetailInternal::class, 'detail_id');
    }

    
}
