<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MateriModel extends Model
{
    protected $table = 'materi_library';
    protected $fillable = [
        'title',
        'file_path',
        'uploaded_by',
        'status',
        'reject_reason',
        'type',
        'parent_id'
    ];

    // Relasi ke parent (folder induk)
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MateriModel::class, 'parent_id');
    }

    // Relasi ke children (isi folder)
    public function children(): HasMany
    {
        return $this->hasMany(MateriModel::class, 'parent_id');
    }

    // Mendapatkan semua item di dalam folder (termasuk sub-folder)
    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }
}
