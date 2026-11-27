<?php

namespace App\Http\Controllers\RencanaDiklat\RPT;

use App\Http\Controllers\Controller;
use App\Imports\DiklatInternalImport;
use App\Models\PendidikanFormalModels;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ImportDiklatInternalController extends Controller
{
    public function store(Request $request)
    {
        $validatedFile = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new DiklatInternalImport;
        Excel::import($import, $validatedFile['file']);

        $rows = $import->rows
            ->filter(fn ($row) => $row->filter(
                fn ($value) => $value !== null && trim((string) $value) !== ''
            )->isNotEmpty());

        if ($rows->isEmpty()) {
            return back()->withErrors([
                'file' => 'File Excel tidak memiliki baris data untuk diimpor.',
            ]);
        }

        $requiredHeaders = [
            'nama_program',
            'kategori',
            'tahun',
            'nama_diklat',
            'keterangan',
            'pengajar',
        ];
        $missingHeaders = array_diff($requiredHeaders, array_keys($rows->first()->toArray()));

        if ($missingHeaders !== []) {
            return back()->withErrors([
                'file' => 'Kolom Excel wajib: Nama Program, Kategori, Tahun, Nama Diklat, Keterangan, dan Pengajar.',
            ]);
        }

        $validRows = [];
        $rowErrors = [];

        foreach ($rows as $index => $row) {
            $excelRow = $index + 2;
            $validator = Validator::make($row->toArray(), [
                'nama_program' => 'required|string|max:50',
                'kategori' => 'required|string|max:50',
                'tahun' => 'nullable|digits:4',
                'nama_diklat' => 'required|string|max:50',
                'keterangan' => 'required|string|max:20',
                'pengajar' => 'required|string|max:30',
            ]);

            if ($validator->fails()) {
                $rowErrors["rows.{$excelRow}"] = "Baris {$excelRow}: "
                    .implode(' ', $validator->errors()->all());

                continue;
            }

            $validRows[] = collect($validator->validated())
                ->map(fn ($value) => is_string($value) ? trim($value) : $value)
                ->all();
        }

        if ($rowErrors !== []) {
            return back()->withErrors($rowErrors);
        }

        $result = DB::transaction(function () use ($validRows) {
            $createdPrograms = 0;
            $createdDetails = 0;

            foreach ($validRows as $row) {
                $program = PendidikanFormalModels::active()->firstOrCreate([
                    'nama_program' => $row['nama_program'],
                    'kategori' => $row['kategori'],
                    'tahun' => $row['tahun'] ?? null,
                ]);

                if ($program->wasRecentlyCreated) {
                    $createdPrograms++;
                }

                $detail = $program->details()
                    ->where('is_deleted', false)
                    ->firstOrCreate(
                        ['nama_diklat' => $row['nama_diklat']],
                        [
                            'keterangan' => $row['keterangan'],
                            'pengajar' => $row['pengajar'],
                        ]
                    );

                if ($detail->wasRecentlyCreated) {
                    $createdDetails++;
                }
            }

            return [
                'programs' => $createdPrograms,
                'details' => $createdDetails,
            ];
        });

        return redirect()->back()->with(
            'success',
            "Impor selesai: {$result['programs']} program baru dan {$result['details']} detail diklat baru."
        );
    }
}
