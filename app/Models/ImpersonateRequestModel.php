<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImpersonateRequestModel extends Model
{
    protected $table = 'impersonate_requests';

    protected $fillable = [
        'admin_nrp',
        'target_nrp',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_nrp', 'nrp');
    }

    public function target()
    {
        return $this->belongsTo(User::class, 'target_nrp', 'nrp');
    }

    public function isExpired(): bool
    {
        return now()->isAfter($this->expires_at);
    }
}
