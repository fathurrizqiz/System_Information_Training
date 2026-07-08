<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestToken extends Model
{
    protected $table = 'token_internal';
    protected $fillable = [
        'periode_id',
        'token',
        'type',
        'expires_at',
        'is_used',
    ];

    public function periode()
    {
        return $this->belongsTo(PeriodeUtama::class, 'periode_id');
    }

    // Token masih valid?
    public function isValid()
    {
        if ($this->is_used) return false;
        if ($this->expires_at && now()->greaterThan($this->expires_at)) return false;

        return true;
    }
}
