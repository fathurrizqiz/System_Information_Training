<?php

namespace App\Traits;

trait CustomSoftDelete
{
    /**
     * Scope untuk mendapatkan data yang aktif (tidak dihapus)
     */
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }

    /**
     * Scope untuk mendapatkan data yang sudah dihapus (recycle bin)
     */
    public function scopeIsDeleted($query)
    {
        return $query->where('is_deleted', true);
    }

    /**
     * Scope untuk mendapatkan semua data (termasuk yang dihapus)
     */
    public function scopeWithTrashed($query)
    {
        return $query->withoutGlobalScope();
    }

    /**
     * Soft delete record
     */
    public function softDelete($deletedBy = null)
    {
        $this->update([
            'is_deleted' => true,
            'deleted_at' => now(),
            'deleted_by' => $deletedBy
        ]);
        
        return $this;
    }

    /**
     * Restore record dari recycle bin
     */
    public function restoreFromTrash()
    {
        $this->update([
            'is_deleted' => false,
            'deleted_at' => null,
            'deleted_by' => null
        ]);
        
        return $this;
    }

    /**
     * Force delete (hapus permanen)
     */
    public function forceDelete()
    {
        return $this->delete();
    }
}