<?php

namespace App\Exports;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;


class PaymentPlanCSV implements FromView
{
    public function __construct($data)
    {
      $this->data = $data;
      // $this->raw = $raw;
    }
    public function view(): View
    {
        return view('Export.PaymentPlanCSV', [
            'data'  => $this->data,
           
        ]);
    }
}
