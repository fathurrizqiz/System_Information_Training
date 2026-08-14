<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapProgramExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $dataEksternal;
    protected $dataHlc;

    public function __construct($dataEksternal, $dataHlc)
    {
        $this->dataEksternal = $dataEksternal;
        $this->dataHlc = $dataHlc;
    }

    public function view(): View
    {
        if ($this->dataEksternal && $this->dataEksternal->isNotEmpty()) {
            return view('laporan.rekap-program-eksternal', [
                'dataEksternal' => $this->dataEksternal,
            ]);
        }
        return view('laporan.rekap-program-hlc', [
            'dataHlc' => $this->dataHlc

        ]);
    }

    // (Opsional) Mengunci baris judul agar tebal
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
        ];
    }
}