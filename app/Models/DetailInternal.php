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

    protected $appends = ['summary_sentiment'];
    public function evaluasi()
    {
        return $this->hasMany(EvaluasiDetailInternal::class, 'detail_id');
    }

    public function getSummarySentimentAttribute()
    {
        $positive = 0;
        $negative = 0;
        $neutral = 0;
        $totalComments = 0;

        // Kita hitung langsung dari relasi evaluasis yang sudah dimuat (loaded)
        // Atau bisa pakai query builder jika datanya sangat besar, tapi loop oke untuk skala menengah
        foreach ($this->evaluasi as $ev) {
            
            // Hitung Materi
            if ($ev->sentimen_materi === 'positive') $positive++;
            elseif ($ev->sentimen_materi === 'negative') $negative++;
            elseif ($ev->sentimen_materi === 'neutral') $neutral++;

            // Hitung Pengajar
            if ($ev->sentimen_pengajar === 'positive') $positive++;
            elseif ($ev->sentimen_pengajar === 'negative') $negative++;
            elseif ($ev->sentimen_pengajar === 'neutral') $neutral++;

            if ($ev->sentimen_materi || $ev->sentimen_pengajar) {
                $totalComments++;
            }
        }

        return [
            'positive' => $positive,
            'negative' => $negative,
            'neutral' => $neutral,
            'total_comments' => $totalComments,
            'is_evaluated' => $totalComments > 0
        ];
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
