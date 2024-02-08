<?php

namespace App\Exports;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;


class JobworkReport implements FromView
{
	public function __construct($raw,$data)
    {
      $this->data = $data;
      // $this->raw = $raw;
    }
    public function view(): View
    {
        return view('Export.JobworkReport', [
            'data'  => $this->data,
           
        ]);
    }
}
