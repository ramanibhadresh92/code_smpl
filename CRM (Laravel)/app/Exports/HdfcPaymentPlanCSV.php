<?php

namespace App\Exports;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;


class HdfcPaymentPlanCSV implements FromView
{
    public function __construct($data)
    {
      $this->data = $data;
     
    }
    public function view(): View
    {
        // prd($this->data);
        return view('Export.HdfcPaymentPlanCSV', [
            'data'  => $this->data,
           
        ]);
    }
}
