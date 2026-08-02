<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiklatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tanggal_mulai' => $this->tanggal_mulai?->toDateString(),
            'tanggal_selesai' => $this->tanggal_selesai?->toDateString(),
            'nama_diklat' => $this->nama_diklat,
            'pengajar' => $this->pengajar,
            'diklat' => $this->diklat,
            'penyelenggara' => $this->penyelenggara,
            'jam_diklat' => $this->jam_diklat,
            'status' => $this->status,
            'alasan_penolakan' => $this->alasan_penolakan,
            'evaluasimateri' => $this->evaluasimateri,
            'evaluasipengajar' => $this->evaluasipengajar,
            'file_url' => $this->file_path ? asset('storage/'.$this->file_path) : null,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
