<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapDatabaseExport implements FromView, ShouldAutoSize, WithStyles
{
  protected $dataRows;

    public function __construct($dataRows)
    {
        $this->dataRows = $dataRows;
    }

    public function view(): View
    {
        return view('laporan.database-export', [
            'rows' => $this->dataRows
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style Header agar tebal (Bold) pada baris pertama
            1    => ['font' => ['bold' => true]],
        ];
    }
}