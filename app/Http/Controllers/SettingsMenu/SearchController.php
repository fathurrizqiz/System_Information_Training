<?php

namespace App\Http\Controllers\SettingsMenu;

use App\Http\Controllers\Controller;
use App\Models\DiklatEksternal;
use App\Models\DiklatKaryawan;
use App\Models\HLCManajement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SearchController extends Controller
{
    public function index()
    {
        return Inertia::render('Jadwal/MenuSettings/Search');
    }

    public function search(Request $request)
    {
        $request->validate([
            'keyword' => 'required|string|min:2',
            'type' => 'required|in:file,sertifikat,pelatihan,program_pelatihan,all',
            'include_trash' => 'boolean',
        ]);

        $keyword = $request->input('keyword');
        $type = $request->input('type');
        $includeTrash = $request->input('include_trash', false);
        $nrp = Auth::user()->nrp;

        $results = [
            'files' => [],
            'sertifikat' => [],
            'pelatihan' => [],
            'program_pelatihan' => [],
        ];

        // Cari berdasarkan tipe
        if ($type === 'file' || $type === 'all') {
            $results['files'] = $this->searchFiles($keyword, $nrp, $includeTrash);
        }

        if ($type === 'sertifikat' || $type === 'all') {
            $results['sertifikat'] = $this->searchSertifikat($keyword, $nrp, $includeTrash);
        }

        if ($type === 'pelatihan' || $type === 'all') {
            $results['pelatihan'] = $this->searchPelatihan($keyword, $nrp, $includeTrash);
        }

        if ($type === 'program_pelatihan' || $type === 'all') {
            $results['program_pelatihan'] = $this->searchProgramPelatihan($keyword, $nrp, $includeTrash);
        }

        return response()->json([
            'success' => true,
            'data' => $results,
            'total' => array_sum(array_map('count', $results)),
        ]);
    }

    private function searchFiles(string $keyword, string $nrp, bool $includeTrash): array
    {
        $query = DiklatKaryawan::where('nrp', $nrp);

        if (!$includeTrash) {
            $query->whereNull('deleted_at');
        }

        $results = $query->where(function ($q) use ($keyword) {
            $q->where('nama_diklat', 'like', "%{$keyword}%")
                ->orWhere('pengajar', 'like', "%{$keyword}%")
                ->orWhere('penyelenggara', 'like', "%{$keyword}%")
                ->orWhere('file_path', 'like', "%{$keyword}%");
        })
            ->select('id', 'nama_diklat', 'file_path', 'tanggal_mulai', 'tanggal_selesai', 'deleted_at')
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->nama_diklat,
                    'file_path' => $item->file_path,
                    'tanggal' => $item->tanggal_mulai?->format('d M Y'),
                    'type' => 'file',
                    'in_trash' => !is_null($item->deleted_at),
                ];
            })
            ->toArray();

        return $results;
    }

    private function searchSertifikat(string $keyword, string $nrp, bool $includeTrash): array
    {
        $results = [];

        // Dari Diklat Eksternal
        $queryExt = DiklatEksternal::where('nrp', $nrp)->whereNotNull('dokumen');

        if (!$includeTrash) {
            $queryExt->whereNull('deleted_at');
        }

        $eksternal = $queryExt->where(function ($q) use ($keyword) {
            $q->whereHas('program', function ($pq) use ($keyword) {
                $pq->where('nama_diklat', 'like', "%{$keyword}%");
            })
                ->orWhere('penyelenggara', 'like', "%{$keyword}%");
        })
            ->with('program')
            ->select('id', 'program_id', 'dokumen', 'tanggal_mulai', 'tanggal_selesai', 'deleted_at')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->program->nama_diklat ?? 'Diklat Eksternal',
                    'dokumen' => $item->dokumen,
                    'tanggal' => $item->tanggal_mulai?->format('d M Y'),
                    'type' => 'sertifikat_eksternal',
                    'in_trash' => !is_null($item->deleted_at),
                ];
            })
            ->toArray();

        // Dari HLC Management
        $queryHlc = HLCManajement::where('nrp', $nrp)->whereNotNull('dokumen');

        if (!$includeTrash) {
            $queryHlc->whereNull('deleted_at');
        }

        $hlc = $queryHlc->where(function ($q) use ($keyword) {
            $q->where('nama_diklat', 'like', "%{$keyword}%")
                ->orWhere('penyelenggara', 'like', "%{$keyword}%");
        })
            ->select('id', 'nama_diklat', 'dokumen', 'tanggal_mulai', 'tanggal_selesai', 'deleted_at')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->nama_diklat,
                    'dokumen' => $item->dokumen,
                    'tanggal' => $item->tanggal_mulai?->format('d M Y'),
                    'type' => 'sertifikat_hlc',
                    'in_trash' => !is_null($item->deleted_at),
                ];
            })
            ->toArray();

        return array_merge($eksternal, $hlc);
    }

    private function searchPelatihan(string $keyword, string $nrp, bool $includeTrash): array
    {
        $results = [];

        // Diklat Karyawan
        $queryKaryawan = DiklatKaryawan::where('nrp', $nrp);

        if (!$includeTrash) {
            $queryKaryawan->whereNull('deleted_at');
        }

        $karyawan = $queryKaryawan->where(function ($q) use ($keyword) {
            $q->where('nama_diklat', 'like', "%{$keyword}%")
                ->orWhere('pengajar', 'like', "%{$keyword}%")
                ->orWhere('penyelenggara', 'like', "%{$keyword}%")
                ->orWhere('diklat', 'like', "%{$keyword}%");
        })
            ->select('id', 'nama_diklat', 'pengajar', 'penyelenggara', 'tanggal_mulai', 'tanggal_selesai', 'jam_diklat', 'status', 'deleted_at')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->nama_diklat,
                    'pengajar' => $item->pengajar,
                    'penyelenggara' => $item->penyelenggara,
                    'tanggal' => $item->tanggal_mulai?->format('d M Y') . ' - ' . $item->tanggal_selesai?->format('d M Y'),
                    'jam_diklat' => $item->jam_diklat,
                    'status' => $item->status,
                    'type' => 'pelatihan_karyawan',
                    'in_trash' => !is_null($item->deleted_at),
                ];
            })
            ->toArray();

        // Diklat Eksternal
        $queryExt = DiklatEksternal::where('nrp', $nrp);

        if (!$includeTrash) {
            $queryExt->whereNull('deleted_at');
        }

        $eksternal = $queryExt->where(function ($q) use ($keyword) {
            $q->whereHas('program', function ($pq) use ($keyword) {
                $pq->where('nama_diklat', 'like', "%{$keyword}%")
                    ->orWhere('penyelenggara', 'like', "%{$keyword}%");
            });
        })
            ->with(['program', 'kehadiranHariIni'])
            ->select('id', 'program_id', 'tanggal_mulai', 'tanggal_selesai', 'jam_diklat', 'status', 'status_verifikasi', 'deleted_at')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->program->nama_diklat ?? 'Diklat Eksternal',
                    'penyelenggara' => $item->penyelenggara,
                    'tanggal' => $item->tanggal_mulai?->format('d M Y') . ' - ' . $item->tanggal_selesai?->format('d M Y'),
                    'jam_diklat' => $item->jam_diklat,
                    'status' => $item->status,
                    'status_verifikasi' => $item->status_verifikasi,
                    'type' => 'pelatihan_eksternal',
                    'in_trash' => !is_null($item->deleted_at),
                ];
            })
            ->toArray();

        // HLC Management
        $queryHlc = HLCManajement::where('nrp', $nrp);

        if (!$includeTrash) {
            $queryHlc->whereNull('deleted_at');
        }

        $hlc = $queryHlc->where(function ($q) use ($keyword) {
            $q->where('nama_diklat', 'like', "%{$keyword}%")
                ->orWhere('pengajar', 'like', "%{$keyword}%")
                ->orWhere('penyelenggara', 'like', "%{$keyword}%");
        })
            ->with('kehadiranHariIni')
            ->select('id', 'nama_diklat', 'pengajar', 'penyelenggara', 'tanggal_mulai', 'tanggal_selesai', 'jam_diklat', 'status', 'status_verifikasi', 'deleted_at')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->nama_diklat,
                    'pengajar' => $item->pengajar,
                    'penyelenggara' => $item->penyelenggara,
                    'tanggal' => $item->tanggal_mulai?->format('d M Y') . ' - ' . $item->tanggal_selesai?->format('d M Y'),
                    'jam_diklat' => $item->jam_diklat,
                    'status' => $item->status,
                    'status_verifikasi' => $item->status_verifikasi,
                    'type' => 'pelatihan_hlc',
                    'in_trash' => !is_null($item->deleted_at),
                ];
            })
            ->toArray();

        return array_merge($karyawan, $eksternal, $hlc);
    }

    private function searchProgramPelatihan(string $keyword, string $nrp, bool $includeTrash): array
    {
        $results = [];

        // Program Eksternal — hanya ada kolom nama_diklat
        $queryExt = \App\Models\ProgramEksternal::where('nama_diklat', 'like', "%{$keyword}%");

        if (!$includeTrash) {
            $queryExt->whereNull('deleted_at');
        }

        $programEksternal = $queryExt->select('id', 'nama_diklat', 'tahun', 'deleted_at')
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'title' => $item->nama_diklat,
                'tahun' => $item->tahun,
                'type' => 'program_eksternal',
                'in_trash' => !is_null($item->deleted_at),
            ])
            ->toArray();

        $results = array_merge($results, $programEksternal);

        // Program HLC — kolom judulnya nama_program, bukan nama_diklat
        $queryHlc = \App\Models\ProgramHlc::where('nama_program', 'like', "%{$keyword}%");

        if (!$includeTrash) {
            $queryHlc->whereNull('deleted_at');
        }

        $programHlc = $queryHlc->select('id', 'nama_program', 'tahun', 'deleted_at')
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'title' => $item->nama_program,
                'tahun' => $item->tahun,
                'type' => 'program_hlc',
                'in_trash' => !is_null($item->deleted_at),
            ])
            ->toArray();

        return array_merge($results, $programHlc);
    }
}
