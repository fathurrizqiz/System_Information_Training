<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternalMeetModels extends Model
{
    protected $table = 'internal_meetings';

    protected $fillable = [
        'periode_id',
        'link_zoom',
    ];

    public function periode()
    {
        return $this->belongsTo(PeriodeUtama::class, 'periode_id');
    }
}
