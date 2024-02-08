<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Models\ViewFocAppointment;
use App\Models\VehicleDriverMappings;
use App\Models\Appoinment;
use App\Models\CustomerMaster;
use App\Models\AdminUser;
use App\Models\VehicleMaster;
use App\Models\CompanyParameter;
use App\Models\LocationMaster;
use PDF;

class FocAppointmentStatus extends Model
{
	protected $table = 'foc_appointment_status';
	protected $guarded = [];
	public    $timestamps =   false;

	public static function saveFOCAppointmentStatus($request)
	{
		$data['appointment_id']     = isset($request->appointment_id) ? $request->appointment_id : '';
		$data['customer_id']        = isset($request->customer_id) ? $request->customer_id : '';
		$data['vehicle_id']         = isset($request->vehicle_id) ? $request->vehicle_id : '';
		$data['collection_by']      = isset($request->collection_by) ? $request->collection_by : auth()->user()->adminuserid;
		$data['longitude']          = isset($request->longitude) ? $request->longitude : '';
		$data['latitude']           = isset($request->latitude) ? $request->latitude : '';
		$data['reach']              = isset($request->reach) ? $request->reach : '';
		$data['reach_time']         = isset($request->reach_time) ? $request->reach_time : '';
		$data['complete_time']      = isset($request->complete_time) ? $request->complete_time : '';
		$data['collection_receive'] = isset($request->collection_receive) ? $request->collection_receive : '';
		$data['collection_remark']  = isset($request->collection_remark) ? $request->collection_remark : '';
		$data['collection_qty']     = isset($request->collection_qty) ? $request->collection_qty : '';
		$data['location_variance']  = isset($request->location_variance) ? $request->location_variance : '';
		$data['created_date']       = Carbon::now();
		$CustomerMasterData         = CustomerMaster::find($data['customer_id']);
		if(!empty($CustomerMasterData)){
			 $data['slab_id']       = $CustomerMasterData->slab_id;
		}
		$data['collection_data']    = isset($request->collection_data) && !empty($request->collection_data) ? $request->collection_data : '';
		return self::create($data);
	}

	/**
	* Function Name : GetMissedFocAppointmentLocation
	* @param object $Request
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to get Missed Appointment Details
	*/
	public static function GetMissedFocAppointmentLocation($CompanyID,$StartTime,$EndTime,$arrFilter=array(),$Json=false)
	{
		$CustomerMaster     = new CustomerMaster;
		$AdminUser          = new AdminUser;
		$VehicleMaster      = new VehicleMaster;
		$ViewFocAppointment = new ViewFocAppointment;
		$CompanyParameter   = new CompanyParameter;
		$LocationMaster     = new LocationMaster;
		$FocAppoinment      = (new self)->getTable();
		$arrAppointments    = array();

		$FILTERCONDITION     = "";
		if (isset($arrFilter['customer_type']) && !empty($arrFilter['customer_type'])) {
			$FILTERCONDITION .= " AND CM.ctype = ".intval($arrFilter['customer_type']);
		}
		if (isset($arrFilter['customer_group']) && !empty($arrFilter['customer_group'])) {
			$FILTERCONDITION .= " AND CM.cust_group = ".intval($arrFilter['customer_group']);
		}
		if (isset($arrFilter['vehicle_id']) && !empty($arrFilter['vehicle_id'])) {
			$FILTERCONDITION .= " AND FOCA.vehicle_id = ".intval($arrFilter['vehicle_id']);
		}
		if (isset($arrFilter['city_id']) && !empty($arrFilter['city_id']) && is_array($arrFilter['city_id'])) {
			$FILTERCONDITION .= " AND CM.city IN (".implode(",",$arrFilter['city_id']).")";
		}
		if (isset($arrFilter['exclude_customer_type']) && !empty($arrFilter['exclude_customer_type']) && is_array($arrFilter['exclude_customer_type'])) {
			$FILTERCONDITION .= " AND CM.cust_group NOT IN (".implode(",",$arrFilter['exclude_customer_type']).")";
		}
		if (isset($arrFilter['exclude_city_id']) && !empty($arrFilter['exclude_city_id']) && is_array($arrFilter['exclude_city_id'])) {
			$FILTERCONDITION .= " AND CM.city NOT IN (".implode(",",$arrFilter['exclude_city_id']).")";
		}
		if (isset($arrFilter['route']) && !empty($arrFilter['route'])) {
			$FILTERCONDITION .= " AND FOCA.route = ".intval($arrFilter['route']);
		}
		$SelectSql      = " SELECT CP.para_value as Route_Name,
							CONCAT(CM.first_name,' ',CM.last_name) AS Customer_Name,
							VM.vehicle_number,
							FOCA.appointment_id,
							FOCA.app_date_time,
							CONCAT(AU.firstname,' ',AU.lastname) AS Collection_By,
							LM.city as City_Name
							FROM ".$ViewFocAppointment->getTable()." AS FOCA
							LEFT JOIN ".$CustomerMaster->getTable()." AS CM ON FOCA.route = CM.route
							LEFT JOIN ".$FocAppoinment." AS FC ON CM.customer_id = FC.customer_id AND FC.appointment_id = FOCA.appointment_id
							LEFT JOIN ".$AdminUser->getTable()." AS AU ON FOCA.collection_by = AU.adminuserid
							LEFT JOIN ".$VehicleMaster->getTable()." AS VM ON FOCA.vehicle_id = VM.vehicle_id
							LEFT JOIN ".$LocationMaster->getTable()." AS LM ON CM.city = LM.location_id
							LEFT JOIN ".$CompanyParameter->getTable()." AS CP ON FOCA.route = CP.para_id
							WHERE FOCA.app_date_time BETWEEN '".$StartTime."' AND '".$EndTime."'
							AND FOCA.company_id = '".intval($CompanyID)."'
							AND CM.longitude != 0
							AND LM.city IS NOT NULL
							AND CM.para_status_id = ".CUSTOMER_STATUS_ACTIVE."
							AND CM.collection_type = ".COLLECTION_TYPE_FOC."
							AND (FC.collection_remark = '' OR FC.collection_remark IS NULL)
							AND (FC.collection_receive = 0 OR FC.collection_receive IS NULL)
							$FILTERCONDITION
							ORDER BY LM.city ASC, FOCA.app_date_time ASC,VM.vehicle_number ASC, Collection_By ASC, CM.route_appointment_order ASC";
		$SelectRes      = \DB::select($SelectSql);
		$result         = array();
		$Attachments    = array();
		$previous_city  = "";
		if (!empty($SelectRes))
		{
			foreach ($SelectRes as $SelectRow)
			{
				if ($previous_city != $SelectRow->City_Name)
				{
					if ($previous_city != "" && !$Json)
					{
						$FILENAME           = $previous_city."_Missed_Foc_Appointment_".date("Y-m-d",strtotime($StartTime))."_".date("Y-m-d",strtotime($EndTime)).".pdf";
						$REPORT_START_DATE  = date("Y-m-d",strtotime($StartTime));
						$REPORT_END_DATE    = date("Y-m-d",strtotime($EndTime));
						$Title              = $previous_city." Missed FOC Collection Point From ".$REPORT_START_DATE." To ".$REPORT_END_DATE;
						$Foc                = 1;
						$pdf = PDF::loadView('email-template.missed_appointment', compact('result','Title','Foc'));
						$pdf->setPaper("A4", "landscape");
						ob_get_clean();
						$path           = public_path("/").PATH_COLLECTION_RECIPT_PDF;
						$PDFFILENAME    = $path.$FILENAME;
						if (!is_dir($path)) {
							mkdir($path, 0777, true);
						}
						$pdf->save($PDFFILENAME, true);
						array_push($Attachments,$PDFFILENAME);
						$result = array();
					}
				}
				if (in_array($SelectRow->appointment_id,$arrAppointments)) {
					$result[$SelectRow->appointment_id]['Customer'][]   = array("Customer_Name"=>$SelectRow->Customer_Name,
																				"City_Name"=>$SelectRow->City_Name);
				} else {
					$result[$SelectRow->appointment_id]['Route_Name']       = $SelectRow->Route_Name;
					$result[$SelectRow->appointment_id]['Collection_By']    = $SelectRow->Collection_By;
					$result[$SelectRow->appointment_id]['App_Time']         = _FormatedDate($SelectRow->app_date_time);
					$result[$SelectRow->appointment_id]['vehicle_number']   = $SelectRow->vehicle_number;
					$result[$SelectRow->appointment_id]['Customer'][]       = array("Customer_Name"=>$SelectRow->Customer_Name,
																					"City_Name"=>$SelectRow->City_Name);
					array_push($arrAppointments,$SelectRow->appointment_id);
				}
				$previous_city = $SelectRow->City_Name;
			}
			if (!$Json) {
				if ($previous_city != "" && !empty($result))
				{
					$FILENAME           = $previous_city."_Missed_Foc_Appointment_".date("Y-m-d",strtotime($StartTime))."_".date("Y-m-d",strtotime($EndTime)).".pdf";
					$REPORT_START_DATE  = date("Y-m-d",strtotime($StartTime));
					$REPORT_END_DATE    = date("Y-m-d",strtotime($EndTime));
					$Title              = $previous_city." Missed FOC Collection Point From ".$REPORT_START_DATE." To ".$REPORT_END_DATE;
					$Foc                = 1;
					$pdf = PDF::loadView('email-template.missed_appointment', compact('result','Title','Foc'));
					$pdf->setPaper("A4", "landscape");
					ob_get_clean();
					$path           = public_path("/").PATH_COLLECTION_RECIPT_PDF;
					$PDFFILENAME    = $path.$FILENAME;
					if (!is_dir($path)) {
						mkdir($path, 0777, true);
					}
					$pdf->save($PDFFILENAME, true);
					array_push($Attachments,$PDFFILENAME);
					$result = array();
				}
			} else {
				if (!empty($result)) {
					return response()->json(['code'=>SUCCESS,
									'msg'=>trans('message.RECORD_FOUND'),
									'SelectSql'=>$arrAppointments,
									'data'=>$result]);
				} else {
					return response()->json(['code'=>SUCCESS,
									'msg'=>trans('message.RECORD_NOT_FOUND'),
									'SelectSql'=>$arrAppointments,
									'data'=>array()]);
				}
			}
		}
		return $Attachments;
	}

	/**
	* Function Name : GetFocLocationCollectionDetails
	* @param integer $RouteID
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @author Kalpak Prajapati
	* @since 2019-03-13
	* @access public
	* @uses method used to get FOC Points Collection Details Datewise
	*/
	public static function GetFocLocationCollectionDetails($RouteID,$StartTime,$EndTime)
	{
		$CustomerMaster     = new CustomerMaster;
		$AdminUser          = new AdminUser;
		$VehicleMaster      = new VehicleMaster;
		$ViewFocAppointment = new ViewFocAppointment;
		$CompanyParameter   = new CompanyParameter;
		$LocationMaster     = new LocationMaster;
		$FocAppoinment      = (new self)->getTable();
		$SelectSql      = " SELECT DATE_FORMAT(FOCA.app_date_time,'%Y-%m-%d') as App_Date,
							VM.vehicle_number,
							FOCA.appointment_id,
							CM.customer_id,
							CM.route_appointment_order,
							CONCAT(AU.firstname,' ',AU.lastname) AS Collection_By,
							CONCAT(CM.first_name,' ',CM.last_name) AS Customer_Name,
							LM.city as City_Name,
							SUM(FC.collection_qty) AS Gross_Qty,
							CP.para_value as Route_Name
							FROM ".$ViewFocAppointment->getTable()." AS FOCA
							LEFT JOIN ".$CustomerMaster->getTable()." AS CM ON FOCA.route = CM.route
							LEFT JOIN ".$FocAppoinment." AS FC ON CM.customer_id = FC.customer_id AND FC.appointment_id = FOCA.appointment_id
							LEFT JOIN ".$AdminUser->getTable()." AS AU ON FOCA.collection_by = AU.adminuserid
							LEFT JOIN ".$VehicleMaster->getTable()." AS VM ON FOCA.vehicle_id = VM.vehicle_id
							LEFT JOIN ".$LocationMaster->getTable()." AS LM ON CM.city = LM.location_id
							LEFT JOIN ".$CompanyParameter->getTable()." AS CP ON FOCA.route = CP.para_id
							WHERE CM.route = '".intval($RouteID)."'
							AND FOCA.app_date_time BETWEEN '".$StartTime."' AND '".$EndTime."'
							AND CM.longitude != 0
							AND CM.para_status_id = ".CUSTOMER_STATUS_ACTIVE."
							AND CM.collection_type IN (".COLLECTION_TYPE_FOC.",".COLLECTION_TYPE_FOC_PAID.")
							GROUP BY App_Date, CM.customer_id
							ORDER BY CM.route_appointment_order ASC,FOCA.app_date_time ASC";
		$SelectRes      = \DB::select($SelectSql);
		$result         = array();
		$titleDetails   = array();
		if (!empty($SelectRes))
		{
			foreach ($SelectRes as $Key=>$SelectRow)
			{
				if ($Key == 0) {
					$Vehicle_Number = $SelectRow->vehicle_number;
					$Collection_By  = $SelectRow->Collection_By;
					$Route_Name     = $SelectRow->Route_Name;
				}
				$result[$SelectRow->route_appointment_order]['Customer_Name']   = $SelectRow->Customer_Name;
				$result[$SelectRow->route_appointment_order]['City_Name']       = $SelectRow->City_Name;
				$result[$SelectRow->route_appointment_order]['Row'][date("d",strtotime($SelectRow->App_Date))] = _FormatNumberV2($SelectRow->Gross_Qty);
			}
			$titleDetails['Vehicle_Number']   = $Vehicle_Number;
			$titleDetails['Collection_By']    = $Collection_By;
			$titleDetails['Route_Name']       = $Route_Name;
		}
		if (empty($result)) {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$result,"titleDetails"=>$titleDetails]);
		} else {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>$result,"titleDetails"=>$titleDetails]);
		}
	}
}