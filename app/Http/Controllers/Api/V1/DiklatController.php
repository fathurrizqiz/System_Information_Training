<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\DiklatResource;
use App\Models\DiklatKaryawan;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DiklatController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min(max($request->integer('per_page', 10), 1), 50);

        return DiklatResource::collection(
            DiklatKaryawan::query()
                ->where('nrp', $request->user()->nrp)
                ->latest()
                ->paginate($perPage)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatedData($request, true);
        $validated = $this->prepareData($request, $validated);
        $validated['nrp'] = $request->user()->nrp;
        $validated['status'] = 'pending';

        $diklat = DiklatKaryawan::create($validated);

        return (new DiklatResource($diklat))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, DiklatKaryawan $diklat): DiklatResource
    {
        $this->ensureOwner($request, $diklat);

        return new DiklatResource($diklat);
    }

    public function update(Request $request, DiklatKaryawan $diklat): DiklatResource
    {
        $this->ensureOwner($request, $diklat);
        $validated = $this->prepareData($request, $this->validatedData($request, false));

        $diklat->update($validated);

        return new DiklatResource($diklat->fresh());
    }

    public function destroy(Request $request, DiklatKaryawan $diklat): JsonResponse
    {
        $this->ensureOwner($request, $diklat);

        if ($diklat->file_path) {
            Storage::disk('public')->delete($diklat->file_path);
        }

        $diklat->delete();

        return response()->json(null, 204);
    }

    private function validatedData(Request $request, bool $isCreating): array
    {
        return $request->validate([
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'nama_diklat' => ['required', 'string', 'max:255'],
            'pengajar' => ['required', 'string', 'max:255'],
            'diklat' => ['required', 'string', 'max:255'],
            'penyelenggara' => ['required', 'string', 'max:255'],
            'jam_diklat' => ['required', 'integer', 'min:1'],
            'evaluasimateri' => ['nullable', 'string', 'max:255'],
            'evaluasipengajar' => ['nullable', 'string', 'max:255'],
            'file' => [$isCreating ? 'required' : 'nullable', 'file', 'mimes:pdf', 'max:2048'],
        ]);
    }

    private function prepareData(Request $request, array $validated): array
    {
        $duration = Carbon::parse($validated['tanggal_mulai'])
            ->diffInDays(Carbon::parse($validated['tanggal_selesai'])) + 1;
        $validated['jam_diklat'] *= $duration;

        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('diklat_files', 'public');
        }

        unset($validated['file']);

        return $validated;
    }

    private function ensureOwner(Request $request, DiklatKaryawan $diklat): void
    {
        abort_unless($diklat->nrp === $request->user()->nrp, 404);
    }
}
