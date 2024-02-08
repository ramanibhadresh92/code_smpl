<?php

namespace App\Classes;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable
class DispatchExport implements FromView {
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
    public function view(): View
    {
        return view('Reports.DispatchReportExcel', [
            'data' => $this->data
        ]);
    }
}

