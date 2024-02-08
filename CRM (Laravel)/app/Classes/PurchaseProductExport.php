<?php

namespace App\Classes;

use App\Models\CompanyProductMaster;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PurchaseProductExport implements FromView
{
    public function view(): View
    {
        return view('test', [
            'invoices' => ""
        ]);
    }
}

// namespace App\Classes;
// use Illuminate\Contracts\View\View;
// use Maatwebsite\Excel\Concerns\FromView;
// use Maatwebsite\Excel\Concerns\Exportable;
// class PurchaseProductExport implements FromView {
//     private $data;

//     public function __construct($data = "")
//     {
//         $this->data = $data;
//     }
//     public function view(): View
//     {
//         return view('test', [
//             'data' => $this->data
//         ]);
//     }
// }

