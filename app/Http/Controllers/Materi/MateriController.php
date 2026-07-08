<?php
namespace App\Http\Controllers\Materi;

use App\Http\Controllers\Controller;
use App\Models\MateriModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class MateriController extends Controller
{
    public function index($folderId = null)
    {
        // Parent null → root folder.
        // Parent id → isi folder.
        $materi = MateriModel::where('parent_id', $folderId)
            ->orderBy('type')
            ->orderBy('title')
            ->get();

        $currentFolder = $folderId ? MateriModel::find($folderId) : null;
        $breadcrumb = [];

        // Build breadcrumb
        if ($currentFolder) {
            $temp = $currentFolder;
            $breadcrumb[] = $temp;

            while ($temp->parent_id) {
                $temp = $temp->parent;
                array_unshift($breadcrumb, $temp);
            }
        }

        return Inertia::render('Materi/index', [
            'materi' => $materi,
            'currentFolder' => $currentFolder,
            'breadcrumb' => $breadcrumb,
        ]);
    }


    public function store(Request $request)
    {
        // === DEBUG STEP 1: Lihat semua data yang diterima dari frontend ===
        // dd($request->all());

        try {
            $request->validate([
                'type' => 'required|in:folder,file',
                'name' => 'required|string|max:255',
                'file' => 'nullable|file|max:20480', // 20MB max
                'parent_id' => 'nullable|exists:materi_library,id',
            ]);

            $path = null;

            // === DEBUG STEP 2: Periksa kondisi IF ===
            // Hapus komentar pada baris di bawah untuk melihat mengapa kondisi IF tidak jalan
            // if (!($request->type === 'file' && $request->hasFile('file'))) {
            //     dd('Kondisi IF tidak terpenuhi. Type: ' . $request->type . ', HasFile: ' . $request->hasFile('file'));
            // }

            if ($request->type === 'file' && $request->hasFile('file')) {
                $path = $request->file('file')->store('materi', 'public');

                // === DEBUG STEP 3: Lihat hasil path dari penyimpanan file ===
                // Jika berhasil, ini akan menampilkan path seperti 'materi/filename.jpg'
                // dd($path);
            }

            // Folder otomatis status verified, file tetap pending
            $status = $request->type === 'folder' ? 'verified' : 'pending';

            $dataToCreate = [
                'title' => $request->name,
                'type' => $request->type,
                'file_path' => $path, // Ini adalah variabel yang kita periksa
                'parent_id' => $request->parent_id,
                'status' => $status,
            ];

            // === DEBUG STEP 4: Lihat data yang akan disimpan ke database ===
            // dd($dataToCreate);

            $materi = MateriModel::create($dataToCreate);

            // return response()->json([
            //     'success' => true,
            //     'message' => 'Materi berhasil ditambahkan.',
            //     'data' => $materi,
            // ]);
        } catch (\Exception $e) {
            // return response()->json([
            //     'success' => false,
            //     'message' => 'Terjadi kesalahan saat menyimpan data.',
            //     'error' => $e->getMessage(),
            // ], 500);
        }
    }

    public function getAll()
    {
        return response()->json(['data' => MateriModel::orderBy('created_at', 'desc')->get()]);
    }

    public function verify($id)
    {
        $materi = MateriModel::findOrFail($id);
        $materi->status = 'verified';
        $materi->save();

        return redirect()->back()->with('success', 'Materi berhasil diverifikasi.');
    }

    public function reject(Request $request, $id)
    {
        $materi = MateriModel::findOrFail($id);
        $materi->status = 'rejected';
        $materi->reject_reason = $request->reason;
        $materi->save();

        // return response()->json([
        //     'success' => true,
        //     'message' => 'Materi ditolak.'
        // ]);
    }

    public function delete($id)
    {
        $materi = MateriModel::findOrFail($id);

        // Jika folder, hapus juga semua isinya
        if ($materi->type === 'folder') {
            $this->deleteFolderContents($materi);
        }

        // Hapus file jika ada
        if ($materi->file_path) {
            Storage::disk('public')->delete($materi->file_path);
        }

        $materi->delete();

        // return response()->json([
        //     'success' => true,
        //     'message' => 'Materi berhasil dihapus.'
        // ]);
    }

    private function deleteFolderContents($folder)
    {
        foreach ($folder->children as $child) {
            if ($child->type === 'folder') {
                $this->deleteFolderContents($child);
            } else if ($child->file_path) {
                Storage::disk('public')->delete($child->file_path);
            }
            $child->delete();
        }
    }
}