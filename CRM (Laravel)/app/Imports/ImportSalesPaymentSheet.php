<?php

namespace App\Imports;
use App\Models\SalesPaymentDetails;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ImportSalesPaymentSheet implements ToModel, WithStartRow
{
	/**
     * @return int
     */
    public function startRow(): int
    {
        return 2;
    }

	/**
	* @param array $row
	*
	* @return \Illuminate\Database\Eloquent\Model|null
	*/
	public function model(array $row)
	{
		$PaymentObject 						= new SalesPaymentDetails;
		$PaymentObject->Customer			= @$row[0];
		$PaymentObject->Location 			= @$row[1];
		$PaymentObject->TransactionType 	= @$row[2];
		$PaymentObject->CustomerCategory 	= @$row[3];
		$PaymentObject->Date 				= \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject(@$row[4]);
		$PaymentObject->DocumentNumber 		= @$row[5];
		$PaymentObject->AmountGross 		= @$row[6];
		$PaymentObject->OpenBalance 		= @$row[7];
		$PaymentObject->DaysTillNetDue 		= @$row[8];
		$PaymentObject->DueDate 			= \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject(@$row[9]);
		$PaymentObject->Remarks 			= @$row[10];
		$PaymentObject->epr_bach_no 		= @$row[11];
		$PaymentObject->PONo 				= "";
		$PaymentObject->Age 				= "";
		$PaymentObject->RDueDate 			= "";
		return $PaymentObject;
	}
}