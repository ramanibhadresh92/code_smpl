<?php

namespace App\Exports;
use App\Models\WmPurchaseToSalesMap;
use App\Models\CompanyProductMaster;
use App\Models\CompanyProductQualityParameter;
use App\Models\WmProductMaster;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;


class SalesRegisterItemWiseReport implements FromView
{
	public function __construct($raw,$data)
    {
      $this->data = $data;
      $this->raw = $raw;
    }
    public function view(): View
    {
        return view('Export.SalesRegisterItemWiseReport', [
            'data'  => $this->data,
            'raw' 	=> $this->raw
        ]);
    }
}
