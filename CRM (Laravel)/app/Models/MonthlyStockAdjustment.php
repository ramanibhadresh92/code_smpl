<?php

namespace App\Models;
use App\Facades\LiveServices;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Model;
use Mail;

class MonthlyStockAdjustment extends Model
{
	protected $table 		= 'monthly_stock_updates';
	protected $fillable     = ['product_id','adj_qty','current_stock','stock_date','mrf_id','created_by','updated_by'];

	/**
	* Function Name : SaveMonthlyStockAdjustmentForProduct
	* @param float $QTY
	* @param float $CURRENT_STOCK
	* @param date $TODAY
	* @param integer $MRF_ID
	* @param integer $USERID
	* @author Kalpak Prajapati
	* @since 2022-01-24
	* @access public
	* @uses SaveMonthlyStockAdjustmentForProduct
	*/
	public static function SaveMonthlyStockAdjustmentForProduct($PRODUCT_ID,$QTY,$CURRENT_STOCK,$TODAY,$MRF_ID,$USERID)
	{
		$add 				= new self();
		$add->product_id	= $PRODUCT_ID;
		$add->adj_qty	 	= $QTY;
		$add->current_stock = $CURRENT_STOCK;
		$add->stock_date 	= $TODAY;
		$add->mrf_id 		= $MRF_ID;
		$add->created_by 	= $USERID;
		$add->updated_by 	= $USERID;
		if($add->save()) {
			return true;
		}
		return false;
	}

	/**
	* Function Name : SendMonthlyStockAdjustmentEmail
	* @param date $START_DATE
	* @param date $END_DATE
	* @author Kalpak Prajapati
	* @since 2022-01-24
	* @access public
	* @uses SendMonthlyStockAdjustmentEmail
	*/
	public static function SendMonthlyStockAdjustmentEmail($START_DATE,$END_DATE)
	{
		$START_DATE = $START_DATE." ".GLOBAL_START_TIME;
		$END_DATE 	= $END_DATE." ".GLOBAL_END_TIME;
		$ReportRows = self::select(\DB::raw("CONCAT(pm.name,'-',pq.parameter_name) As Product_Name"),
									"monthly_stock_updates.stock_date",
									"monthly_stock_updates.current_stock","monthly_stock_updates.adj_qty",
									"MRF.department_name",
									\DB::raw("CONCAT(CB.firstname,' ',CB.lastname) As Adjusted_By"))
						->LEFTJOIN("company_product_master as pm","pm.id",'=','monthly_stock_updates.product_id')
						->LEFTJOIN("company_product_quality_parameter as pq","pq.product_id",'=','pm.id')
						->LEFTJOIN("adminuser as CB","monthly_stock_updates.created_by",'=','CB.adminuserid')
						->LEFTJOIN("wm_department as MRF","monthly_stock_updates.mrf_id",'=','MRF.id')
						->whereBetWeen("monthly_stock_updates.created_at",[$START_DATE,$END_DATE])
						->orderBy("monthly_stock_updates.id")
						->get();
		if (!empty($ReportRows))
		{
			$ReportData = array();
			foreach($ReportRows as $ReportRow)
			{
				$ReportData[] 	= array("PRODUCT_NAME"=>$ReportRow->Product_Name,
										"STOCK_DATE"=>$ReportRow->stock_date,
										"CURRENT_STOCK"=>$ReportRow->current_stock,
										"ADJ_QTY"=>$ReportRow->adj_qty,
										"MRF_NAME"=>$ReportRow->department_name,
										"ADJUSTED_BY"=>$ReportRow->Adjusted_By);
			}
			if (!empty($ReportData)) {
				$Attachments    = array();
				$FromEmail 		= array("Email"=>"reports@letsrecycle.co.in","Name"=>"Nepra Resource Management Private Limited");
				$ToEmail 		= "ankit@nepra.co.in";
				$Subject 		= "STOCK ADJUSTMENT DETAILS DEPARTMENT-WISE FROM ".date("Y-m-d",strtotime($START_DATE))." TO ".date("Y-m-d",strtotime($END_DATE));
				$sendEmail      = Mail::send("email-template.monthly_stock_updates",array("ReportData"=>$ReportData,"HeaderTitle"=>$Subject), function ($message) use ($ToEmail,$FromEmail,$Subject,$Attachments) {
								$message->from($FromEmail['Email'], $FromEmail['Name']);
								$message->to(explode(",",$ToEmail));
								$message->subject($Subject);
								$message->bcc("kalpak@nepra.co.in");
								if (!empty($Attachments)) {
									foreach($Attachments as $Attachment) {
										$message->attach($Attachment, ['as' => basename($Attachment),'mime' => mime_content_type($Attachment)]);
									}
								}
							});
			}
		}
	}
}
