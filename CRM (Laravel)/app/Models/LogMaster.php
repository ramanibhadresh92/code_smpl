<?php

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\Model;
use App\Models\LogAction;
use App\Models\AdminUser;
use App\Facades\LiveServices;
class LogMaster extends Model
{
	   
	protected 	$table              = 'log';
	protected 	$guarded            = ['log_id'];
	protected 	$primaryKey         = 'log_id'; // or null
	protected static $arr_action_perform = array(
												'Group_Added' => '1',
												'Group_Updated' => '2',
												'Group_Deleted' => '3',
												'Group_Rights_Updated' => '4',
												'Parameter_Added' => '14',
												'Parameter_Updated' => '15',
												'Prameter_Deleted' => '16',
												'Parameter_Type_Added' => '17',
												'Parameter_Type_Updated' => '18',
												'Prameter_Type_Deleted' => '19',
												'Dustbin_Added' => '20',
												'Dustbin_Updated' => '21',
												'Dustbin_Deleted' => '22',
												'Category_Added' => '23',
												'Category_Updated' => '24',
												'Category_Deleted' => '25',
												'Company_Added' => '26',
												'Company_Updated' => '27',
												'Company_Deleted' => '28',
												'Customer_Added' => '29',
												'Customer_Updated' => '30',
												'Customer_Deleted' => '31',
												'Appointment_Added' => '32',
												'Appointment_Updated' => '33',
												'Appointment_Deleted' => '34',
												'Product_Added' => '35',
												'Product_Updated' => '36',
												'Product_Deleted' => '37',
												'Collection_Added' => '38',
												'Collection_Updated' => '39',
												'Collection_Deleted' => '40',
												'Product_Parameter_Added' => '41',
												'Product_Parameter_Updated' => '42',
												'Product_Parameter_Deleted' => '43',
												'Collection_Details_Added' => '44',
												'Collection_Details_Updated' => '45',
												'Audit_Collection_Details_Added' => '46',
												'Audit_Collection_Details_Updated' => '47',
												'Customer_GEO_Location_Details_Updated' => '48',
												'Added_Godown_Collection' => '49',
												'Updated_Godown_Collection' => '50',
												'Collection_Details_Delated' => '51',
												'Stock_Outward_Added' => '54',
												'Stock_Outward_Updated' => '55',
												'Product_Factory_Price_Update_(Sent_For_Approval' => '56',
												'Product_Factory_Price_Update_(Approved' => '57',
												'Survey_Question_Added' => '58',
												'Survey_Question_Updated' => '59',
												'Survey_Question_Deleted' => '60',
												'PhoneBook_Added' => '61',
												'PhoneBook_Updated' => '62',
												'PhoneBook_Deleted' => '63',
												'Vehicle_KM_Reading_Added' => '64',
												'Vehicle_KM_Reading_Updated' => '65',
												'Survey_Answer_Deleted' => '66',
												'Customer_Product_Updated' => '67',
												'FOC_Appointment_Added' => '68',
												'FOC_Appointment_Updated' => '69',
												'FOC_Appointment_Cancel' => '70',
												'Zone_Inserted' => '71',
												'Ward_Inserted' => '72',
												'Society_Inserted' => '73',
												'Zone_Updated' => '74',
												'Ward_Updated' => '75',
												'Society_Updated' => '76',
												'Customer_Contact_Details_Updated' => '77',
												'Customer_Contact_Details_Deleted' => '78',
												'Appointment_Payment_Added' => '79',
												'Send_SMS' => '80',
												'Task_Added' => '81',
												'Task_Updated' => '82',
												'Task_Detail_Added' => '83',
												'Article_Added' => '84',
												'Article_Updated' => '85',
												'BOP_SURVEY_UPDATED' => '89',
												'BOP_Survey_Mapped' => '90',
												'Send_Appointment_Image_Email' => '91',
												'BOP_Survey_Children_Info_Updated' => '92',
												'Generate_OTP_Code' => '93',
												'Edit_Scheduled_SMS' => '94',
												'Delete_Scheduled_SMS' => '95',
												'Collection_Tag_Added' => '96',
												'Collection_Tag_Updated' => '97',
												'Collection_Tag_Deleted' => '98',
												'Collection_Cash_Transaction_Added' => '99',
												'Collection_Cash_Transaction_Updated' => '100',
												'Collection_Cash_Transaction_Deleted' => '101',
												'Appointment_Mediator_Added' => '102',
												'Appointment_Mediator_Updated' => '103',
												'Customer_Schedule_Approve' => '104',
												'Customer_Schedule_Reject' => '105',
												'Redeem_Product_Added' => '106',
												'Redeem_Product_Updated' => '107',
												'Redeem_Product_Order_Updated' => '108',
												'Send_Product_Order_Invoice' => '109',
												'Vehicle_Added' => '110',
												'Vehicle_Updated' => '111',
												'Vehicle_Document_Updated' => '112',
												'Vehicle_Document_Deleted' => '113',
												'Vehicle_Data_Update_Approval_Request_Added' => '114',
												'Vehicle_Data_Update_Approval_Request_Updated' => '115',
												'Customer_Data_Update_Approval_Request_Added' => '116',
												'Customer_Data_Update_Approval_Request_Updated' => '117');
	/**
	 * addLog
	 *
	 * Behaviour : Public
	 *
	 * @param : 
	 *
	 * @defination : In order to add log.
	 **/
	public static function addLog($action_id,$action_value='',$action_value_table='',$system=false,$remark="",$log_id=0)
	{
		$log_ip     = \Request::getClientIp(true);
		if (empty($log_ip)) $log_ip = getIP("X");
		if ($log_id == 0)
		{
			$logObj             = self::create(['log_ip'            => $log_ip,
												'log_dt'            => date('Y-m-d H:i:s'),
												'loguser_id'        => isset(auth()->user()->adminuserid) ? auth()->user()->adminuserid : '1',
												'action_id'         => self::$arr_action_perform[$action_id],
												'action_value'      => $action_value,
												'action_value_table'=> $action_value_table,
												'remark'            => $remark,
												'user_type'         => (isset(auth()->user()->user_type) && !empty(auth()->user()->user_type))?auth()->user()->user_type:'']);
		}
	}

	/**
	* Function Name : GetActionLogDetails
	* @param object $request
	* @param datetime $StartTime
	* @param datetime $EndTime
	* @author Kalpak Prajapati
	* @since 2019-05-28
	* @access public
	* @uses method used to get action log details for system
	*/
	public static function GetActionLogDetails($request)
	{

		$date 				= date("Y-m-d");
		$AdminUser      	= new AdminUser;
		$LogAction      	= new LogAction;
		$LogMaster			= (new self)->getTable();
		$sortBy         	= (isset($request->sortBy) && !empty($request->input('sortBy')))            ? $request->input('sortBy') 		: $LogMaster.".log_dt";
		$sortOrder      	= (isset($request->sortOrder) && !empty($request->input('sortOrder')))      ? $request->input('sortOrder') 		: "DESC";
		$recordPerPage  	= (isset($request->size) && !empty($request->input('size')))                ?   $request->input('size')         : 10;
		$pageNumber     	= (isset($request->pageNumber) && !empty($request->input('pageNumber')))    ?   $request->input('pageNumber')   : '';

		$StartTime 			= ($request->has('params.starttime') && !empty($request->input('params.starttime'))) ? $request->input('params.starttime') : $date;

		$EndTime 			= ($request->has('params.endtime') && !empty($request->input('params.endtime'))) ? $request->input('params.endtime') : $date;
		$StartTime 			= date("Y-m-d",strtotime($StartTime));
		$EndTime 			= date("Y-m-d",strtotime($EndTime));
		$AdminUserCompanyID = isset(Auth()->user()->company_id)?Auth()->user()->company_id:0;
		$LogDetails 		= self::select("$LogMaster.log_id",
											"$LogMaster.log_ip",
											"$LogMaster.loguser_id",
											"$LogMaster.log_dt",
											"$LogMaster.remark",
											"$LogMaster.action_id",
											$AdminUser->getTable().".username",
											$LogAction->getTable().".log_action_desc");
		$LogDetails->leftjoin($AdminUser->getTable(),$LogMaster.".loguser_id","=",$AdminUser->getTable().".adminuserid");
		$LogDetails->leftjoin($LogAction->getTable(),$LogMaster.".action_id","=",$LogAction->getTable().".log_action_id");
		$LogDetails->where($AdminUser->getTable().'.company_id',$AdminUserCompanyID);

		if($request->has('params.log_ip') && !empty($request->input('params.log_ip')))
		{
			$LogDetails->where($LogMaster.'.log_ip','like','%'.$request->input('params.log_ip').'%');
		}

		if($request->has('params.loguser_id') && !empty($request->input('params.loguser_id')))
		{
			$LogDetails->where($LogMaster.'.loguser_id',$request->input('params.loguser_id'));
		}

		if($request->has('params.action_id') && !empty($request->input('params.action_id')))
		{
			$LogDetails->where($LogMaster.'.action_id',intval($request->input('params.action_id')));
		}


		if($request->has('params.action_value') && !empty($request->input('params.action_value')))
		{
			$LogDetails->where($LogMaster.'.action_value','like','%'.$request->input('params.action_value').'%');
		}
		if($request->has('params.action_value_table') && !empty($request->input('params.action_value_table')))
		{
			$LogDetails->where($LogMaster.'.action_value_table',$request->input('params.action_value_table'));
		}

		if($request->has('params.remark') && !empty($request->input('params.remark')))
		{
			$LogDetails->where($LogMaster.'.remark','like','%'.$request->input('params.remark').'%');
		}

		$LogDetails->whereBetween("$LogMaster.log_dt", array($StartTime." ".GLOBAL_START_TIME,$EndTime." ".GLOBAL_END_TIME));
		$LogDetails->orderBy($sortBy,$sortOrder);
		// LiveServices::toSqlWithBinding($LogDetails);
		
		$LogData 	=	$LogDetails->paginate($recordPerPage,['*'],PAGE_NUMBER_ATTR,$pageNumber);
		if (!empty($LogData->total())) {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_FOUND'),'data'=>$LogData->toArray()]);
		} else {
			return response()->json(['code'=>SUCCESS,'msg'=>trans('message.RECORD_NOT_FOUND'),'data'=>array()]);
		}
	}
}