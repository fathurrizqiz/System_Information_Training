<?php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LaporanDiklatExport implements FromView, ShouldAutoSize
{
    protected $data;
    protected $namaBulan;

    public function __construct($data, $namaBulan)
    {
        $this->data = $data;
        $this->namaBulan = $namaBulan;
    }

    public function view(): View
    {
        return view('laporan.laporan-diklat', [
            'data' => $this->data,
            'namaBulan' => $this->namaBulan
        ]);
    }
}