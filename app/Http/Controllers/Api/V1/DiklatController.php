<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DiklatKaryawan;
use Illuminate\Http\Request;

class DiklatController extends Controller
{
    public function index(Request $request)
    {
        $diklat = DiklatKaryawan::where('nrp', $request->user()->nrp)
            ->latest()
            ->get();

        return response()->json([
            'data' => $diklat
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_diklat' => ['required', 'string'],
            'pengajar' => ['required', 'string'],
            'penyelenggara' => ['required', 'string'],
            'diklat' => ['required', 'string'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date'],
            'jam_diklat' => ['required', 'integer'],
            'file' => ['nullable', 'file', 'mimes:pdf', 'max:2048'],
        ]);

        $validated['nrp'] = $request->user()->nrp;
        $validated['status'] = 'pending';

        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('diklat_files', 'public');
        }

        $diklat = DiklatKaryawan::create($validated);

        return response()->json([
            'message' => 'Pengajuan diklat mandiri berhasil dibuat',
            'data' => $diklat
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $diklat = DiklatKaryawan::where('id', $id)
            ->where('nrp', $request->user()->nrp)
            ->firstOrFail();

        return response()->json([
            'data' => $diklat
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $diklat = DiklatKaryawan::where('id', $id)
            ->where('nrp', $request->user()->nrp)
            ->firstOrFail();

        $validated = $request->validate([
            'nama_diklat' => ['sometimes', 'string'],
            'pengajar' => ['sometimes', 'string'],
            'penyelenggara' => ['sometimes', 'string'],
            'diklat' => ['sometimes', 'string'],
            'tanggal_mulai' => ['sometimes', 'date'],
            'tanggal_selesai' => ['sometimes', 'date'],
            'jam_diklat' => ['sometimes', 'integer'],
            'file' => ['nullable', 'file', 'mimes:pdf', 'max:2048'],
        ]);

        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('diklat_files', 'public');
        }

        $diklat->update($validated);

        return response()->json([
            'message' => 'Pengajuan diklat mandiri berhasil diperbarui',
            'data' => $diklat
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        $diklat = DiklatKaryawan::where('id', $id)
            ->where('nrp', $request->user()->nrp)
            ->firstOrFail();

        $diklat->delete();

        return response()->json([
            'message' => 'Pengajuan diklat mandiri berhasil dihapus'
        ], 200);
    }
}