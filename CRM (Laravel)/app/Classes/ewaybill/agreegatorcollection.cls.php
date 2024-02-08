<?php
/************************************************************
* File Name : agreegatorcollection.cls.php					*
* purpose	: agreegators collection add/edit/list/search	*
* @package  : classes										*
* @author 	: Kalpak Prajapati								*
* @since 	: 16-10-2018									*
************************************************************/

class clsagreegatorcollection extends clsbase
{
	/** variable declaration of paging class variable */
	var $clspaging;

	/** variable declaration */
	var $id;
	var $agreegator_id;
	var $wm_client_master_id;
	var $vehicle_no;
	var $plain_vehicle_no;
	var $driver_name;
	var $driver_contact_no;
	var $unload;
	var $unload_date;
	var $unload_by;
	var $is_audited;
	var $audit_by;
	var $audit_date;
	var $invoice_generated;
	var $invoice_sent;
	var $invoice_date;
	var $approved_by;
	var $para_payment_status_type_id;
	var $payment_approved_by;
	var $PaymentApproved_By;
	var $payment_due_date;
	var $payment_approved_dt;
	var $payment_amount;
	var $total_collection_weight;
	var $PaymentDetails;
	var $source_address;
	var $destination_address;
	var $source_lat;
	var $source_long;
	var $destination_lat;
	var $destination_long;
	var $source_geo_location;
	var $destination_geo_location;
	var $travel_distance;
	var $queue_no;
	var $isAuditor;
	var $isFactoryAdmin;
	var $From_Security_Login;
	var $From_Driver_Login;

	var $product_id;

	var $DOCUMENT_TYPE_WAYBRIDGE;
	var $DOCUMENT_TYPE_UNLOAD_PHOTOS;
	var $DOCUMENT_TYPE_AUDIT_COLLECTION_PHOTOS;
	var $DOCUMENT_TYPE_PAYMENT_DOCS;
	var $DOCUMENT_TYPE_LICENCE_DOCS;
	var $DOCUMENT_TYPE_VEHICLE_DOCS;
	var $DOCUMENT_TYPE_EMPTY_VEHICLE;
	var $DOCUMENT_TYPE_FILLED_VEHICLE;
	var $DOCUMENT_TYPE_BILL_T_STAMPPED;
	var $DOCUMENT_TYPE_VEHICLE_TRIP_START_DOCS;
	var $DOCUMENT_TYPE_VEHICLE_TRIP_ENDS_DOCS;
	var $DOCUMENT_TYPE_VEHICLE_DRIVER_PHOTO;
	var $PAYMENT_APPROVED;

	var $arrProducts;
	var $arrDocuments;
	var $arrLicenceDocuments;
	var $arrVehicleDocuments;
	var $arrUnloadDocuments;
	var $arrPaymentDocuments;
	var $arrAuditDocuments;
	var $arrBillTDocuments;
	var $arrEmptyVehicleDocuments;
	var $arrFilledVehicleDocuments;
	var $arrDriverDocuments;
	var $arrTripStartDocuments;
	var $arrTripEndDocuments;
	var $status;
	var $lattitude;
	var $longitude;
	var $waybridge;
	var $licence_copy;
	var $vehicle_copy;
	var $audit_collection_documents;
	var $supplier_name;
	var $client_name;
	var $weigh_slip_no;
	var $bill_t_no;
	var $challan_no;
	var $eway_bill_no;
	var $sales_invoice_no;
	var $gross_weight;
	var $tare_weight;
	var $UnloadBy;
	var $AuditBy;
	var $ApprovedBy;
	var $version;
	var $arrPaymentAccounts;
	var $checkpoints;
	
	var $created_by;
	var $created_dt;
	var $updated_by;
	var $updated_dt;
	var $record_added = "N";
	var $record_updated = "N";
	var $VALID_IMG_TYPE = array("jpg","png","gif","jpeg");
	//var $arrNotificationMobiles = array("919824107807","917096109441","918849808632");
	var $arrNotificationMobiles = array("919824267999");

	var $searchid;
	var $action;

	var $period;
	var $searchdate;
	var $startday;
	var $startmonth;
	var $startyear;
	var $endday;
	var $endmonth;
	var $endyear;
	var $showmenu;

	/** DEFAULT SORTING VARS */
	var $DEFAULT_SORT_FLD = "sortorder";
	var $DEFAULT_SORT_TYPE = "ASC";
	var $TABLE_PREFIX = "";
	var $tablename = "";
	var $tablename_documents = "";
	var $tablename_products = "";
	var $tablename_payment_plan = "";
	var $tablename_checklist = "";

	/** class constructor
	* @param $connect
	* @return
	* @author Kalpak Prajapati
	*/
	function clsagreegatorcollection($connect='')
	{
		$this->clsbase($connect);
		$this->clspaging 					= new clspaging('clsagreegatorcollection');
		
		$this->id 							= "";
		$this->agreegator_id 				= "";
		$this->wm_client_master_id 			= "";
		$this->vehicle_no 					= "";
		$this->plain_vehicle_no 			= "";
		$this->driver_name 					= "";
		$this->driver_contact_no 			= "";
		$this->unload 						= "";
		$this->unload_date 					= "";
		$this->unload_by 					= "";
		$this->is_audited 					= "";
		$this->audit_by 					= "";
		$this->audit_date 					= "";
		$this->invoice_generated 			= "";
		$this->invoice_sent 				= "";
		$this->invoice_date 				= "";
		$this->approved_by 					= "";
		$this->para_payment_status_type_id 	= "";
		$this->payment_approved_by 			= "";
		$this->payment_approved_dt 			= "";
		$this->payment_amount 				= 0;
		$this->total_collection_weight 		= 0;
		$this->PaymentDetails 				= array();
		$this->arrProducts 					= array();
		$this->product_id 					= 0;

		$this->source_address 				= "";
		$this->destination_address	 		= "";
		$this->source_geo_location	 		= "";
		$this->destination_geo_location	 	= "";
		$this->travel_distance	 			= "";
		$this->source_lat	 				= 0;
		$this->source_long	 				= 0;
		$this->destination_lat	 			= 0;
		$this->destination_long	 			= 0;
		$this->queue_no	 					= 0;
		$this->isAuditor	 				= 0;
		$this->isFactoryAdmin	 			= 0;
		$this->From_Security_Login	 		= 0;
		$this->From_Driver_Login	 		= 0;
		
		
		$this->arrDocuments 				= array();
		$this->arrLicenceDocuments 			= array();
		$this->arrVehicleDocuments 			= array();
		$this->arrUnloadDocuments			= array();
		$this->arrPaymentDocuments			= array();
		$this->arrAuditDocuments			= array();
		$this->arrBillTDocuments			= array();
		$this->arrEmptyVehicleDocuments		= array();
		$this->arrFilledVehicleDocuments	= array();
		$this->arrDriverDocuments			= array();
		$this->arrTripStartDocuments		= array();
		$this->arrTripEndDocuments			= array();

		$this->status 						= "";
		$this->lattitude 					= "";
		$this->longitude 					= "";
		$this->waybridge 					= array();
		$this->licence_copy 				= array();
		$this->vehicle_copy 				= array();
		$this->audit_collection_documents 	= array();
		$this->supplier_name 				= "";
		$this->weigh_slip_no 				= "";
		$this->bill_t_no 					= "";
		$this->challan_no 					= "";
		$this->eway_bill_no 				= 0;
		$this->sales_invoice_no 			= "";
		$this->gross_weight 				= "";
		$this->tare_weight 					= "";
		$this->arrPaymentAccounts 			= array();
		$this->checkpoints 					= array();

		$this->searchid						= '';
		$this->action						= '';
		$this->period						= '';
		$this->searchdate					= '';
		$this->startday						= '';
		$this->startmonth					= '';
		$this->startyear					= '';
		$this->endday						= '';
		$this->endmonth						= '';
		$this->endyear						= '';
		$this->showmenu						= 1;

		$this->tablename					= $this->TABLE_PREFIX."macro_agreegators_dispatch";
		$this->tablename_documents 			= $this->TABLE_PREFIX."macro_agreegators_dispatch_documents";
		$this->tablename_products 			= $this->TABLE_PREFIX."macro_agreegators_dispatch_products";
		$this->tablename_payment_plan 		= $this->TABLE_PREFIX."macro_agreegators_dispatch_payment_plan";
		$this->tablename_checklist 			= $this->TABLE_PREFIX."macro_agreegators_dispatch_checklist";
		$this->DEFAULT_SORT_FLD 			= $this->tablename.".id";
		$this->DEFAULT_SORT_TYPE 			= "ASC";


		$this->DOCUMENT_TYPE_WAYBRIDGE 					= 1021002;
		$this->DOCUMENT_TYPE_BILL_T_STAMPPED 			= 1021003;
		$this->DOCUMENT_TYPE_UNLOAD_PHOTOS 				= 1021004;
		$this->DOCUMENT_TYPE_AUDIT_COLLECTION_PHOTOS 	= 1021005;
		$this->DOCUMENT_TYPE_PAYMENT_DOCS 				= 1021006;
		$this->DOCUMENT_TYPE_LICENCE_DOCS 				= 1021007;
		$this->DOCUMENT_TYPE_VEHICLE_DOCS 				= 1021008;
		$this->DOCUMENT_TYPE_EMPTY_VEHICLE 				= 1021009;
		$this->DOCUMENT_TYPE_FILLED_VEHICLE 			= 1021010;
		$this->DOCUMENT_TYPE_VEHICLE_TRIP_START_DOCS 	= 1021011;
		$this->DOCUMENT_TYPE_VEHICLE_TRIP_ENDS_DOCS 	= 1021012;
		$this->DOCUMENT_TYPE_VEHICLE_DRIVER_PHOTO 		= 1021013;
		$this->PAYMENT_PLAN_INITIATED 					= 1;
		$this->PAYMENT_APPROVED 						= 2;
	}

	/**
	* Function Name : setgetvars
	* @param
	* @return
	* @author Kalpak Prajapati
	*/
	function setgetvars()
	{
		parent::setgetvars();
		if (isset($_GET['id']))				$this->id				= intval(trim($_GET['id']));
		if (isset($_GET['agreegator_id']))	$this->agreegator_id	= intval(trim($_GET['agreegator_id']));
		if (isset($_GET['showmenu']))		$this->showmenu			= trim($_GET['showmenu']);
	}

	/**
	* Function Name : setpostvars
	* @param
	* @return
	* @author Kalpak Prajapati
	*/
	function setpostvars()
	{
		parent::setpostvars();
		//setpostvars
		if (isset($_POST['clsagreegatorcollection_showmenu'])) $this->showmenu 											= trim($_POST['clsagreegatorcollection_showmenu']);
		
		if (isset($_POST['clsagreegatorcollection_id'])) $this->id 														= trim($_POST['clsagreegatorcollection_id']);
		if (isset($_POST['clsagreegatorcollection_agreegator_id'])) $this->agreegator_id 								= trim($_POST['clsagreegatorcollection_agreegator_id']);
		if (isset($_POST['clsagreegatorcollection_wm_client_master_id'])) $this->wm_client_master_id 					= trim($_POST['clsagreegatorcollection_wm_client_master_id']);
		if (isset($_POST['clsagreegatorcollection_vehicle_no'])) $this->vehicle_no 										= trim($_POST['clsagreegatorcollection_vehicle_no']);
		if (isset($_POST['clsagreegatorcollection_driver_name'])) $this->driver_name 									= trim($_POST['clsagreegatorcollection_driver_name']);
		if (isset($_POST['clsagreegatorcollection_driver_contact_no'])) $this->driver_contact_no 						= trim($_POST['clsagreegatorcollection_driver_contact_no']);
		if (isset($_POST['clsagreegatorcollection_unload'])) $this->unload 												= trim($_POST['clsagreegatorcollection_unload']);
		if (isset($_POST['clsagreegatorcollection_unload_date'])) $this->unload_date 									= trim($_POST['clsagreegatorcollection_unload_date']);
		if (isset($_POST['clsagreegatorcollection_unload_by'])) $this->unload_by 										= trim($_POST['clsagreegatorcollection_unload_by']);
		if (isset($_POST['clsagreegatorcollection_is_audited'])) $this->is_audited 										= trim($_POST['clsagreegatorcollection_is_audited']);
		if (isset($_POST['clsagreegatorcollection_audit_by'])) $this->audit_by 											= trim($_POST['clsagreegatorcollection_audit_by']);
		if (isset($_POST['clsagreegatorcollection_audit_date'])) $this->audit_date 										= trim($_POST['clsagreegatorcollection_audit_date']);
		if (isset($_POST['clsagreegatorcollection_invoice_generated'])) $this->invoice_generated 						= trim($_POST['clsagreegatorcollection_invoice_generated']);
		if (isset($_POST['clsagreegatorcollection_invoice_sent'])) $this->invoice_sent 									= trim($_POST['clsagreegatorcollection_invoice_sent']);
		if (isset($_POST['clsagreegatorcollection_invoice_date'])) $this->invoice_date 									= trim($_POST['clsagreegatorcollection_invoice_date']);
		if (isset($_POST['clsagreegatorcollection_approved_by'])) $this->approved_by 									= trim($_POST['clsagreegatorcollection_approved_by']);
		if (isset($_POST['clsagreegatorcollection_para_payment_status_type_id'])) $this->para_payment_status_type_id 	= trim($_POST['clsagreegatorcollection_para_payment_status_type_id']);
		if (isset($_POST['clsagreegatorcollection_payment_approved_by'])) $this->payment_approved_by 					= trim($_POST['clsagreegatorcollection_payment_approved_by']);
		if (isset($_POST['clsagreegatorcollection_payment_approved_dt'])) $this->payment_approved_dt 					= trim($_POST['clsagreegatorcollection_payment_approved_dt']);
		if (isset($_POST['clsagreegatorcollection_status'])) $this->status 												= trim($_POST['clsagreegatorcollection_status']);
		if (isset($_POST['clsagreegatorcollection_weigh_slip_no'])) $this->weigh_slip_no 								= trim($_POST['clsagreegatorcollection_weigh_slip_no']);
		if (isset($_POST['clsagreegatorcollection_bill_t_no'])) $this->bill_t_no 										= trim($_POST['clsagreegatorcollection_bill_t_no']);
		if (isset($_POST['clsagreegatorcollection_challan_no'])) $this->challan_no 										= trim($_POST['clsagreegatorcollection_challan_no']);
		if (isset($_POST['clsagreegatorcollection_eway_bill_no'])) $this->eway_bill_no 									= trim($_POST['clsagreegatorcollection_eway_bill_no']);
		if (isset($_POST['clsagreegatorcollection_sales_invoice_no'])) $this->sales_invoice_no 							= trim($_POST['clsagreegatorcollection_sales_invoice_no']);
		if (isset($_POST['clsagreegatorcollection_gross_weight'])) $this->gross_weight 									= trim($_POST['clsagreegatorcollection_gross_weight']);
		if (isset($_POST['clsagreegatorcollection_tare_weight'])) $this->tare_weight 									= trim($_POST['clsagreegatorcollection_tare_weight']);
		if (isset($_POST['clsagreegatorcollection_supplier_name'])) $this->supplier_name 								= trim($_POST['clsagreegatorcollection_supplier_name']);
		if (isset($_POST['clsagreegatorcollection_arrProducts'])) $this->arrProducts 									= $_POST['clsagreegatorcollection_arrProducts'];
		if (isset($_POST['clsagreegatorcollection_arrPaymentAccounts'])) $this->arrPaymentAccounts 						= $_POST['clsagreegatorcollection_arrPaymentAccounts'];
		if (isset($_POST['clsagreegatorcollection_checkpoints'])) $this->checkpoints 									= $_POST['clsagreegatorcollection_checkpoints'];
		if (isset($_FILES['clsagreegatorcollection_waybridge'])) $this->waybridge 										= ($_FILES['clsagreegatorcollection_waybridge']);
		if (isset($_FILES['clsagreegatorcollection_licence_copy'])) $this->licence_copy 								= ($_FILES['clsagreegatorcollection_licence_copy']);
		if (isset($_FILES['clsagreegatorcollection_vehicle_copy'])) $this->vehicle_copy 								= ($_FILES['clsagreegatorcollection_vehicle_copy']);
		if (isset($_FILES['clsagreegatorcollection_audit_collection_documents'])) $this->audit_collection_documents 	= ($_FILES['clsagreegatorcollection_audit_collection_documents']);

		if (isset($_POST['clsagreegatorcollection_source_address'])) $this->source_address 						= trim($_POST['clsagreegatorcollection_source_address']);
		if (isset($_POST['clsagreegatorcollection_destination_address'])) $this->destination_address 			= trim($_POST['clsagreegatorcollection_destination_address']);
		if (isset($_POST['clsagreegatorcollection_source_geo_location'])) $this->source_geo_location 			= trim($_POST['clsagreegatorcollection_source_geo_location']);
		if (isset($_POST['clsagreegatorcollection_destination_geo_location'])) $this->destination_geo_location 	= trim($_POST['clsagreegatorcollection_destination_geo_location']);
		if (isset($_POST['clsagreegatorcollection_travel_distance'])) $this->travel_distance 					= trim($_POST['clsagreegatorcollection_source_address']);
		if (isset($_POST['clsagreegatorcollection_source_lat'])) $this->source_lat 								= trim($_POST['clsagreegatorcollection_source_lat']);
		if (isset($_POST['clsagreegatorcollection_source_long'])) $this->source_long 							= trim($_POST['clsagreegatorcollection_source_long']);
		if (isset($_POST['clsagreegatorcollection_destination_lat'])) $this->destination_lat 					= trim($_POST['clsagreegatorcollection_destination_lat']);
		if (isset($_POST['clsagreegatorcollection_destination_long'])) $this->destination_long 					= trim($_POST['clsagreegatorcollection_destination_long']);
		
		if (isset($_POST['clsagreegatorcollection_created_by'])) 	$this->created_by 	= trim($_POST['clsagreegatorcollection_created_by']);
		if (isset($_POST['clsagreegatorcollection_created_dt'])) 	$this->created_dt 	= trim($_POST['clsagreegatorcollection_created_dt']);
		if (isset($_POST['clsagreegatorcollection_created_by'])) 	$this->updated_by 	= trim($_POST['clsagreegatorcollection_updated_by']);
		if (isset($_POST['clsagreegatorcollection_updated_dt'])) 	$this->updated_dt 	= trim($_POST['clsagreegatorcollection_updated_dt']);

		if (isset($_POST['clsagreegatorcollection_searchid'])) 	$this->searchid 		= trim($_POST['clsagreegatorcollection_searchid']);
		if (isset($_POST['clsagreegatorcollection_hdnaction']))	$this->action 			= trim($_POST['clsagreegatorcollection_hdnaction']);

		if (isset($_POST['clsagreegatorcollection_period']))		$this->period		= trim($_POST['clsagreegatorcollection_period']);
		if (isset($_POST['clsagreegatorcollection_searchdate']))	$this->searchdate	= trim($_POST['clsagreegatorcollection_searchdate']);
		if (isset($_POST['clsagreegatorcollection_startday']))		$this->startday		= trim($_POST['clsagreegatorcollection_startday']);
		if (isset($_POST['clsagreegatorcollection_startmonth']))	$this->startmonth	= trim($_POST['clsagreegatorcollection_startmonth']);
		if (isset($_POST['clsagreegatorcollection_startyear']))		$this->startyear	= trim($_POST['clsagreegatorcollection_startyear']);
		if (isset($_POST['clsagreegatorcollection_endday']))		$this->endday		= trim($_POST['clsagreegatorcollection_endday']);
		if (isset($_POST['clsagreegatorcollection_endmonth']))		$this->endmonth		= trim($_POST['clsagreegatorcollection_endmonth']);
		if (isset($_POST['clsagreegatorcollection_endyear']))		$this->endyear		= trim($_POST['clsagreegatorcollection_endyear']);

		if (!empty($this->source_lat) && !empty($this->source_long)) {
			$this->source_geo_location = serialize(array($this->source_lat,$this->source_long));
		}
		if (!empty($this->destination_lat) && !empty($this->destination_long)) {
			$this->destination_geo_location = serialize(array($this->destination_lat,$this->destination_long));
		}
		if (!empty($this->source_lat) && !empty($this->source_long) && !empty($this->destination_lat) && !empty($this->destination_long)) {
			$this->travel_distance = distance($this->source_lat,$this->source_long,$this->destination_lat,$this->destination_long,"K");
		}
	}

	/**
	* Function Name : setVars
	* @param
	* @return
	* @author Kalpak Prajapati
	*/
	function setVars()
	{
		$this->setgetvars();
		$this->setpostvars();
	}

	/**
	* Function Name : saveAgreegatorCollection
	* @param $id
	* @return
	* @author Kalpak Prajapati
	*/
	function saveAgreegatorCollection($id=0)
	{
		if($id == 0)
		{
			$this->status 			= (($this->status=='')?'I':$this->status);
			$this->challan_no 		= $this->GenerateChallanNo($this->now);
			$this->plain_vehicle_no = preg_replace("/[^a-z0-9]/i","",$this->vehicle_no);
			$query 				= "	INSERT INTO ".$this->tablename." SET
									agreegator_id 				= '".DBVarConv($this->agreegator_id)."',
									wm_client_master_id 		= '".DBVarConv($this->wm_client_master_id)."',
									vehicle_no 					= '".DBVarConv($this->vehicle_no)."',
									plain_vehicle_no 			= '".DBVarConv($this->plain_vehicle_no)."',
									driver_name 				= '".DBVarConv($this->driver_name)."',
									driver_contact_no 			= '".DBVarConv($this->driver_contact_no)."',
									weigh_slip_no 				= '".DBVarConv($this->weigh_slip_no)."',
									bill_t_no 					= '".DBVarConv($this->bill_t_no)."',
									challan_no 					= '".DBVarConv($this->challan_no)."',
									gross_weight 				= '".DBVarConv($this->gross_weight)."',
									tare_weight 				= '".DBVarConv($this->tare_weight)."',
									status						= '".DBVarConv($this->status)."',
									lattitude					= '".DBVarConv($this->lattitude)."',
									longitude					= '".DBVarConv($this->longitude)."',
									source_address				= '".DBVarConv($this->source_address)."',
									destination_address			= '".DBVarConv($this->destination_address)."',
									source_geo_location			= '".DBVarConv($this->source_geo_location)."',
									destination_geo_location	= '".DBVarConv($this->destination_geo_location)."',
									travel_distance				= '".DBVarConv($this->travel_distance)."',
									created_by					= '".$GLOBALS[SESSION_OBJ_NAME]->adminuserid."',
									created_dt					= '".$this->now."',
									updated_by					= '".$GLOBALS[SESSION_OBJ_NAME]->adminuserid."',
									updated_dt					= '".$this->now."'";
			$dbqry = new dbquery($query);
			$this->id = $dbqry->insertid;
			$this->SaveProductDetails();
			$this->SaveDispatchDocuments();
			$this->saveAgreegatorLocation();
			// $this->GenerateEwaybill($this->id);
			$this->SendNotificationToSalesTeam($this->id);
			$GLOBALS['LOGDATABASE']->log_action($GLOBALS['LOGDATABASE']->Agreegator_Collection_Added,$this->id,$this->tablename);
			$this->record_added = "Y";
			$GLOBALS[SESSION_OBJ_NAME]->addstatusmessage("Agreegator Collection information added successfully.");
			//$this->SendPushNotificationToFactory($this->id,$this->wm_client_master_id,$this->agreegator_id);
		}
		else
		{
			$query 	= "	UPDATE ".$this->tablename." SET
						agreegator_id 				= '".DBVarConv($this->agreegator_id)."',
						wm_client_master_id 		= '".DBVarConv($this->wm_client_master_id)."',
						vehicle_no 					= '".DBVarConv($this->vehicle_no)."',
						plain_vehicle_no 			= '".DBVarConv($this->plain_vehicle_no)."',
						driver_name 				= '".DBVarConv($this->driver_name)."',
						driver_contact_no 			= '".DBVarConv($this->driver_contact_no)."',
						weigh_slip_no 				= '".DBVarConv($this->weigh_slip_no)."',
						gross_weight 				= '".DBVarConv($this->gross_weight)."',
						tare_weight 				= '".DBVarConv($this->tare_weight)."',
						bill_t_no 					= '".DBVarConv($this->bill_t_no)."',
						source_address				= '".DBVarConv($this->source_address)."',
						destination_address			= '".DBVarConv($this->destination_address)."',
						source_geo_location			= '".DBVarConv($this->source_geo_location)."',
						destination_geo_location	= '".DBVarConv($this->destination_geo_location)."',
						travel_distance				= '".DBVarConv($this->travel_distance)."',
						status						= '".DBVarConv($this->status)."',
						updated_by					= '".$GLOBALS[SESSION_OBJ_NAME]->adminuserid."',
						updated_dt					= '".$this->now."'
	                    WHERE id					= '".$id."'";
			$dbqry = new dbquery($query);
			$this->SaveProductDetails();
			$this->SaveDispatchDocuments();
			$GLOBALS['LOGDATABASE']->log_action($GLOBALS['LOGDATABASE']->Agreegator_Collection_Updated,$id,$this->tablename);
			$this->record_updated = "Y";
			$GLOBALS[SESSION_OBJ_NAME]->addstatusmessage("Agreegator Collection information updated successfully.");
		}
	}

	/**
	* Function Name : deleteAgreegatorCollection
	* @param $id
	* @return
	* @author Kalpak Prajapati
	*/
	function deleteAgreegatorCollection($id=0)
	{
		$id 		= $id;
		$tablename 	= $this->tablename;
		$pk 		= 'id';

		if(is_array($id))
			$strIds = "'".join($id,"', '")."'";
		elseif($id!='')
			$strIds = "'".$id."'";
		$this->status = 3;
		$query 	= "	UPDATE ".$this->tablename." SET
					status		= '".DBVarConv($this->status)."',
					updated_by 	= '".$GLOBALS[SESSION_OBJ_NAME]->adminuserid."',
					updated_dt 	= '".$this->now."'
                    WHERE $pk IN (".$strIds.")";
		$dbqry = new dbquery($query);
		$GLOBALS['LOGDATABASE']->log_action($GLOBALS['LOGDATABASE']->Agreegator_Collection_Deleted,$id,$this->tablename);
		$GLOBALS[SESSION_OBJ_NAME]->addstatusmessage("Agreegator Collection information deleted successfully.");
	}

	/**
	* Function Name : retrieveAgreegatorCollection
	* @param $id
	* @return
	* @author Kalpak Prajapati
	*/
	function retrieveAgreegatorCollection($id,$FromUpdate=false)
	{
		$id 		= intval($id);
		$tablename 	= $this->tablename;
		$pk 		= 'id';
		$query 		= "	SELECT ".$this->tablename.".*,MA.name as supplier_name,CM.client_name,
						".$this->tablename.".unload_date,".$this->tablename.".audit_date,
						".$this->tablename.".invoice_date,
						UB.username as UnloadBy,UB.username as AuditBy,UB.username as ApprovedBy,
						adminuser.username as PaymentApproved_By,
						GetAgreegatorPaymentDueDate(MA.para_payment_cycle_type_id,".$this->tablename.".para_payment_status_type_id,".$this->tablename.".payment_approved_dt) as payment_due_date,
						DATE_FORMAT(".$this->tablename.".created_dt,'%Y-%m-%d') as `date_create`,
						DATE_FORMAT(".$this->tablename.".updated_dt,'%Y-%m-%d') as `date_update` 
						FROM ".$this->tablename."
						INNER JOIN macro_agreegators MA ON ".$this->tablename.".agreegator_id = MA.id
						INNER JOIN wm_client_master CM ON ".$this->tablename.".wm_client_master_id = CM.id
						LEFT JOIN macro_agreegator_users UB ON ".$this->tablename.".unload_by = UB.id
						LEFT JOIN macro_agreegator_users AB ON ".$this->tablename.".audit_by = AB.id
						LEFT JOIN macro_agreegator_users PAB ON ".$this->tablename.".approved_by = PAB.id
						LEFT JOIN adminuser ON ".$this->tablename.".payment_approved_by = adminuser.adminuserid
						WHERE $tablename.$pk = '$id'";
        $dbqry 		= new dbquery($query);
		if($dbqry->numrows() > 0)
		{
			$row = $dbqry->getrowarray(MYSQL_ASSOC);
			foreach ($row as $key=>$val) $this->$key = $val;
			if (!empty($this->source_geo_location)) {
				$source_location 	= unserialize($this->source_geo_location);
				$this->source_lat 	= $source_location[0];
				$this->source_long 	= $source_location[1];
			}
			if (!empty($this->destination_geo_location)) {
				$destination_location 	= unserialize($this->destination_geo_location);
				$this->destination_lat 	= $destination_location[0];
				$this->destination_long = $destination_location[1];
			}
			if (!empty($this->source_lat) && !empty($this->source_long) && !empty($this->destination_lat) && !empty($this->destination_long)) {
				$this->travel_distance = distance($this->source_lat,$this->source_long,$this->destination_lat,$this->destination_long,"K");
			}
			$this->retrieveCollectionProducts($this->id);
			$this->retrieveAgreegatorCollectionDocuments();
			$this->PaymentDetails = $this->retrievePaymentAmount($this->agreegator_id);
			if (!$FromUpdate) {
				$this->retrievePaymentChecklist($this->id);
				$this->retrieveScheduledPaymentPlan($this->id);
			}
		}
		else
		{
			return false;
		}
		return true;
	}

	/**
	* Function Name : retrieveCollectionProducts
	* @param $id
	* @return
	* @author Kalpak Prajapati
	*/
	function retrieveCollectionProducts($dispatch_id=0,$returnArray=false)
	{
		$id 		= $dispatch_id;
		$tablename 	= $this->tablename_products;
		$pk 		= 'dispatch_id';
		$query 		= "	SELECT $tablename.*,wm_product_master.title as Product_Name,
						wm_product_master.hsn_code AS HSN,'2.5' as CGST, '2.5' as SGST, '5.0' as IGST,'KGS' as UOM
						FROM $tablename
						INNER JOIN wm_product_master ON $tablename.product_id = wm_product_master.id
						WHERE $pk = '$id'";
        $dbqry 		= new dbquery($query);
        $arrResult	= array();
        $PostData	= array();
        if(!empty($this->arrProducts) && $returnArray == false) {
        	foreach ($this->arrProducts as $arrProduct) {
        		if (isset($arrProduct['audit_qty'])) {
        			$PostData[$arrProduct['product_id']] = array("audit_qty"=>$arrProduct['audit_qty'],"inert"=>$arrProduct['inert']);
        		}
        	}
        	$this->arrProducts = array();
        }
		if($dbqry->numrows() > 0) {
			while ($row = $dbqry->getrowarray(MYSQL_ASSOC)) {
				if ($returnArray) {
					$arrResult[$row['product_id']] = $row;
				} else {
					$row['audit_qty'] 		= isset($PostData[$row['product_id']]['audit_qty'])?$PostData[$row['product_id']]['audit_qty']:$row['audit_qty'];
					$row['inert'] 			= isset($PostData[$row['product_id']]['inert'])?$PostData[$row['product_id']]['inert']:$row['inert'];
					$row['net_weight'] 		= $row['qty'];
					$this->arrProducts[] 	= $row;
				}
			}
		} else {
			return false;
		}
		if ($returnArray) {
			return $arrResult;
		} else {
			return true;
		}
	}

    /**
	* Function Name : searchAgreegatorCollection
	* @param
	* @return
	* @author Kalpak Prajapati
	*/
	function searchAgreegatorCollection($FromMobile=false)
	{
		$arrResult = array();
		$COUNT  = "	SELECT COUNT(*) AS cnt ";
		$SELECT = "	SELECT ".$this->tablename.".*,MA.name as supplier_name,CM.client_name,
					CM.client_name as factory_name,
					UB.username as UnloadBy,UB.username as AuditBy,UB.username as ApprovedBy,
					adminuser.username as PaymentApproved_By,
					GetAgreegatorPaymentDueDate(MA.para_payment_cycle_type_id,".$this->tablename.".para_payment_status_type_id,".$this->tablename.".payment_approved_dt) as payment_due_date,
					DATE_FORMAT(".$this->tablename.".created_dt,'%Y-%m-%d') as `date_create`,
					DATE_FORMAT(".$this->tablename.".updated_dt,'%Y-%m-%d') as `date_update` ";
		$FROM   = "	FROM ".$this->tablename."
					INNER JOIN macro_agreegators MA ON ".$this->tablename.".agreegator_id = MA.id
					INNER JOIN wm_client_master CM ON ".$this->tablename.".wm_client_master_id = CM.id
					LEFT JOIN macro_agreegator_users UB ON ".$this->tablename.".unload_by = UB.id
					LEFT JOIN macro_agreegator_users AB ON ".$this->tablename.".audit_by = AB.id
					LEFT JOIN macro_agreegator_users PAB ON ".$this->tablename.".approved_by = PAB.id
					LEFT JOIN adminuser ON ".$this->tablename.".payment_approved_by = adminuser.adminuserid";
		$WHERE  = "	WHERE 1 = 1 ";

		if ($this->searchid != "" && preg_match("/[^0-9, ]/",$this->searchid) == false) {
			if($this->searchid != "")		$WHERE .= " AND ".$this->tablename.".id IN (".makeSearchIdString($this->searchid).")";
		}
		if ($this->wm_client_master_id != "" && preg_match("/[^0-9, ]/",$this->wm_client_master_id) == false) {
			$WHERE .= " AND ".$this->tablename.".wm_client_master_id IN (".makeSearchIdString($this->wm_client_master_id).")";
		}
		if ($this->agreegator_id != "" && preg_match("/[^0-9, ]/",$this->agreegator_id) == false) {
			$WHERE .= " AND ".$this->tablename.".agreegator_id IN (".makeSearchIdString($this->agreegator_id).")";
		}	
		if ($this->supplier_name!="") {
			$WHERE .= " AND (	MA.name LIKE '%".DBVarConv($this->supplier_name)."%'
								OR
								CM.client_name LIKE '%".DBVarConv($this->supplier_name)."%'
							)";
		}
		if ($this->vehicle_no!="") {
			$WHERE .= " AND LOWER(".$this->tablename.".plain_vehicle_no) LIKE '%".strtolower(DBVarConv($this->vehicle_no))."%'";
		}
		if ($this->driver_contact_no!="") {
			$WHERE .= " AND (".$this->tablename.".driver_contact_no) = '".(DBVarConv($this->driver_contact_no))."'";
		}
		if ($this->status!="") {
			$WHERE .= " AND ".$this->tablename.".status = '".DBVarConv($this->status)."' ";
		} else {
			$WHERE .= " AND ".$this->tablename.".status IN ('A','I') ";
		}
		if ($this->isFactoryAdmin)
		{
			$WHERE .= " AND ".$this->tablename.".queue_no > 0 ";
		}
		else if ($this->isAuditor)
		{
			$WHERE .= " AND ".$this->tablename.".is_audited = 0 AND ".$this->tablename.".queue_no > 0 ";
		}
		else if ($this->From_Security_Login)
		{
			$WHERE .= " AND (".$this->tablename.".queue_no <= 0 OR ".$this->tablename.".queue_no IS NULL) ";
		}
		else if ($this->From_Driver_Login)
		{
			$WHERE .= " AND ".$this->tablename.".unload = 0 AND (".$this->tablename.".queue_no <= 0 OR ".$this->tablename.".queue_no IS NULL)";
		}

		//TODO: Add other search conditions here

		if($this->period!=0 && $this->period!='')
		{
			$date = $this->searchdate != '' ? $this->searchdate : $this->tablename.".created_dt";
			$WHERE .=  queryByPeriod($date,
			$this->period,
			$this->startmonth,
			$this->startday,
			$this->startyear,
			$this->endmonth,
			$this->endday,
			$this->endyear);
		}

		//TODO: Add other search conditions here

		$querycount = $COUNT.$FROM.$WHERE;
		$dbqry = new dbquery($querycount);
		$row = $dbqry->getrowarray();
		$this->clspaging->numofrecs = $row["cnt"];
		if ($this->clspaging->numofrecs>0)
			$this->clspaging->getcurrpagevardb();
		else
			return $arrResult;		//No matching records found so returning empty array

		$query = $SELECT.$FROM.$WHERE;

		if ($this->clspaging->sortby == '')
			$this->clspaging->sortby = $this->DEFAULT_SORT_FLD;
		if ($this->clspaging->sorttype == '')
			$this->clspaging->sorttype = $this->DEFAULT_SORT_TYPE;
		if ($this->isAuditor || $this->isFactoryAdmin) {
			$query .= ' ORDER BY macro_agreegators_dispatch.unload DESC,
						macro_agreegators_dispatch.unload_date ASC,
						macro_agreegators_dispatch.queue_no ASC ';
		} else {
			if($this->clspaging->sortby != '') {
				$query .= ' ORDER BY '.$this->clspaging->sortby.' '. $this->clspaging->sorttype.' ';
			}
		}

		if($this->clspaging->limit != '') $query .= $this->clspaging->limit;

		$dbqry = new dbquery($query);

		if($dbqry->numrows()>0)
		{
			while($row=$dbqry->getrowarray(MYSQL_ASSOC))
			{
				if ($FromMobile) {
					$arrResult[] = $row;
				} else {
					$arrResult[$row['id']] = $row;
				}
				//TODO: You may change the result array format here
			}
		}
		
		return $arrResult;
	}

	/**
	* Function Name : validateEditAgreegatorCollection
	* @param
	* @return
	* @author Kalpak Prajapati
	*/
	function validateEditAgreegatorCollection()
	{
		//TODO: ADD VALIDATIONS REQUIRED FOR INSERT/UPDATE
		if (empty($this->agreegator_id)) $this->error[] = "Agreegator is required field.";
		if (empty($this->wm_client_master_id)) $this->error[] = "Factory is required field.";
		if (!empty($this->wm_client_master_id) && !$this->ValidateAllowedCustomer($this->agreegator_id,$this->wm_client_master_id)) $this->error[] = "You are not allowed to dispatch for this factory.";
		if (empty($this->vehicle_no)) $this->error[] = "Vehicle No is required field.";
		if (empty($this->driver_contact_no)) $this->error[] = "Driver Contact is required field.";
		if (!ValidateInputString($this->driver_contact_no)) $this->error[] = "Please enter valid driver contact.";
		if (empty($this->arrProducts)) $this->error[] = "Please enter at-least one product for dispatch.";
		if (!empty($this->arrProducts) && !empty($this->wm_client_master_id)) {
			$arrAllowedProducts = $this->GetAllowedProducts($this->wm_client_master_id);
			if (empty($arrAllowedProducts)) {
				$this->error[] = "You are not allowed to dispatch one of selected products.";
			} else {
				$ProductValidation = true;
				foreach ($this->arrProducts as $key => $arrProduct) {
					$Quantity 		= isset($arrProduct['qty'])?$arrProduct['qty']:0;
					$Quantity 		= isset($arrProduct['net_weight'])?$arrProduct['net_weight']:$Quantity;
					$gross_weight 	= isset($arrProduct['gross_weight'])?$arrProduct['gross_weight']:0;
					if (!in_array($arrProduct['product_id'],$arrAllowedProducts)) {
						$ProductValidation = false;
					} else if (empty($Quantity) || preg_match("/[^0-9\.]/",$Quantity)) {
						$ProductValidation = false;
					} else if (preg_match("/[^0-9\.]/",$gross_weight)) {
						$ProductValidation = false;
					}
				}
				if (!$ProductValidation) {
					$this->error[] = "You are not allowed to dispatch one of selected products or you have entered wrong Net Weight / Gross Weight in one of the product to be dispatched.";
				}
			}
		}
		return (count($this->error)>0)?false:true;
	}

	/**
	* Function Name : ValidateAllowedCustomer
	* @param $agreegator_id
	* @param $wm_client_master_id
	* @return boolean
	* @author Kalpak Prajapati
	*/
	function ValidateAllowedCustomer($agreegator_id=0,$wm_client_master_id=0)
	{
		$tablename = "macro_agreegators_allowed_customers";
		$agreegator_id = intval($agreegator_id);
		$wm_client_master_id = intval($wm_client_master_id);
		$query 	= "	SELECT COUNT(*) as `tot` FROM $tablename 
					WHERE agreegator_id = $agreegator_id
					AND wm_client_master_id = $wm_client_master_id
					AND status = 1";
		$dbqry = new dbquery($query);
		$row = $dbqry->getrowarray();
		if ($row['tot'] > 0)
			return true;
		else
			return false;
	}

	/**
	* Function Name : GetAllowedProducts
	* @param $wm_client_master_id
	* @return $arrResult
	* @author Kalpak Prajapati
	*/
	function GetAllowedProducts($wm_client_master_id=0)
	{
		$tablename 				= "macro_agreegators_products";
		$wm_client_master_id 	= intval($wm_client_master_id);
		$query 					= "	SELECT $tablename.product_id 
									FROM $tablename 
									INNER JOIN macro_agreegators ON $tablename.agreegator_id = macro_agreegators.id
									WHERE wm_client_master_id = $wm_client_master_id";
		$dbqry 					= new dbquery($query);
		$arrResult 				= array();
		if($dbqry->numrows()>0)
		{
			while($row=$dbqry->getrowarray(MYSQL_ASSOC))
			{
				$arrResult[] = $row['product_id'];
			}
		}
		return $arrResult;
	}

	//TODO: Add other validation functions here

	/**
	* Function Name : pageadminEditAgreegatorCollection
	* @param
	* @return
	* @author Kalpak Prajapati
	*/
	function pageadminEditAgreegatorCollection($FromMobile=false)
	{
		$this->setgetvars();
		$this->setpostvars();
		
		$this->created_by 			= isset($GLOBALS[SESSION_OBJ_NAME]->adminuserid)?$GLOBALS[SESSION_OBJ_NAME]->adminuserid:$this->created_by;
		$this->updated_by 			= isset($GLOBALS[SESSION_OBJ_NAME]->adminuserid)?$GLOBALS[SESSION_OBJ_NAME]->adminuserid:$this->updated_by;
		$this->requested_by 		= isset($GLOBALS[SESSION_OBJ_NAME]->adminuserid)?$GLOBALS[SESSION_OBJ_NAME]->adminuserid:$this->requested_by;
		$this->payment_approved_by 	= isset($GLOBALS[SESSION_OBJ_NAME]->adminuserid)?$GLOBALS[SESSION_OBJ_NAME]->adminuserid:$this->requested_by;
		
		if ($this->submited)
		{
			if ($this->action == 'UPDATE_Agreegator_Collection')
			{
				if($this->validateEditAgreegatorCollection())
				{
					//TODO: Set other properties here

					$this->saveAgreegatorCollection($this->id);
					header("location:manageagreegatorcollection.php?id=".$this->id."&agreegator_id=".$this->agreegator_id."&showmenu=".$this->showmenu);
					exit;
				}
			}
			if ($this->action == 'EDIT_Agreegator_Collection')
			{
				//TODO: Set other properties here

				$this->retrieveAgreegatorCollection($this->id);
				return;
			}

			if ($this->action == 'Save_Agreegator_Collection_Payment_Plan')
			{
				//TODO: Set other properties here
				if($this->validateEditAgreegatorCollectionPaymentPlan())
				{
					$this->saveAgreegatorCheckPoints($this->id);
					$this->saveAgreegatorCollectionPaymentPlan($this->id);
					$this->saveDispatchPaymentStatus($this->id,$this->PAYMENT_PLAN_INITIATED);
					if (!$FromMobile) {
						header("location:manageagreegatorcollectionpaymentplan.php?id=".$this->id."&showmenu=".$this->showmenu);
						exit;
					}
				} else {
					return $this->retrieveAgreegatorCollection($this->id,true);
				}
			}

			if ($this->action == 'Schedule_Agreegator_Collection_Payment_Plan')
			{
				//TODO: Set other properties here
				if($this->validateScheduleAgreegatorCollectionPaymentPlan())
				{
					$this->created_by 			= $this->agreegator_id;
					$this->updated_by 			= $this->agreegator_id;
					$this->requested_by 		= $this->agreegator_id;
					$this->payment_approved_by 	= $this->agreegator_id;
					$this->saveAgreegatorCollectionPaymentPlan($this->id);
				}
			}

			if ($this->action == 'Save_And_Approved_Agreegator_Collection_Payment_Plan')
			{
				//TODO: Set other properties here
				if($this->validateEditAgreegatorCollectionPaymentPlan())
				{
					$this->saveAgreegatorCheckPoints($this->id);
					$this->saveAgreegatorCollectionPaymentPlan($this->id);
					$this->saveDispatchPaymentStatus($this->id,$this->PAYMENT_APPROVED);
					if (!$FromMobile) {
						header("location:manageagreegatorcollectionpaymentplan.php?id=".$this->id."&showmenu=".$this->showmenu);
						exit;
					}
				} else {
					return $this->retrieveAgreegatorCollection($this->id,true);
				}
			}
		}
		else
		{
			if ($this->id != '')
			{
				//TODO: Set other properties here

				$this->retrieveAgreegatorCollection($this->id);
			}
		}

	}

	/**
	* Function Name : pageadminListAgreegatorCollection
	* @param
	* @return
	* @author Kalpak Prajapati
	*/
	function pageadminListAgreegatorCollection($defaultList=false)
	{
		$this->setgetvars();
		$this->setpostvars();
		$this->clspaging->setgetvars();
		$this->clspaging->setpostvars();

		$this->created_by = isset($GLOBALS[SESSION_OBJ_NAME]->adminuserid)?$GLOBALS[SESSION_OBJ_NAME]->adminuserid:$this->created_by;
		$this->updated_by = isset($GLOBALS[SESSION_OBJ_NAME]->adminuserid)?$GLOBALS[SESSION_OBJ_NAME]->adminuserid:$this->updated_by;
		
		if ($this->submited)
		{
			if ($this->action == 'SEARCH_Agreegator_Collection')
			{
				//TODO: Set other properties here (if required)

				return $this->searchAgreegatorCollection();
			}

			if ($this->action == 'DELETE_Agreegator_Collection')
			{
				$this->deleteAgreegatorCollection($this->id);
				header("location:listagreegatorcollection.php");
				exit;
			}
		}
		else
		{
			//TODO: Set other properties here
			return $this->searchAgreegatorCollection();
		}
	}

	/**
	* Function Name : retrieveAgreegatorCollectionListByFilterCollection
	* @param $arrFilter array()
	* @param $orderby
	* @return $arrResult array()
	* @author Kalpak Prajapati
	*/
	function retrieveAgreegatorCollectionListByFilter($arrFilter="",$orderby="id ASC")
	{
		$tablename = $this->tablename;
		$query = "SELECT * FROM $tablename WHERE 1=1 ";
		if (is_array($arrFilter) && count($arrFilter)>0)
			$query .= " AND ".join(' AND ',$arrFilter);
		elseif($arrFilter!="")
			$query .= " AND $arrFilter";

		if($orderby!="")
		$query .= " ORDER BY ".$orderby;

		$dbqry = new dbquery($query);
		$arrResult = array();
		if($dbqry->numrows() > 0)
		{
			while($row = $dbqry->getrowarray(MYSQL_ASSOC))
			{
				$arrResult[$row['id']] = $row;
			}
		}
		return $arrResult;
	}

    /** Function to check duplicate Agreegators Collection.
    * Function Name checkDuplicateAgreegatorCollection
	* @param array/string $arrFilter
    * @return boolean
	* @author Kalpak Prajapati
	*/
    function checkDuplicateAgreegatorCollection($arrFilter="")
	{
		$tablename = $this->tablename;
		$query = "SELECT COUNT(*) as `tot` FROM $tablename WHERE 1";

		if (is_array($arrFilter) && count($arrFilter)>0)
			$query .= " AND ".join(' AND ',$arrFilter);
		elseif($arrFilter!="")
			$query .= " AND $arrFilter";

		$dbqry = new dbquery($query);
		$row = $dbqry->getrowarray();
		if ($row['tot'] > 0)
			return false;
		else
			return true;
	}
	
	/**
    * Function Name SaveProductDetails
	* @param 
    * @return
	* @author Kalpak Prajapati
	*/
	function SaveProductDetails()
	{
		include_once(PATH_ABSOLUTE_CLASS.'agreegators.cls.php');
		$clsagreegator = new clsagreegator();
		if (!empty($this->arrProducts)) {
			foreach ($this->arrProducts as $key => $arrProduct)
			{
				$arrFilter 		= array("product_id = '".$arrProduct['product_id']."'");
				$arrProductInfo = $clsagreegator->GetProductDetails($arrFilter);
				if (!empty($arrProductInfo) && isset($arrProductInfo[$arrProduct['product_id']])) 
				{
					$DispatchProduct 	= "	SELECT count(0) AS CNT FROM ".$this->tablename_products."
											WHERE dispatch_id 	= '".DBVarConv($this->id)."'
											AND product_id 		= '".DBVarConv($arrProduct['product_id'])."'";
					$DispatchProductRes = new dbquery($DispatchProduct);
					$DispatchProductRow = $DispatchProductRes->getrowarray(MYSQL_ASSOC);

					$Quantity 		= isset($arrProduct['qty'])?$arrProduct['qty']:0;
					$Quantity 		= isset($arrProduct['net_weight'])?$arrProduct['net_weight']:$Quantity;
					$gross_weight 	= isset($arrProduct['gross_weight'])?$arrProduct['gross_weight']:0;

					if ($DispatchProductRow['CNT'] == 0)
					{
						$query 				= "	INSERT INTO ".$this->tablename_products." SET
												dispatch_id 				= '".DBVarConv($this->id)."',
												product_id 					= '".DBVarConv($arrProduct['product_id'])."',
												qty 						= '".DBVarConv($Quantity)."',
												gross_weight 				= '".DBVarConv($gross_weight)."',
												rate 						= '".DBVarConv($arrProductInfo[$arrProduct['product_id']]['rate'])."',
												factory_rate 				= '".DBVarConv($arrProductInfo[$arrProduct['product_id']]['factory_rate'])."',
												created_by					= '".$this->created_by."',
												created_dt					= '".$this->now."',
												updated_by					= '".$this->updated_by."',
												updated_dt					= '".$this->now."'";
						$dbqry = new dbquery($query);
					} else {
						$query 				= "	UPDATE ".$this->tablename_products." SET
												qty 						= '".DBVarConv($Quantity)."',
												gross_weight 				= '".DBVarConv($gross_weight)."',
												updated_by					= '".$this->updated_by."',
												updated_dt					= '".$this->now."'
												WHERE dispatch_id 	= '".DBVarConv($this->id)."'
												AND product_id 		= '".DBVarConv($arrProduct['product_id'])."'";
						$dbqry = new dbquery($query);
					}
				}
			}
		}
	}
	
	/**
    * Function Name SaveDispatchDocuments
	* @param 
    * @return
	* @author Kalpak Prajapati
	*/
	function SaveDispatchDocuments()
	{
		if(!is_dir(PATH_ABSOLUTE_HTTP_IMAGES_AGREEGATORS_COLLECTION.$this->id)) {
			mkdir(PATH_ABSOLUTE_HTTP_IMAGES_AGREEGATORS_COLLECTION.$this->id);
		}
		/* FOR NORMAL IMAGE */
		$NORMAL_IMAGE_NAME = "";
		if (isset($this->waybridge['name']) && !empty($this->waybridge['name'])) 
		{
			$extension 			= substr(strrchr($this->waybridge['name'], "."), 1);
			$filename 			= date("Ymdhis")."_waybridge.".strtolower($extension);
			$SERVER_IMAGE_NAME	= PATH_ABSOLUTE_HTTP_IMAGES_AGREEGATORS_COLLECTION.$this->id."/".$filename;
			move_uploaded_file($this->waybridge['tmp_name'],$SERVER_IMAGE_NAME);
			$RISIZE				= PATH_ABSOLUTE_HTTP_IMAGES_AGREEGATORS_COLLECTION.$this->id."/resize_".basename($SERVER_IMAGE_NAME);
			system("convert ".$SERVER_IMAGE_NAME." -resize 116x116 -quality 100 ".$RISIZE);
			$NORMAL_IMAGE_NAME = basename($SERVER_IMAGE_NAME);

			$query 	= "	INSERT INTO ".$this->tablename_documents." SET
						dispatch_id 					= '".DBVarConv($this->id)."',
						para_dispatch_document_type_id 	= '".DBVarConv($this->DOCUMENT_TYPE_WAYBRIDGE)."',
						filename 						= '".DBVarConv($this->waybridge['name'])."',
						filename_server 				= '".DBVarConv($NORMAL_IMAGE_NAME)."',
						created_by						= '".$this->created_by."',
						created_dt						= '".$this->now."',
						updated_by						= '".$this->updated_by."',
						updated_dt						= '".$this->now."'";
			$dbqry = new dbquery($query);
		}
		if (isset($this->licence_copy['name']) && !empty($this->licence_copy['name'])) 
		{
			$extension 			= substr(strrchr($this->licence_copy['name'], "."), 1);
			$filename 			= date("Ymdhis")."_licence_copy.".strtolower($extension);
			$SERVER_IMAGE_NAME	= PATH_ABSOLUTE_HTTP_IMAGES_AGREEGATORS_COLLECTION.$this->id."/".$filename;
			move_uploaded_file($this->licence_copy['tmp_name'],$SERVER_IMAGE_NAME);
			$RISIZE				= PATH_ABSOLUTE_HTTP_IMAGES_AGREEGATORS_COLLECTION.$this->id."/resize_".basename($SERVER_IMAGE_NAME);
			system("convert ".$SERVER_IMAGE_NAME." -resize 116x116 -quality 100 ".$RISIZE);
			$NORMAL_IMAGE_NAME = basename($SERVER_IMAGE_NAME);

			$query 	= "	INSERT INTO ".$this->tablename_documents." SET
						dispatch_id 					= '".DBVarConv($this->id)."',
						para_dispatch_document_type_id 	= '".DBVarConv($this->DOCUMENT_TYPE_LICENCE_DOCS)."',
						filename 						= '".DBVarConv($this->licence_copy['name'])."',
						filename_server 				= '".DBVarConv($NORMAL_IMAGE_NAME)."',
						created_by						= '".$this->created_by."',
						created_dt						= '".$this->now."',
						updated_by						= '".$this->updated_by."',
						updated_dt						= '".$this->now."'";
			$dbqry = new dbquery($query);
		}
		if (isset($this->vehicle_copy['name']) && !empty($this->vehicle_copy['name'])) 
		{
			$extension 			= substr(strrchr($this->vehicle_copy['name'], "."), 1);
			$filename 			= date("Ymdhis")."_vehicle_copy.".strtolower($extension);
			$SERVER_IMAGE_NAME	= PATH_ABSOLUTE_HTTP_IMAGES_AGREEGATORS_COLLECTION.$this->id."/".$filename;
			move_uploaded_file($this->vehicle_copy['tmp_name'],$SERVER_IMAGE_NAME);
			$RISIZE				= PATH_ABSOLUTE_HTTP_IMAGES_AGREEGATORS_COLLECTION.$this->id."/resize_".basename($SERVER_IMAGE_NAME);
			system("convert ".$SERVER_IMAGE_NAME." -resize 116x116 -quality 100 ".$RISIZE);
			$NORMAL_IMAGE_NAME = basename($SERVER_IMAGE_NAME);

			$query 	= "	INSERT INTO ".$this->tablename_documents." SET
						dispatch_id 					= '".DBVarConv($this->id)."',
						para_dispatch_document_type_id 	= '".DBVarConv($this->DOCUMENT_TYPE_VEHICLE_DOCS)."',
						filename 						= '".DBVarConv($this->vehicle_copy['name'])."',
						filename_server 				= '".DBVarConv($NORMAL_IMAGE_NAME)."',
						created_by						= '".$this->created_by."',
						created_dt						= '".$this->now."',
						updated_by						= '".$this->updated_by."',
						updated_dt						= '".$this->now."'";
			$dbqry = new dbquery($query);
		}
		/* FOR NORMAL IMAGE */
	}

	/**
	* Function Name : pageadminListAuditAgreegatorCollection
	* @param
	* @return
	* @author Kalpak Prajapati
	*/
	function pageadminListAuditAgreegatorCollection($redirect=true)
	{
		$this->setgetvars();
		$this->setpostvars();
		$this->clspaging->setgetvars();
		$this->clspaging->setpostvars();

		$this->created_by = isset($GLOBALS[SESSION_OBJ_NAME]->adminuserid)?$GLOBALS[SESSION_OBJ_NAME]->adminuserid:$this->created_by;
		$this->updated_by = isset($GLOBALS[SESSION_OBJ_NAME]->adminuserid)?$GLOBALS[SESSION_OBJ_NAME]->adminuserid:$this->updated_by;

		if ($this->submited)
		{
			if ($this->action == 'Audit_Agreegator_Collection')
			{
				if ($this->validateAuditCollection()) {
					$this->audit_by = isset($GLOBALS[SESSION_OBJ_NAME]->adminuserid)?$GLOBALS[SESSION_OBJ_NAME]->adminuserid:$this->audit_by;
					$this->AuditAgreegatorCollection();
					$this->para_dispatch_document_type_id = $this->DOCUMENT_TYPE_AUDIT_COLLECTION_PHOTOS;
					$this->UploadAuditAgreegatorCollectionDocuments();
					$this->SendAuditNotificationToSalesTeam($this->id);
					if ($redirect) {
						header("location:auditagreegatorcollection.php?id=".$this->id."&showmenu=".$this->showmenu);
						exit;
					} else {
						return true;
					}
				}
			}

			if ($this->action == 'Unload_Agreegator_Collection')
			{
				if ($this->validateUnloadCollection()) {
					$this->unload_by = isset($GLOBALS[SESSION_OBJ_NAME]->adminuserid)?$GLOBALS[SESSION_OBJ_NAME]->adminuserid:$this->unload_by;
					$this->UnloadAgreegatorCollection();
					$this->para_dispatch_document_type_id = $this->DOCUMENT_TYPE_UNLOAD_PHOTOS;
					$this->UploadAuditAgreegatorCollectionDocuments();
					$this->SendUnloadNotificationToSalesTeam($this->id);
					if ($redirect) {
						header("location:unloadagreegatorcollection.php?id=".$this->id."&showmenu=".$this->showmenu);
						exit;
					} else {
						return true;
					}
				}
			}

			if ($this->action == 'Approve_Agreegator_Collection')
			{
				if ($this->validateGenerateInvoiceCollection()) {
					$this->approved_by = isset($GLOBALS[SESSION_OBJ_NAME]->adminuserid)?$GLOBALS[SESSION_OBJ_NAME]->adminuserid:$this->approved_by;
					$this->GenerateAgreegatorCollectionInvoice();
					$this->para_dispatch_document_type_id = $this->DOCUMENT_TYPE_PAYMENT_DOCS;
					$this->UploadAuditAgreegatorCollectionDocuments();
					if ($redirect) {
						header("location:approveagreegatorcollection.php?id=".$this->id."&showmenu=".$this->showmenu);
						exit;
					} else {
						return true;
					}
				}
			}

			return $this->retrieveAgreegatorCollection($this->id);
		}
		else
		{
			//TODO: Set other properties here
			return $this->retrieveAgreegatorCollection($this->id);
		}
	}

	/**
	* Function Name : validateAuditCollection
	* @param
	* @return
	* @author Kalpak Prajapati
	*/
	function validateAuditCollection()
	{
		//TODO: ADD VALIDATIONS REQUIRED FOR INSERT/UPDATE
		if (empty($this->id) || !ctype_digit($this->id)) $this->error[] = "Audit Collection ID is required field.";
		if (empty($this->arrProducts)) $this->error[] = "Product details required for auditing process.";
		$arrFilter['id'] 	= intval($this->id);
		$arrCollections 	= $this->retrieveAgreegatorCollectionListByFilter($arrFilter);
		if (empty($arrCollections) || !isset($arrCollections[$this->id])) $this->error[] = "Invalid Collection details.";
		if (!empty($arrCollections) && isset($arrCollections[$this->id])) {
			if ($arrCollections[$this->id]['is_audited'] == 1) {
				$this->error[] = "Material is already audited at factory site.";
			}
		}
		if (empty($this->arrProducts)) $this->error[] = "No material has been selected for audit process.";
		$arrProducts = $this->retrieveCollectionProducts($this->id,true);
		if (!empty($this->arrProducts)) {
			foreach ( $this->arrProducts as $Product) {
				$OriginalQty 	= isset($arrProducts[$Product['product_id']]['qty'])?$arrProducts[$Product['product_id']]['qty']:0;
				$AuditQty 		= ($Product['audit_qty']+$Product['inert']);
				if (floatval($OriginalQty) < floatval($AuditQty)) {
					$this->error[] = "Audit quantity cannot be greater than dispatch quantity [Original Qty: $OriginalQty] [Audit Qty: $AuditQty].";
				}
			}
		}
		if (!empty($this->audit_collection_documents) && empty($this->error)) {
			foreach ($this->audit_collection_documents as $audit_collection_documents) {
				if (isset($audit_collection_documents['name']) && !empty($audit_collection_documents['name'])) {
					$icon		 = $audit_collection_documents['name'];
					$extension	 = substr(strrchr($icon, '.'), 1);
					$extension	 = strtolower($extension);
					if (!in_array($extension,$this->VALID_IMG_TYPE)) {
						$this->error[] = "Please Upload image with ".join(", ",$this->VALID_IMG_TYPE)." Extension.";
						break;
				 	}
				}
			}
		}
		return (count($this->error)>0)?false:true;
	}



	/**
	* Function Name : validateCollectionDocument
	* @param
	* @return
	* @author Kalpak Prajapati
	*/
	function validateCollectionDocument()
	{
		//TODO: ADD VALIDATIONS REQUIRED FOR INSERT/UPDATE
		if (empty($this->id) || !ctype_digit($this->id)) $this->error[] = "Audit Collection ID is required field.";
		$arrFilter['id'] 	= intval($this->id);
		$arrCollections 	= $this->retrieveAgreegatorCollectionListByFilter($arrFilter);
		if (empty($arrCollections) || !isset($arrCollections[$this->id])) $this->error[] = "Invalid Collection details.";
		if (!empty($this->audit_collection_documents) && empty($this->error)) {
			foreach ($this->audit_collection_documents as $audit_collection_documents) {
				if (isset($audit_collection_documents['name']) && !empty($audit_collection_documents['name'])) {
					$icon		 = $audit_collection_documents['name'];
					$extension	 = substr(strrchr($icon, '.'), 1);
					$extension	 = strtolower($extension);
					if (!in_array($extension,$this->VALID_IMG_TYPE)) {
						$this->error[] = "Please Upload image with ".join(", ",$this->VALID_IMG_TYPE)." Extension.";
						break;
				 	}
				}
			}
		}
		return (count($this->error)>0)?false:true;
	}

	/**
    * Function Name AuditAgreegatorCollection
	* @param 
    * @return
	* @author Kalpak Prajapati
	*/
	function AuditAgreegatorCollection()
	{
		if (!empty($this->arrProducts)) {
			foreach($this->arrProducts as $product_id) 
			{
				$Remarks 			= isset($product_id['remarks'])?($product_id['remarks']):"";
				$audit_loss_tags	= isset($product_id['audit_loss_tags'])?($product_id['audit_loss_tags']):"";
				$query 	= "	UPDATE ".$this->tablename_products." SET
							audit_qty					= '".DBVarConv(floatval($product_id['audit_qty']))."',
							inert						= '".DBVarConv(floatval($product_id['inert']))."',
							remarks						= '".DBVarConv($Remarks)."',
							audit_loss_tags				= '".DBVarConv($audit_loss_tags)."',
							updated_by					= '".$this->updated_by."',
							updated_dt					= '".$this->now."'
		                    WHERE dispatch_id 			= '".DBVarConv($this->id)."'
		                    AND product_id 				= '".DBVarConv(intval($product_id['product_id']))."'";
				$dbqry = new dbquery($query);
			}
			$query 	= "	UPDATE ".$this->tablename." SET
						is_audited					= '1',
						audit_by					= '".DBVarConv($this->audit_by)."',
						audit_date					= '".$this->now."',
						updated_by					= '".$this->updated_by."',
						updated_dt					= '".$this->now."'
	                    WHERE id 					= '".DBVarConv($this->id)."'";
			$dbqry = new dbquery($query);
			$this->SendAuditNotification($this->id);
			$GLOBALS['LOGDATABASE']->log_action($GLOBALS['LOGDATABASE']->Agreegator_Collection_Audited,$this->id,$this->tablename);
			$GLOBALS[SESSION_OBJ_NAME]->addstatusmessage("Agreegator Collection information audited successfully.");
		}
	}

	/**
	* Function Name : validateUnloadCollection
	* @param
	* @return
	* @author Kalpak Prajapati
	*/
	function validateUnloadCollection()
	{
		//TODO: ADD VALIDATIONS REQUIRED FOR INSERT/UPDATE
		if (empty($this->id) || !ctype_digit($this->id)) $this->error[] = "Audit Collection ID is required field.";
		$arrFilter 			= array("id='".intval($this->id)."'");
		$arrCollections 	= $this->retrieveAgreegatorCollectionListByFilter($arrFilter);
		if (empty($arrCollections) || !isset($arrCollections[$this->id])) $this->error[] = "Invalid Collection details.";
		if (!empty($arrCollections) && isset($arrCollections[$this->id])) {
			if ($arrCollections[$this->id]['unload'] == 1) {
				$this->error[] = "Material is already unloaded at factory site.";
			}
		}
		if (!empty($this->audit_collection_documents) && empty($this->error)) {
			foreach ($this->audit_collection_documents as $audit_collection_documents) {
				if (isset($audit_collection_documents['name']) && !empty($audit_collection_documents['name'])) {
					$icon		 = $audit_collection_documents['name'];
					$extension	 = substr(strrchr($icon, '.'), 1);
					$extension	 = strtolower($extension);
					if (!in_array($extension,$this->VALID_IMG_TYPE)) {
						$this->error[] = "Please Upload image with ".join(", ",$this->VALID_IMG_TYPE)." Extension.";
						break;
				 	}
				}
			}
		}
		return (count($this->error)>0)?false:true;
	}

	/**
    * Function Name UnloadAgreegatorCollection
	* @param 
    * @return
	* @author Kalpak Prajapati
	*/
	function UnloadAgreegatorCollection()
	{
		$query 	= "	UPDATE ".$this->tablename." SET
					unload						= '1',
					unload_date					= '".$this->now."',
					unload_by					= '".$this->unload_by."',
					updated_by					= '".$this->updated_by."',
					updated_dt					= '".$this->now."'
                    WHERE id 					= '".DBVarConv($this->id)."'";
		$dbqry = new dbquery($query);
		$this->SendUnloadNotification($this->id);
		$GLOBALS['LOGDATABASE']->log_action($GLOBALS['LOGDATABASE']->Agreegator_Collection_Unloaded,$this->id,$this->tablename);
		$GLOBALS[SESSION_OBJ_NAME]->addstatusmessage("Agreegator Collection unloaded successfully.");
	}

	/**
    * Function Name GenerateAgreegatorCollectionInvoice
	* @param 
    * @return
	* @author Kalpak Prajapati
	*/
	function GenerateAgreegatorCollectionInvoice()
	{
		$this->sales_invoice_no = $this->GenerateSalesNo($this->invoice_date);
		$this->credit_note_no	= $this->GetCreditNoteDetails($this->id,$this->invoice_date);
		$query 	= "	UPDATE ".$this->tablename." SET
					invoice_generated			= '1',
					invoice_sent				= '1',
					para_payment_status_type_id	= '".$this->PAYMENT_PLAN_INITIATED."',
					sales_invoice_no			= '".$this->sales_invoice_no."',
					credit_note_no				= '".$this->credit_note_no."',
					invoice_date				= '".$this->invoice_date."',
					approved_by					= '".$this->approved_by."',
					updated_by					= '".$this->updated_by."',
					updated_dt					= '".$this->now."'
                    WHERE id 					= '".DBVarConv($this->id)."'";
		$dbqry = new dbquery($query);
		$GLOBALS['LOGDATABASE']->log_action($GLOBALS['LOGDATABASE']->Agreegator_Collection_Approved,$this->id,$this->tablename);
		$GLOBALS[SESSION_OBJ_NAME]->addstatusmessage("Agreegator Collection Approved successfully.");
	}

	/**
    * Function Name GetCreditNoteDetails
	* @param $dispatch_id
	* @param $invoice_date
    * @return
	* @author Kalpak Prajapati
	*/
	function GetCreditNoteDetails($dispatch_id=0,$invoice_date="")
	{
		$DispatchProduct 	= "	SELECT count(0) AS CNT FROM ".$this->tablename_products."
								WHERE dispatch_id = '".DBVarConv($dispatch_id)."' AND qty != audit_qty";
		$DispatchProductRes = new dbquery($DispatchProduct);
		$DispatchProductRow	= $DispatchProductRes->getrowarray(MYSQL_ASSOC);
		if ($DispatchProductRow['CNT'] > 0) {
			return $this->GenerateCreditNoteNo($invoice_date);
		}
		return "";
	}

	/**
	* Function Name : validateGenerateInvoiceCollection
	* @param
	* @return
	* @author Kalpak Prajapati
	*/
	function validateGenerateInvoiceCollection()
	{
		//TODO: ADD VALIDATIONS REQUIRED FOR INSERT/UPDATE
		if (empty($this->id) || !ctype_digit($this->id)) $this->error[] = "Audit Collection ID is required field.";
		// if (empty($this->sales_invoice_no)) $this->error[] = "Invoice No. is required field.";
		$arrFilter 			= array("id='".intval($this->id)."'");
		$arrCollections 	= $this->retrieveAgreegatorCollectionListByFilter($arrFilter);
		if (empty($arrCollections) || !isset($arrCollections[$this->id])) $this->error[] = "Invalid Collection details.";
		if (!empty($arrCollections) && isset($arrCollections[$this->id])) {
			if ($arrCollections[$this->id]['unload'] != 1) {
				$this->error[] = "Audit Collection is yet to unload.";
			} else if ($arrCollections[$this->id]['is_audited'] != 1) {
				$this->error[] = "Audit Collection is yet not audited at factory site.";
			}
		}
		return (count($this->error)>0)?false:true;
	}
	
	/**
    * Function Name UploadAuditAgreegatorCollectionDocuments
	* @param 
    * @return
	* @author Kalpak Prajapati
	*/
	function UploadAuditAgreegatorCollectionDocuments()
	{
		if(!is_dir(PATH_ABSOLUTE_HTTP_IMAGES_AGREEGATORS_COLLECTION.$this->id)) {
			mkdir(PATH_ABSOLUTE_HTTP_IMAGES_AGREEGATORS_COLLECTION.$this->id);
		}
		/* FOR NORMAL IMAGE */
		$NORMAL_IMAGE_NAME = "";
		if (!empty($this->audit_collection_documents['name'])) {
			foreach ($this->audit_collection_documents['name'] as $key => $audit_collection_documents) 
			{
				if (!empty($audit_collection_documents) && $this->audit_collection_documents['error'][$key] == 0)
				{
					$extension 			= substr(strrchr($audit_collection_documents, "."), 1);
					$filename 			= date("Ymdhis")."_audit_photos.".strtolower($extension);
					$SERVER_IMAGE_NAME	= PATH_ABSOLUTE_HTTP_IMAGES_AGREEGATORS_COLLECTION.$this->id."/".$filename;
					move_uploaded_file($this->audit_collection_documents['tmp_name'][$key],$SERVER_IMAGE_NAME);
					$RISIZE				= PATH_ABSOLUTE_HTTP_IMAGES_AGREEGATORS_COLLECTION.$this->id."/resize_".basename($SERVER_IMAGE_NAME);
					system("convert ".$SERVER_IMAGE_NAME." -resize 116x116 -quality 100 ".$RISIZE);
					$NORMAL_IMAGE_NAME = basename($SERVER_IMAGE_NAME);

					$query 	= "	INSERT INTO ".$this->tablename_documents." SET
								dispatch_id 					= '".DBVarConv($this->id)."',
								para_dispatch_document_type_id 	= '".DBVarConv($this->para_dispatch_document_type_id)."',
								filename 						= '".DBVarConv($audit_collection_documents)."',
								filename_server 				= '".DBVarConv($NORMAL_IMAGE_NAME)."',
								created_by						= '".$this->created_by."',
								created_dt						= '".$this->now."',
								updated_by						= '".$this->updated_by."',
								updated_dt						= '".$this->now."'";
					$dbqry = new dbquery($query);
				}		
			}
		}
		/* FOR NORMAL IMAGE */
	}
	
	/**
    * Function Name UploadAuditAgreegatorCollectionDocuments
	* @param 
    * @return
	* @author Kalpak Prajapati
	*/
	function UploadAuditAgreegatorCollectionDocument()
	{
		if(!is_dir(PATH_ABSOLUTE_HTTP_IMAGES_AGREEGATORS_COLLECTION.$this->id)) {
			mkdir(PATH_ABSOLUTE_HTTP_IMAGES_AGREEGATORS_COLLECTION.$this->id);
		}
		/* FOR NORMAL IMAGE */
		$NORMAL_IMAGE_NAME = "";
		if (!empty($this->audit_collection_documents['name'])) 
		{
			if (isset($this->audit_collection_documents['error']) && $this->audit_collection_documents['error'] == 0)
			{
				$extension 			= substr(strrchr($this->audit_collection_documents['name'], "."), 1);
				$filename 			= getRandomNumber()."_audit_photos.".strtolower($extension);
				$SERVER_IMAGE_NAME	= PATH_ABSOLUTE_HTTP_IMAGES_AGREEGATORS_COLLECTION.$this->id."/".$filename;
				move_uploaded_file($this->audit_collection_documents['tmp_name'],$SERVER_IMAGE_NAME);
				$RISIZE				= PATH_ABSOLUTE_HTTP_IMAGES_AGREEGATORS_COLLECTION.$this->id."/resize_".basename($SERVER_IMAGE_NAME);
				system("convert ".$SERVER_IMAGE_NAME." -resize 116x116 -quality 100 ".$RISIZE);
				$NORMAL_IMAGE_NAME = basename($SERVER_IMAGE_NAME);

				$query 	= "	INSERT INTO ".$this->tablename_documents." SET
							dispatch_id 					= '".DBVarConv($this->id)."',
							para_dispatch_document_type_id 	= '".DBVarConv($this->para_dispatch_document_type_id)."',
							filename 						= '".DBVarConv($this->audit_collection_documents['name'])."',
							filename_server 				= '".DBVarConv($NORMAL_IMAGE_NAME)."',
							created_by						= '".$this->created_by."',
							created_dt						= '".$this->now."',
							updated_by						= '".$this->updated_by."',
							updated_dt						= '".$this->now."'";
				$dbqry = new dbquery($query);
				return $dbqry->insertid;
			}
		}
		return 0;
		/* FOR NORMAL IMAGE */
	}

	/**
	* Function Name : retrieveAgreegatorCollectionDocuments
	* @param $para_dispatch_document_type_id
	* @return
	* @author Kalpak Prajapati
	*/
	function retrieveAgreegatorCollectionDocuments($para_dispatch_document_type_id=0)
	{
		$id 		= $para_dispatch_document_type_id;
		$tablename 	= $this->tablename_documents;
		$pk 		= 'para_dispatch_document_type_id';
		if ($para_dispatch_document_type_id > 0) {
			$query 		= "SELECT * FROM $tablename WHERE $pk = '$id' AND dispatch_id = '".DBVarConv($this->id)."'";
		} else {
			$query 		= "SELECT * FROM $tablename WHERE dispatch_id = '".DBVarConv($this->id)."'";
		}
        $dbqry 		= new dbquery($query);
		if($dbqry->numrows() > 0) {
			while ($row = $dbqry->getrowarray(MYSQL_ASSOC)) {
				if (file_exists(PATH_ABSOLUTE_HTTP_IMAGES_AGREEGATORS_COLLECTION.$row['dispatch_id']."/".$row['filename_server'])) {
					$row['document_url'] = URL_ABSOLUTE_HTTP_IMAGES_AGREEGATORS_COLLECTION.$row['dispatch_id']."/".$row['filename_server'];
					if (file_exists(PATH_ABSOLUTE_HTTP_IMAGES_AGREEGATORS_COLLECTION.$row['dispatch_id']."/resize_".$row['filename_server'])) {
						$row['thumb_document_url'] 	= URL_ABSOLUTE_HTTP_IMAGES_AGREEGATORS_COLLECTION.$row['dispatch_id']."/resize_".$row['filename_server'];
					} else {
						$row['thumb_document_url'] 	= $row['document_url'];
					}
					switch ($row['para_dispatch_document_type_id']) {
						case $this->DOCUMENT_TYPE_BILL_T_STAMPPED:
							$this->arrBillTDocuments[] = $row;
							break;
						case $this->DOCUMENT_TYPE_UNLOAD_PHOTOS:
							$this->arrUnloadDocuments[] = $row;
							break;
						case $this->DOCUMENT_TYPE_EMPTY_VEHICLE:
							$this->arrEmptyVehicleDocuments[] = $row;
							break;
						case $this->DOCUMENT_TYPE_FILLED_VEHICLE:
							$this->arrFilledVehicleDocuments[] = $row;
							break;
						case $this->DOCUMENT_TYPE_PAYMENT_DOCS:
							$this->arrPaymentDocuments[] = $row;
							break;
						case $this->DOCUMENT_TYPE_AUDIT_COLLECTION_PHOTOS:
							$this->arrAuditDocuments[] = $row;
							break;
						case $this->DOCUMENT_TYPE_LICENCE_DOCS:
							$this->arrLicenceDocuments[] = $row;
							break;
						case $this->DOCUMENT_TYPE_VEHICLE_DOCS:
							$this->arrVehicleDocuments[] = $row;
							break;
						case $this->DOCUMENT_TYPE_VEHICLE_DRIVER_PHOTO:
							$this->arrDriverDocuments[] = $row;
							break;
						case $this->DOCUMENT_TYPE_VEHICLE_TRIP_START_DOCS:
							$this->arrTripStartDocuments[] = $row;
							break;
						case $this->DOCUMENT_TYPE_VEHICLE_TRIP_ENDS_DOCS:
							$this->arrTripEndDocuments[] = $row;
							break;
						default:
							$this->arrDocuments[] 	= $row;
							break;
					}
				}
			}
		}
	}

	/**
	* Function Name : generatechallan
	* @param $dispatch_id
	* @return
	* @author Kalpak Prajapati
	*/
	function generatechallan($dispatch_id=0)
	{
		$this->retrieveAgreegatorCollection($dispatch_id);
	}

	/**
	* Function Name : GenerateChallanNo
	* @param $date
	* @return
	* @author Kalpak Prajapati
	*/
	function GenerateChallanNo($date)
	{
		$response 	= array();
		$StartDate 	= date('Y-m-01', strtotime($date));
		$EndDate 	= date('Y-m-t', strtotime($date));
		$select 	= "SELECT MAX(challan_no) AS MAX_ID FROM $this->tablename WHERE created_dt BETWEEN '".$StartDate."' AND '".$EndDate."'";
		$result 	= new dbquery($select);
		$row		= $result->getrowarray(MYSQL_ASSOC);
		$challan_no = (!empty($row['MAX_ID']) ? $row['MAX_ID'] + 1 : 1);
		return $challan_no;
	}

	/**
	* Function Name : GenerateSalesNo
	* @param $date
	* @return
	* @author Kalpak Prajapati
	*/
	function GenerateSalesNo($date)
	{
		$response 	= array();
		$StartDate 	= date('Y-m-01', strtotime($date));
		$EndDate 	= date('Y-m-t', strtotime($date));
		$select 	= "SELECT MAX(sales_invoice_no) AS MAX_ID FROM $this->tablename WHERE invoice_date BETWEEN '".$StartDate."' AND '".$EndDate."'";
		$result 	= new dbquery($select);
		$row		= $result->getrowarray(MYSQL_ASSOC);
		$SalesNo 	= (!empty($row['MAX_ID']) ? $row['MAX_ID'] + 1 : 1);
		return $SalesNo;
	}

	/**
	* Function Name : GenerateCreditNoteNo
	* @param $date
	* @return
	* @author Kalpak Prajapati
	*/
	function GenerateCreditNoteNo($date)
	{
		$response 		= array();
		$StartDate 		= date('Y-m-01', strtotime($date));
		$EndDate 		= date('Y-m-t', strtotime($date));
		$select 		= "	SELECT MAX(credit_note_no) AS MAX_ID 
							FROM $this->tablename WHERE invoice_date BETWEEN '".$StartDate."' AND '".$EndDate."'";
		$result 		= new dbquery($select);
		$row			= $result->getrowarray(MYSQL_ASSOC);
		$CreditNoteNo 	= (!empty($row['MAX_ID']) ? $row['MAX_ID'] + 1 : 1);
		return $CreditNoteNo;
	}

	/**
	* Function Name : GetGenerateChallanNo
	* @param $challan_no
	* @param $date
	* @return
	* @author Kalpak Prajapati
	*/
	function GetChallanNo($challan_no=0,$date="")
	{
		$Month 		= date("m",strtotime($date));
		$Year 		= date("y",strtotime($date));
		$ChallanNo 	= "AG/PUR/";
		if (intval($Month) >= 1 && intval($Month) <= 3) {
			$ChallanNo 	.= ($Year-1)."-".date("y",strtotime($date))."/";
		} else {
			$ChallanNo 	.= $Year."-".(date("y",strtotime($date))+1)."/";
		}
		$ChallanNo .= str_pad($challan_no,4,"0",STR_PAD_LEFT);
		return $ChallanNo;
	}

	/**
	* Function Name : GetSalesInvoiceNo
	* @param $sales_invoice_no
	* @param $date
	* @return
	* @author Kalpak Prajapati
	*/
	function GetSalesInvoiceNo($sales_invoice_no=0,$date="")
	{
		$Month 			= date("m",strtotime($date));
		$Year 			= date("y",strtotime($date));
		$SalesInvoiceNo = "AG/Sale/";
		if (intval($Month) >= 1 && intval($Month) <= 3) {
			$SalesInvoiceNo 	.= ($Year-1)."-".date("y",strtotime($date))."/";
		} else {
			$SalesInvoiceNo 	.= $Year."-".(date("y",strtotime($date))+1)."/";
		}
		$SalesInvoiceNo .= str_pad($sales_invoice_no,4,"0",STR_PAD_LEFT);
		return $SalesInvoiceNo;
	}

	/**
	* Function Name : GetCreditNoteNo
	* @param $credit_note_no
	* @param $date
	* @return
	* @author Kalpak Prajapati
	*/
	function GetCreditNoteNo($credit_note_no=0,$date="")
	{
		$Month 			= date("m",strtotime($date));
		$Year 			= date("y",strtotime($date));
		$CreditNoteNo 	= "CRN/PUR/";
		if (intval($Month) >= 1 && intval($Month) <= 3) {
			$CreditNoteNo 	.= ($Year-1)."-".date("y",strtotime($date))."/";
		} else {
			$CreditNoteNo 	.= $Year."-".(date("y",strtotime($date))+1)."/";
		}
		$CreditNoteNo .= str_pad($credit_note_no,4,"0",STR_PAD_LEFT);
		return $CreditNoteNo;
	}

	/**
	* Function Name : GetProductDetailHtml
	* @param $ForInvoice
	* @return
	* @author Kalpak Prajapati
	*/
	function GetProductDetailHtml($ForInvoice=false,$ForCreditNote=false,$Unload=false,$Audit=false)
	{
		include_once(PATH_ABSOLUTE_CLASS."agreegators.cls.php");
		$clsagreegator 								= new clsagreegator();
		$clsagreegator->retrieveAgreegator($this->agreegator_id);
		$ProductDetailHtml['PRODUCT_DETAILS'] 		= "";
		$ProductDetailHtml['TOTAL_QTY'] 			= "";
		$ProductDetailHtml['TOTAL_AMOUNT'] 			= "";
		$ProductDetailHtml['TOTAL_AMOUNT_IN_WORDS'] = "";
		$ProductDetailHtml['TAX_PRODUCT_DETAILS'] 	= "";
		$ProductDetailHtml['TAX_AMOUNT_IN_WORDS'] 	= "";
		$PRODUCT_DETAILS 							= "";
		$TAX_PRODUCT_DETAILS 						= "";
		$TOTAL_QTY 									= 0;
		$TOTAL_AMOUNT 								= 0;
		$TOTAL_CGST 								= 0;
		$TOTAL_SGST 								= 0;
		$TOTAL_TAX_AMOUNT 							= 0;
		$BREAK_TABLE								= "";
		if (!empty($clsagreegator->gst_no) || $ForInvoice) {
			$TAX_PRODUCT_DETAILS .= '<tr>
										<td class="text-center" align="center">HSN/SAC</td>
										<td class="text-center" align="center" colspan="2">Taxable</td>
										<td class="text-center" align="center" colspan="2">Central Tax</td>
										<td class="text-center" align="center" colspan="2">State Tax</td>
										<td class="text-center" align="center">Total</td>
									</tr>
									<tr>
										<td class="text-center" align="center">&nbsp;</td>
										<td class="text-center" align="center" colspan="2">Value</td>
										<td class="text-center" align="center">Rate</td>
										<td class="text-center" align="center">Amount</td>
										<td class="text-center" align="center">Rate</td>
										<td class="text-center" align="center">Amount</td>
										<td class="text-center" align="center">Tax Amount</td>
									</tr>';
		}
		if (count($this->arrProducts) > 1) {
			//$BREAK_TABLE = '</table><div style="page-break-after: always;"></div><table align="center" cellpadding="5" cellspacing="0" width="90%" class="collapse">';
		}
		if (!empty($this->arrProducts)) 
		{
			$RowID 					= 1;
			foreach ($this->arrProducts as $arrProduct) 
			{
				if ($ForCreditNote && $arrProduct['inert'] <= 0) continue; //skip row for CREDIT NOTE

				$CGST 				= 0;
				$SGST 				= 0;
				$Quantity 			= ($ForInvoice || $Audit)?$arrProduct['audit_qty']:$arrProduct['qty'];
				$Quantity 			= ($ForCreditNote)?$arrProduct['inert']:$Quantity;
				$Product_Rate 		= ($ForInvoice)?$arrProduct['factory_rate']:$arrProduct['rate'];
				$Amount 			= $Quantity * $Product_Rate;

				$PRODUCT_DETAILS 	.= "<tr>
											<td>".$RowID."</td>
											<td colspan=\"2\">".$arrProduct['Product_Name']."</td>
											<td class=\"text-center\" align=\"center\">".$arrProduct['HSN']."</td>
											<td class=\"text-center\" align=\"center\">".$Quantity." ".$arrProduct['UOM']."</td>
											<td align=\"right\">"._FormatNumberV2($Product_Rate)."</td>
											<td class=\"text-center\" align=\"center\">".$arrProduct['UOM']."</td>
											<td align=\"right\">"._FormatNumberV2($Amount)."</td>
										</tr>";
				if (!empty($clsagreegator->gst_no) || $ForInvoice)
				{
					$CGST 				= ($Amount > 0)?(($Amount * $arrProduct['CGST'])/100):0;
					$SGST 				= ($Amount > 0)?(($Amount * $arrProduct['SGST'])/100):0;
					$TAX_PRODUCT_DETAILS.= "<tr>
												<td class=\"text-center\" align=\"center\">".$arrProduct['HSN']."</td>
												<td colspan=\"2\" align=\"right\">"._FormatNumberV2($Amount)."</td>
												<td align=\"right\">"._FormatNumberV2($arrProduct['CGST'])."%</td>
												<td align=\"right\">"._FormatNumberV2($CGST)."</td>
												<td align=\"right\">"._FormatNumberV2($arrProduct['SGST'])."%</td>
												<td align=\"right\">"._FormatNumberV2($SGST)."</td>
												<td align=\"right\">"._FormatNumberV2(($CGST + $SGST))."</td>
											</tr>";
				}
				$TOTAL_AMOUNT 		+= $Amount;
				$TOTAL_QTY 			+= $Quantity;
				$TOTAL_CGST 		+= $CGST;
				$TOTAL_SGST 		+= $SGST;
				$TOTAL_TAX_AMOUNT 	+= ($TOTAL_CGST + $TOTAL_SGST);
				$RowID++;
			}
			if (!empty($clsagreegator->gst_no) || $ForInvoice)
			{
				$TAX_PRODUCT_DETAILS .= "<tr>
											<td class=\"text-bold\" align=\"right\">Total</td>
											<td colspan=\"2\" class=\"text-bold\" align=\"right\">"._FormatNumberV2($TOTAL_AMOUNT)."</td>
											<td class=\"text-bold\" align=\"right\">&nbsp;</td>
											<td class=\"text-bold\" align=\"right\">"._FormatNumberV2($TOTAL_CGST)."</td>
											<td class=\"text-bold\" align=\"right\">&nbsp;</td>
											<td class=\"text-bold\" align=\"right\">"._FormatNumberV2($TOTAL_SGST)."</td>
											<td class=\"text-bold\" align=\"right\">"._FormatNumberV2(($TOTAL_TAX_AMOUNT))."</td>
										</tr>
										<tr>
											<td colspan=\"8\">Tax Amount (in words): <span class=\"text-bold\">".(($TOTAL_TAX_AMOUNT > 0)?ucwords(getIndianCurrency(floatval($TOTAL_TAX_AMOUNT))):"-")."</span></td>
										</tr>";
			}
		} else {
			$PRODUCT_DETAILS 		= "<tr><td class=\"text-bold\" colspan=\"8\">NO PRODUCTS SELECTED</td></tr>";
			if (!empty($clsagreegator->gst_no) || $ForInvoice) {
				$TAX_PRODUCT_DETAILS 	= "<tr><td class=\"text-bold\" colspan=\"8\">NO PRODUCTS SELECTED</td></tr>";
			}
		}

		$ProductDetailHtml['BREAK_TABLE'] 			= $BREAK_TABLE;
		$ProductDetailHtml['PRODUCT_DETAILS'] 		= $PRODUCT_DETAILS;
		$ProductDetailHtml['TOTAL_QTY'] 			= _FormatNumberV2($TOTAL_QTY)." KGS";
		$ProductDetailHtml['TOTAL_AMOUNT'] 			= "RS "._FormatNumberV2($TOTAL_AMOUNT);
		$ProductDetailHtml['TOTAL_AMOUNT_IN_WORDS'] = ($TOTAL_AMOUNT > 0)?ucwords(getIndianCurrency(floatval($TOTAL_AMOUNT))):"-";
		$ProductDetailHtml['TAX_PRODUCT_DETAILS'] 	= $TAX_PRODUCT_DETAILS;
		$ProductDetailHtml['TAX_AMOUNT_IN_WORDS'] 	= ($TOTAL_TAX_AMOUNT > 0)?ucwords(getIndianCurrency(floatval($TOTAL_TAX_AMOUNT))):"-";
		return $ProductDetailHtml;
	}

	/**
	* Function Name : GetNotificationEmailHtml
	* @param $dispatch_id
	* @return $HTML_CONTENT
	* @author Kalpak Prajapati
	*/
	public function GetNotificationEmailHtml($dispatch_id=0,$send_sms=true,$unload=false,$audit=false)
	{
		$HTML_CONTENT = "";
		include_once(PATH_ABSOLUTE_CLASS."agreegators.cls.php");
		include_once(PATH_ABSOLUTE_CLASS."parameter.cls.php");
		$PARAMETER 		= new clsparameter();
		$clsagreegator 	= new clsagreegator();
		$this->retrieveAgreegatorCollection($dispatch_id);
		$clsagreegator->retrieveAgreegator($this->agreegator_id);
		$CLIENT_MASTER 			= $clsagreegator->getAssignedCustomers($this->agreegator_id,true,true,$this->wm_client_master_id);
		$arrPaymentCycle		= $PARAMETER->GetSupplierPaymentCycle();
		if (count($this->arrProducts) > 0 && $clsagreegator->id > 0)
		{
			$PRODUCT_DETAILS_HTML 	= $this->GetProductDetailHtml(false,false,$unload,$audit);
			$AGREEGATOR_NAME 		= $clsagreegator->name;
			$INVOICE_NO				= $this->GetChallanNo($this->challan_no,$this->created_dt);
			$EWAYBILL_NO 			= (!empty($this->eway_bill_no)?$this->eway_bill_no:"-");
			$INVOICE_DATE 			= date("d/M/Y",strtotime($this->created_dt));
			$AGREEGATOR_GST_NO 		= !empty($clsagreegator->gst_no)?$clsagreegator->gst_no:"-";
			$AGREEGATOR_STATE_NAME	= !empty($clsagreegator->statename)?$clsagreegator->statename:"-";
			$AGREEGATOR_STATE_CODE 	= !empty($clsagreegator->state_code)?$clsagreegator->state_code:"-";
			$AGREEGATOR_ID 			= str_pad($clsagreegator->id,5,"0",STR_PAD_LEFT);
			$OTHER_REF				= "-";
			$WEIGH_SLIP_NO			= !empty($this->weigh_slip_no)?$this->weigh_slip_no:"-";
			$PAYMENT_TERMS			= isset($arrPaymentCycle[$clsagreegator->para_payment_cycle_type_id])?$arrPaymentCycle[$clsagreegator->para_payment_cycle_type_id]['para_value']:"-";
			$BILL_T_NO				= !empty($this->bill_t_no)?$this->bill_t_no:"-";
			$VEHICLE_NO				= !empty($this->vehicle_no)?$this->vehicle_no:"-";
			$DRIVER_NAME 			= !empty($this->driver_name)?$this->driver_name:"-";
			$DRIVER_CONTACT 		= !empty($this->driver_contact_no)?$this->driver_contact_no:"-";
			$BILL_TO_NAME			= LR_COMPANY_NAME;
			$BILL_TO_ADDRESS		= LR_COMPANY_ADDRESS;
			$BILL_TO_GSTNO			= LR_COMPANY_GSTNO;
			$BILL_TO_STATE			= LR_COMPANY_STATENAME;
			$BILL_TO_CODE			= LR_COMPANY_STATECODE;
			$SHIP_TO_NAME			= $CLIENT_MASTER[$this->wm_client_master_id]['client_name'];
			$SHIP_TO_ADDRESS		= "";
			if (!empty($CLIENT_MASTER[$this->wm_client_master_id]['address'])) {
				$ADDRESS = explode(",",$CLIENT_MASTER[$this->wm_client_master_id]['address']);
				foreach ($ADDRESS as $key => $ADDRESS_ROW) {
					if ($key > 1) {
						$SHIP_TO_ADDRESS .= "<br />";
					}
					$SHIP_TO_ADDRESS .= $ADDRESS_ROW;
				}
				$SHIP_TO_ADDRESS = trim($SHIP_TO_ADDRESS,"<br />");
			}
			$SHIP_TO_GSTNO			= $CLIENT_MASTER[$this->wm_client_master_id]['gstin_no'];;
			$SHIP_TO_STATE			= $CLIENT_MASTER[$this->wm_client_master_id]['statename'];;
			$SHIP_TO_CODE			= $CLIENT_MASTER[$this->wm_client_master_id]['state_code'];;
			$PRODUCT_DETAILS 		= $PRODUCT_DETAILS_HTML['PRODUCT_DETAILS'];
			$LESS_AMOUNT			= "0.00";
			$TOTAL_QTY				= ($PRODUCT_DETAILS_HTML['TOTAL_QTY']);
			$TOTAL_AMOUNT 			= ($PRODUCT_DETAILS_HTML['TOTAL_AMOUNT']);
			$TOTAL_AMOUNT_IN_WORDS	= $PRODUCT_DETAILS_HTML['TOTAL_AMOUNT_IN_WORDS'];
			$TAX_PRODUCT_DETAILS	= $PRODUCT_DETAILS_HTML['TAX_PRODUCT_DETAILS'];
			$TAX_AMOUNT_IN_WORDS	= $PRODUCT_DETAILS_HTML['TAX_AMOUNT_IN_WORDS'];
			$COMPANY_PAN_NO			= $clsagreegator->pan_no; //LR_COMPANY_PAN_NO;
			$BREAK_TABLE 			= $PRODUCT_DETAILS_HTML['BREAK_TABLE'];

			$HTML_CONTENT			= file_get_contents(PATH_ABSOLUTE_HTTP."images/email-template/agreegator_notification_email_template.html");

			$SEARCH_ARRAY	= array("[AGREEGATOR_NAME]","[INVOICE_NO]","[INVOICE_DATE]","[AGREEGATOR_GST_NO]","[AGREEGATOR_STATE_NAME]",
									"[AGREEGATOR_STATE_CODE]","[DRIVER_NAME]","[DRIVER_CONTACT]","[AGREEGATOR_ID]","[OTHER_REF]",
									"[WEIGH_SLIP_NO]","[PAYMENT_TERMS]","[BILL_T_NO]","[VEHICLE_NO]",
									"[BILL_TO_NAME]","[BILL_TO_ADDRESS]","[BILL_TO_GSTNO]","[BILL_TO_STATE]","[BILL_TO_STATE_CODE]",
									"[SHIP_TO_NAME]","[SHIP_TO_ADDRESS]","[SHIP_TO_GSTNO]","[SHIP_TO_STATE]","[SHIP_TO_STATE_CODE]",
									"[PRODUCT_DETAILS]","[LESS_AMOUNT]","[TOTAL_QTY]","[TOTAL_AMOUNT]",
									"[TOTAL_AMOUNT_IN_WORDS]","[TAX_PRODUCT_DETAILS]","[TAX_AMOUNT_IN_WORDS]","[COMPANY_PAN_NO]","[BREAK_TABLE]","[EWAYBILL_NO]");

			$REPLACE_ARRAY	= array($AGREEGATOR_NAME,$INVOICE_NO,$INVOICE_DATE,$AGREEGATOR_GST_NO,$AGREEGATOR_STATE_NAME,
									$AGREEGATOR_STATE_CODE,$DRIVER_NAME,$DRIVER_CONTACT,$AGREEGATOR_ID,$OTHER_REF,
									$WEIGH_SLIP_NO,$PAYMENT_TERMS,$BILL_T_NO,$VEHICLE_NO,
									$BILL_TO_NAME,$BILL_TO_ADDRESS,$BILL_TO_GSTNO,$BILL_TO_STATE,$BILL_TO_CODE,
									$SHIP_TO_NAME,$SHIP_TO_ADDRESS,$SHIP_TO_GSTNO,$SHIP_TO_STATE,$SHIP_TO_CODE,
									$PRODUCT_DETAILS,$LESS_AMOUNT,$TOTAL_QTY,$TOTAL_AMOUNT,
									$TOTAL_AMOUNT_IN_WORDS,$TAX_PRODUCT_DETAILS,$TAX_AMOUNT_IN_WORDS,$COMPANY_PAN_NO,$BREAK_TABLE,$EWAYBILL_NO);
			$HTML_CONTENT 	= str_replace($SEARCH_ARRAY,$REPLACE_ARRAY,$HTML_CONTENT);
			$send_sms 		= false;
			if ($send_sms)
			{
				foreach($this->arrNotificationMobiles as $MOBILE) 
				{
					if ($unload) {
						$SMS_TEXT = "Dispatch# $dispatch_id Unloaded at Factory For - ".$AGREEGATOR_NAME;
					} else if ($audit) {
						$SMS_TEXT = "Dispatch# $dispatch_id Audited at Factory For - ".$AGREEGATOR_NAME;
					} else {
						$SMS_TEXT = "New Dispatch Added By Agreegator - ".$AGREEGATOR_NAME.", Dispatch# is ".$dispatch_id;
					}
					$MESSAGE			= urlencode($SMS_TEXT);
					$FIND_ARRAY			= array("[SMS_USER]","[SMS_PASS]","[MESSAGE]","[MOBILE]");
					$REPL_ARRAY			= array(SMS_USER,SMS_PASS,$MESSAGE,$MOBILE);
					$SMS_GATEWAY_URL 	= str_replace($FIND_ARRAY,$REPL_ARRAY,SMS_GATWAY_URL);
					$ch 				= curl_init($SMS_GATEWAY_URL);
					curl_setopt($ch, CURLOPT_HEADER,0);  			// DO NOT RETURN HTTP HEADERS
					curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);  	// RETURN THE CONTENTS
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT  ,0);
					$SMS_CONTENT 		= curl_exec($ch);
					$this->SaveSMSResponse($dispatch_id,"Dispatch ID ".$dispatch_id." SMS response :: ".$SMS_CONTENT,$SMS_GATEWAY_URL);
				}
			}
		}
		return $HTML_CONTENT;
	}

	/**
	* Function Name : SaveSMSResponse
	* @param integer $dispatch_id
	* @param string $remark
	* @param string $content
	* @return
	* @author Kalpak Prajapati
	*/
	function SaveSMSResponse($dispatch_id,$remark,$content)
	{
		$query 	= "	INSERT INTO appointment_sms_response SET
					appointment_id	= '".DBVarConv($dispatch_id)."',
					remark			= '".DBVarConv($remark)."',
					content			= '".DBVarConv($content)."',
					created_dt		= '".$this->now."'";
		$dbqry = new dbquery($query);
	}

	/**
	* Function Name : SaveGPSLocation
	* @param
	* @return
	* @author Kalpak Prajapati
	*/
	function SaveGPSLocation()
	{
		$ip_address = getipaddress();
		$query 	= "	INSERT INTO macro_agreegators_dispatch_tracking SET
					vehicle_no	= '".DBVarConv($this->vehicle_no)."',
					lattitude	= '".DBVarConv($this->lattitude)."',
					longitude	= '".DBVarConv($this->longitude)."',
					version		= '".DBVarConv($this->version)."',
					ip_address	= '".DBVarConv($ip_address)."',
					created		= '".$this->now."'";
		$dbqry = new dbquery($query);
	}

	/**
	* Function Name : SendNotificationToSalesTeam
	* @param $dispatch_id
	* @return
	* @author Kalpak Prajapati
	*/
	public function SendNotificationToSalesTeam($dispatch_id=0)
	{
		$HTML_CONTENT = $this->GetNotificationEmailHtml($dispatch_id,false);
		$dompdf = new DOMPDF();
		$dompdf->load_html($HTML_CONTENT);
		$dompdf->set_paper("letter","portrait");
		$dompdf->render();
		$pdfdata 	= $dompdf->output();
		unset($dompdf);
		$PDF_PATH 	= PATH_ABSOLUTE_TEMP."MA_D_".$dispatch_id.".pdf";
		if (file_exists($PDF_PATH)) @unlink($PDF_PATH);
		$fp 		= fopen($PDF_PATH,"w+");
		fwrite($fp,$pdfdata);
		fclose($fp);
		$ATTACHMENT[] = array("filedata"=>file_get_contents($PDF_PATH),"filename"=>basename($PDF_PATH),"mimetype"=>"application/pdf");
		$FROM 		= TITLE."<info@letsrecycle.in>";
		$TO 		= "kalpak@yugtia.com";
		$SUBJECT	= TITLE." - New Dispatch Added By Agreegator";
		send_emails($FROM,$TO,$SUBJECT,$FROM,$HTML_CONTENT,true,$ATTACHMENT);
		if (file_exists($PDF_PATH)) @unlink($PDF_PATH);
	}

	/**
	* Function Name : SendUnloadNotificationToSalesTeam
	* @param $dispatch_id
	* @return
	* @author Kalpak Prajapati
	*/
	public function SendUnloadNotificationToSalesTeam($dispatch_id=0,$send_sms=0)
	{
		include_once(PATH_ABSOLUTE_CLASS."agreegators.cls.php");
		include_once(PATH_ABSOLUTE_CLASS."parameter.cls.php");
		$HTML_CONTENT 	= $this->GetNotificationEmailHtml($dispatch_id,false,true,false);
		$dompdf 		= new DOMPDF();
		$dompdf->load_html($HTML_CONTENT);
		$dompdf->set_paper("letter","portrait");
		$dompdf->render();
		$pdfdata 	= $dompdf->output();
		unset($dompdf);
		$PDF_PATH 	= PATH_ABSOLUTE_TEMP."MA_D_".$dispatch_id.".pdf";
		if (file_exists($PDF_PATH)) @unlink($PDF_PATH);
		$fp = fopen($PDF_PATH,"w+");
		fwrite($fp,$pdfdata);
		fclose($fp);

		$EMAIL_HTML_CONTENT = "";
		$PARAMETER 			= new clsparameter();
		$clsagreegator 		= new clsagreegator();
		$this->retrieveAgreegatorCollection($dispatch_id);
		$clsagreegator->retrieveAgreegator($this->agreegator_id);
		if (count($this->arrProducts) > 0 && $clsagreegator->id > 0)
		{
			$AGREEGATOR_NAME 	= $clsagreegator->name;
			$INVOICE_NO			= $this->challan_no;
			$INVOICE_DATE 		= date("d/M/Y",strtotime($this->unload_date));
			$EMAIL_HTML_CONTENT	= file_get_contents(PATH_ABSOLUTE_HTTP."images/email-template/agreegator_unload_notification_email_template.html");
			$SEARCH_ARRAY		= array("[DISPATCH_ID]","[AGREEGATOR_NAME]","[INVOICE_NO]","[INVOICE_DATE]");
			$REPLACE_ARRAY		= array($dispatch_id,$AGREEGATOR_NAME,$INVOICE_NO,$INVOICE_DATE);
			$EMAIL_HTML_CONTENT = str_replace($SEARCH_ARRAY,$REPLACE_ARRAY,$HTML_CONTENT);
			$send_sms			= false;
			if ($send_sms)
			{
				foreach($this->arrNotificationMobiles as $MOBILE) 
				{
					$SMS_TEXT			= "New Dispatch Added By Agreegator - ".$AGREEGATOR_NAME.", Dispatch# is ".$dispatch_id;
					$MESSAGE			= urlencode($SMS_TEXT);
					$FIND_ARRAY			= array("[SMS_USER]","[SMS_PASS]","[MESSAGE]","[MOBILE]");
					$REPL_ARRAY			= array(SMS_USER,SMS_PASS,$MESSAGE,$MOBILE);
					$SMS_GATEWAY_URL 	= str_replace($FIND_ARRAY,$REPL_ARRAY,SMS_GATWAY_URL);
					$ch 				= curl_init($SMS_GATEWAY_URL);
					curl_setopt($ch, CURLOPT_HEADER,0);  			// DO NOT RETURN HTTP HEADERS
					curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);  	// RETURN THE CONTENTS
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT  ,0);
					$SMS_CONTENT 		= curl_exec($ch);
					$this->SaveSMSResponse($dispatch_id,"Dispatch ID ".$dispatch_id." SMS response :: ".$SMS_CONTENT,$SMS_GATEWAY_URL);
				}
			}
			$ATTACHMENT[] = array("filedata"=>file_get_contents($PDF_PATH),"filename"=>basename($PDF_PATH),"mimetype"=>"application/pdf");
			$FROM 		= TITLE."<info@letsrecycle.in>";
			$TO 		= "kalpak@yugtia.com";
			$SUBJECT	= TITLE." - Unload Dispatch Notification Dispatch# ".$dispatch_id;
			send_emails($FROM,$TO,$SUBJECT,$FROM,$EMAIL_HTML_CONTENT,true,$ATTACHMENT);
		}
		if (file_exists($PDF_PATH)) @unlink($PDF_PATH);
	}



	/**
	* Function Name : SendAuditNotificationToSalesTeam
	* @param $dispatch_id
	* @return
	* @author Kalpak Prajapati
	*/
	public function SendAuditNotificationToSalesTeam($dispatch_id=0)
	{
		include_once(PATH_ABSOLUTE_CLASS."agreegators.cls.php");
		include_once(PATH_ABSOLUTE_CLASS."parameter.cls.php");

		$HTML_CONTENT 	= $this->GetNotificationEmailHtml($dispatch_id,false,false,true);
		$dompdf 		= new DOMPDF();
		$dompdf->load_html($HTML_CONTENT);
		$dompdf->set_paper("letter","portrait");
		$dompdf->render();
		$pdfdata 	= $dompdf->output();
		unset($dompdf);
		$PDF_PATH 	= PATH_ABSOLUTE_TEMP."MA_D_".$dispatch_id.".pdf";
		if (file_exists($PDF_PATH)) @unlink($PDF_PATH);
		$fp = fopen($PDF_PATH,"w+");
		fwrite($fp,$pdfdata);
		fclose($fp);

		$EMAIL_HTML_CONTENT = "";
		$PARAMETER 			= new clsparameter();
		$clsagreegator 		= new clsagreegator();
		$this->retrieveAgreegatorCollection($dispatch_id);
		$clsagreegator->retrieveAgreegator($this->agreegator_id);
		if (count($this->arrProducts) > 0 && $clsagreegator->id > 0)
		{
			$AGREEGATOR_NAME 	= $clsagreegator->name;
			$INVOICE_NO			= $this->challan_no;
			$INVOICE_DATE 		= date("d/M/Y",strtotime($this->unload_date));
			$EMAIL_HTML_CONTENT	= file_get_contents(PATH_ABSOLUTE_HTTP."images/email-template/agreegator_unload_notification_email_template.html");
			$SEARCH_ARRAY		= array("[DISPATCH_ID]","[AGREEGATOR_NAME]","[INVOICE_NO]","[INVOICE_DATE]");
			$REPLACE_ARRAY		= array($dispatch_id,$AGREEGATOR_NAME,$INVOICE_NO,$INVOICE_DATE);
			$EMAIL_HTML_CONTENT = str_replace($SEARCH_ARRAY,$REPLACE_ARRAY,$HTML_CONTENT);
			$send_sms			= false;
			if ($send_sms)
			{
				$CLIENT_MASTER 	= $clsagreegator->getAssignedCustomers($this->agreegator_id,true,true,$this->wm_client_master_id);
				$FACTORY_NAME	= $CLIENT_MASTER[$this->wm_client_master_id]['client_name'];
				foreach($this->arrNotificationMobiles as $MOBILE) 
				{
					$SMS_TEXT			= "Dispatch#  ".$dispatch_id." audited by Factory ".$FACTORY_NAME." supplied by ".$AGREEGATOR_NAME.".";
					$MESSAGE			= urlencode($SMS_TEXT);
					$FIND_ARRAY			= array("[SMS_USER]","[SMS_PASS]","[MESSAGE]","[MOBILE]");
					$REPL_ARRAY			= array(SMS_USER,SMS_PASS,$MESSAGE,$MOBILE);
					$SMS_GATEWAY_URL 	= str_replace($FIND_ARRAY,$REPL_ARRAY,SMS_GATWAY_URL);
					$ch 				= curl_init($SMS_GATEWAY_URL);
					curl_setopt($ch, CURLOPT_HEADER,0);  			// DO NOT RETURN HTTP HEADERS
					curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);  	// RETURN THE CONTENTS
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT  ,0);
					$SMS_CONTENT 		= curl_exec($ch);
					$this->SaveSMSResponse($dispatch_id,"Audit Dispatch ID ".$dispatch_id." SMS response :: ".$SMS_CONTENT,$SMS_GATEWAY_URL);
				}
			}

			$ATTACHMENT[] = array("filedata"=>file_get_contents($PDF_PATH),"filename"=>basename($PDF_PATH),"mimetype"=>"application/pdf");
			$FROM 		= TITLE."<info@letsrecycle.in>";
			$TO 		= "kalpak@yugtia.com";
			$SUBJECT	= TITLE." - Audit Dispatch Notification Dispatch# ".$dispatch_id;
			send_emails($FROM,$TO,$SUBJECT,$FROM,$EMAIL_HTML_CONTENT,true,$ATTACHMENT);
		}
		if (file_exists($PDF_PATH)) @unlink($PDF_PATH);
	}

	/**
	* Function Name : pageadminEditAgreegatorCollectionFromMobile
	* @param
	* @return
	* @author Kalpak Prajapati
	*/
	function pageadminEditAgreegatorCollectionFromMobile()
	{
		$this->setgetvars();
		$this->setpostvars();
		$this->created_by = $this->agreegator_id;
		$this->updated_by = $this->agreegator_id;
		if ($this->submited)
		{
			if ($this->action == 'UPDATE_Agreegator_Collection')
			{
				if($this->validateEditAgreegatorCollection())
				{
					$this->saveAgreegatorCollection($this->id);
				}
			}
			if ($this->action == 'EDIT_Agreegator_Collection')
			{
				$this->retrieveAgreegatorCollection($this->id);
			}
		}
		else
		{
			if ($this->id != '')
			{
				//TODO: Set other properties here
				$this->retrieveAgreegatorCollection($this->id);
			}
		}
	}

	/**
	* Function Name : pageadminListAgreegatorCollectionFromMobile
	* @param
	* @return
	* @author Kalpak Prajapati
	*/
	function pageadminListAgreegatorCollectionFromMobile()
	{
		$this->setgetvars();
		$this->setpostvars();
		$this->clspaging->setgetvars();
		$this->clspaging->setpostvars();
		$this->created_by = $this->agreegator_id;
		$this->updated_by = $this->agreegator_id;
		return $this->searchAgreegatorCollection(true);
	}

	/**
	* Function Name : pageadminListAuditAgreegatorCollectionFromMobile
	* @param
	* @return
	* @author Kalpak Prajapati
	*/
	function pageadminListAuditAgreegatorCollectionFromMobile()
	{
		$this->setgetvars();
		$this->setpostvars();
		$this->created_by = $this->agreegator_id;
		$this->updated_by = $this->agreegator_id;
		if ($this->submited)
		{
			if ($this->action == 'Unload_Agreegator_Collection')
			{
				if ($this->validateUnloadCollection()) {
					$this->unload_by = $this->created_by;
					$this->UnloadAgreegatorCollection();
					$this->SendUnloadNotificationToSalesTeam($this->id);
					return true;
				}
			}
		
			if ($this->action == 'Audit_Agreegator_Collection')
			{
				if ($this->validateAuditCollection()) {
					$this->audit_by = $this->created_by;
					$this->AuditAgreegatorCollection();
					$this->SendAuditNotificationToSalesTeam($this->id);
					return true;
				}
			}

			if ($this->action == 'Approve_Agreegator_Collection')
			{
				if ($this->validateGenerateInvoiceCollection()) {
					$this->approved_by 	= $this->created_by;
					$this->invoice_date = $this->now;
					$this->GenerateAgreegatorCollectionInvoice();
					return true;
				}
			}

			if ($this->action == 'Reach_Agreegator_Collection')
			{
				if ($this->validateGenerateQueueCollection()) {
					$this->GenerateAgreegatorCollectionQueue();
					return true;
				}
			}

			switch ($this->action) {
				case 'Upload_Agreegator_Collection_Document':
					$this->para_dispatch_document_type_id = $this->DOCUMENT_TYPE_WAYBRIDGE;
					break;
				case 'Upload_Vehicle_Driver_Photo':
					$this->para_dispatch_document_type_id = $this->DOCUMENT_TYPE_VEHICLE_DRIVER_PHOTO;
					break;
				case 'Upload_Vehicle_Licence_Copy':
					$this->para_dispatch_document_type_id = $this->DOCUMENT_TYPE_LICENCE_DOCS;
					break;
				case 'Upload_Vehicle_Documents':
					$this->para_dispatch_document_type_id = $this->DOCUMENT_TYPE_VEHICLE_DOCS;
					break;
				case 'Upload_Empty_Vehicle_Document':
					$this->para_dispatch_document_type_id = $this->DOCUMENT_TYPE_EMPTY_VEHICLE;
					break;
				case 'Upload_Filled_Vehicle_Document':
					$this->para_dispatch_document_type_id = $this->DOCUMENT_TYPE_FILLED_VEHICLE;
					break;
				case 'Upload_Vehicle_Trip_Start_Documents':
					$this->para_dispatch_document_type_id = $this->DOCUMENT_TYPE_VEHICLE_TRIP_START_DOCS;
					break;
				case 'Upload_Vehicle_Trip_End_Documents':
					$this->para_dispatch_document_type_id = $this->DOCUMENT_TYPE_VEHICLE_TRIP_ENDS_DOCS;
					break;
				case 'Upload_Bill_T_Copy':
					$this->para_dispatch_document_type_id = $this->DOCUMENT_TYPE_BILL_T_STAMPPED;
					break;
				case 'Unload_Agreegator_Collection_Document':
					$this->para_dispatch_document_type_id = $this->DOCUMENT_TYPE_UNLOAD_PHOTOS;
					break;
				case 'Audit_Agreegator_Collection_Document':
					$this->para_dispatch_document_type_id = $this->DOCUMENT_TYPE_AUDIT_COLLECTION_PHOTOS;
					break;
				case 'Approve_Agreegator_Collection_Document':
					$this->para_dispatch_document_type_id = $this->DOCUMENT_TYPE_PAYMENT_DOCS;
					break;
				case 'Upload_Product_Photo':
					$this->para_dispatch_document_type_id = $this->DOCUMENT_TYPE_FILLED_VEHICLE;
					break;
				default:
					# code...
					break;
			}
			if (!empty($this->para_dispatch_document_type_id))
			{
				if ($this->validateCollectionDocument()) {
					$LastInsertID = $this->UploadAuditAgreegatorCollectionDocument();
					if ($this->action == "Upload_Product_Photo") {
						$this->UpdateProductDispatchPhoto($LastInsertID,$this->product_id,$this->id);
					}
					return true;
				}
			}
		}
	}

	/**
	* Function Name : validateEditAgreegatorCollectionPaymentPlan
	* @param
	* @return
	* @author Kalpak Prajapati
	*/
	function validateEditAgreegatorCollectionPaymentPlan()
	{
		//TODO: ADD VALIDATIONS REQUIRED FOR INSERT/UPDATE
		if (empty($this->id) || !ctype_digit($this->id)) $this->error[] = "Audit Collection ID is required field.";
		$arrFilter['id'] 	= intval($this->id);
		$arrCollections 	= $this->retrieveAgreegatorCollectionListByFilter($arrFilter);
		if (empty($arrCollections) || !isset($arrCollections[$this->id])) $this->error[] = "Invalid Collection details.";
		if (!empty($arrCollections) && isset($arrCollections[$this->id])) {
			if ($arrCollections[$this->id]['unload'] == 0) {
				$this->error[] = "Material is yet to unload at factory site.";
			}
			if ($arrCollections[$this->id]['is_audited'] == 0) {
				$this->error[] = "Material is yet to audited at factory site.";
			}
			if ($arrCollections[$this->id]['invoice_generated'] == 0) {
				$this->error[] = "Material is yet to approved at factory site.";
			}
			$this->retrieveAgreegatorCollectionDocuments($this->DOCUMENT_TYPE_BILL_T_STAMPPED);
			if (empty($this->arrBillTDocuments)) {
				$this->error[] = "Please verify Agreegator yet to submit the stampped copy of Bill.";
			}
		}
		if (empty($this->checkpoints)) $this->error[] = "Before submitting please review the check points.";
		if (empty($this->arrPaymentAccounts)) $this->error[] = "At least one account is required to payment plan.";
		$ValidPaymentPlan 	= $this->validatePaymentPlan($this->id);
		if (!$ValidPaymentPlan) $this->error[] = "Please verify amount for payment to be released against dispatch.";
		return (count($this->error)>0)?false:true;
	}

	/**
	* Function Name : validateScheduleAgreegatorCollectionPaymentPlan
	* @param
	* @return
	* @author Kalpak Prajapati
	*/
	function validateScheduleAgreegatorCollectionPaymentPlan()
	{
		//TODO: ADD VALIDATIONS REQUIRED FOR INSERT/UPDATE
		if (empty($this->id) || !ctype_digit($this->id)) $this->error[] = "Audit Collection ID is required field.";
		$arrFilter['id'] 	= intval($this->id);
		$arrCollections 	= $this->retrieveAgreegatorCollectionListByFilter($arrFilter);
		if (empty($arrCollections) || !isset($arrCollections[$this->id])) $this->error[] = "Invalid Collection details.";
		if (!empty($arrCollections) && isset($arrCollections[$this->id])) {
			if ($arrCollections[$this->id]['unload'] == 0) {
				$this->error[] = "Material is yet to unload at factory site.";
			}
			if ($arrCollections[$this->id]['is_audited'] == 0) {
				$this->error[] = "Material is yet to audited at factory site.";
			}
			if ($arrCollections[$this->id]['invoice_generated'] == 0) {
				$this->error[] = "Material is yet to approved at factory site.";
			}
			$this->retrieveAgreegatorCollectionDocuments($this->DOCUMENT_TYPE_BILL_T_STAMPPED);
			if (empty($this->arrBillTDocuments)) {
				$this->error[] = "Please verify Agreegator yet to submit the stampped copy of Bill.";
			}
		}
		if (empty($this->arrPaymentAccounts)) $this->error[] = "At least one account is required to payment plan.";
		$ValidPaymentPlan 	= $this->validatePaymentPlan($this->id);
		if (!$ValidPaymentPlan) $this->error[] = "Please verify amount for payment to be released against dispatch.";
		return (count($this->error)>0)?false:true;
	}

	/**
	* Function Name : validatePaymentPlan
	* @param $dispatch_id
	* @return boolean
	* @author Kalpak Prajapati
	*/
	private function validatePaymentPlan($dispatch_id=0)
	{
		if (empty($dispatch_id)) return false;
		$PaymentPlan 			= "	SELECT sum(amount) as Total_Paid FROM ".$this->tablename_payment_plan." 
								WHERE dispatch_id = '".DBVarConv($dispatch_id)."' AND approved = 1";
		$PaymentPlanRes 		= new dbquery($PaymentPlan);
		$Total_Paid_Amount		= 0;
		if ($PaymentPlanRes->numrows() > 0) {
			$PaymentPlanRow 	= $PaymentPlanRes->getrowarray(MYSQL_ASSOC);
			$Total_Paid_Amount 	= !empty($PaymentPlanRow['Total_Paid'])?$PaymentPlanRow['Total_Paid']:0;
		}
		$Total_To_Paid 			= $this->GetTotalAmountToBePaid($dispatch_id);
		$Total_Schedule_Amount 	= 0;
		if(!empty($this->arrPaymentAccounts)) {
			foreach ($this->arrPaymentAccounts as $PaymentRequestRow) {
				$Total_Schedule_Amount += $PaymentRequestRow['amount'];
			}
		}
		if (floatval($Total_To_Paid) < (floatval($Total_Paid_Amount) + floatval($Total_Schedule_Amount))) {
			return false;
		}
		return true;
	}

	/**
	* Function Name : GetTotalAmountToBePaid
	* @param $dispatch_id
	* @return float $Total_To_Paid
	* @author Kalpak Prajapati
	*/
	private function GetTotalAmountToBePaid($dispatch_id)
	{
		include_once(PATH_ABSOLUTE_CLASS."agreegators.cls.php");
		$clsagreegator 		= new clsagreegator();
		$GrandTotal 		= 0;
		$arrFilter['id'] 	= intval($dispatch_id);
		$arrCollections 	= $this->retrieveAgreegatorCollectionListByFilter($arrFilter);
		$this->retrieveCollectionProducts($dispatch_id);
		$this->PaymentDetails = $this->retrievePaymentAmount($arrCollections[$dispatch_id]['agreegator_id']);
		$GrandTotal = $this->payment_amount;
		return $GrandTotal;
	}

	/**
	* Function Name : saveAgreegatorCheckPoints
	* @param $dispatch_id
	* @return
	* @author Kalpak Prajapati
	*/
	function saveAgreegatorCheckPoints($dispatch_id=0)
	{
		if(!empty($dispatch_id) && !empty($this->checkpoints))
		{
			$DeleteSQL 	= "DELETE FROM ".$this->tablename_checklist." WHERE dispatch_id = '".DBVarConv($dispatch_id)."'";
			$dbqry 		= new dbquery($DeleteSQL);
			foreach ($this->checkpoints as $checkpoint) {
				if (isset($checkpoint['id'])) 
				{
					$CheckList 		= "	SELECT * FROM ".$this->tablename_checklist."
										WHERE para_check_list_id 	= '".DBVarConv($checkpoint['id'])."'
										AND dispatch_id 			= '".DBVarConv($dispatch_id)."'";
					$CheckListRes = new dbquery($CheckList);
					if ($CheckListRes->numrows() > 0) 
					{
						$CheckListRow = $CheckListRes->getrowarray(MYSQL_ASSOC);
						$query 				= "	UPDATE ".$this->tablename_checklist." SET
												remarks 			= '".DBVarConv($checkpoint['remarks'])."',
												updated_by 			= '".DBVarConv($this->updated_by)."',
												updated				= '".$this->now."'
												WHERE dispatch_id 	= '".DBVarConv($this->id)."'
												AND para_check_list_id = '".DBVarConv($checkpoint['id'])."'";
						$dbqry = new dbquery($query);
					} else {
						$query 	= "	INSERT INTO ".$this->tablename_checklist." SET
									dispatch_id 		= '".DBVarConv($dispatch_id)."',
									para_check_list_id 	= '".DBVarConv($checkpoint['id'])."',
									remarks 			= '".DBVarConv($checkpoint['remarks'])."',
									created_by 			= '".DBVarConv($this->created_by)."',
									updated_by 			= '".DBVarConv($this->updated_by)."',
									created				= '".$this->now."',
									updated				= '".$this->now."'";
						$dbqry = new dbquery($query);
					}
				}
			}
			$GLOBALS['LOGDATABASE']->log_action($GLOBALS['LOGDATABASE']->Agreegator_Collection_Updated,$dispatch_id,$this->tablename_checklist,"Collection Payment Checklist verified.");
			$GLOBALS[SESSION_OBJ_NAME]->addstatusmessage("Agreegator Collection Payment check list saved.");
		}
	}

	/**
	* Function Name : saveAgreegatorCollectionPaymentPlan
	* @param $id
	* @return
	* @author Kalpak Prajapati
	*/
	function saveAgreegatorCollectionPaymentPlan($dispatch_id=0)
	{
		if(!empty($dispatch_id) && !empty($this->arrPaymentAccounts))
		{
			$DeleteSQL = "	DELETE FROM ".$this->tablename_payment_plan." 
							WHERE dispatch_id = '".DBVarConv($this->id)."'
							AND approved = 0 ";
			new dbquery($DeleteSQL);
			foreach ($this->arrPaymentAccounts as $PaymentRequestRow) {
				$PaymentPlan 		= "	SELECT * FROM ".$this->tablename_payment_plan."
										WHERE bank_account_id 	= '".DBVarConv($PaymentRequestRow['bank_account_id'])."'
										AND dispatch_id 		= '".DBVarConv($dispatch_id)."'";
				$PaymentPlanRes 	= new dbquery($PaymentPlan);
				$Remarks = (isset($PaymentRequestRow['remarks'])?$PaymentRequestRow['remarks']:"");
				if ($PaymentPlanRes->numrows() > 0) 
				{
					$PaymentPlanRow = $PaymentPlanRes->getrowarray(MYSQL_ASSOC);
					if ($PaymentPlanRow['approved'] == 0 && empty($PaymentPlanRow['transaction_id']))
					{
						$query 				= "	UPDATE ".$this->tablename_payment_plan." SET
												bank_account_id 	= '".DBVarConv($PaymentRequestRow['bank_account_id'])."',
												amount 				= '".DBVarConv($PaymentRequestRow['amount'])."',
												remarks 			= '".DBVarConv($Remarks)."',
												updated_by			= '".$this->updated_by."',
												updated				= '".$this->now."'
												WHERE dispatch_id 	= '".DBVarConv($this->id)."'
												AND bank_account_id = '".DBVarConv($PaymentRequestRow['bank_account_id'])."'";
						$dbqry = new dbquery($query);
					}
				} else {
					$query 	= "	INSERT INTO ".$this->tablename_payment_plan." SET
								dispatch_id 		= '".DBVarConv($dispatch_id)."',
								bank_account_id 	= '".DBVarConv($PaymentRequestRow['bank_account_id'])."',
								amount 				= '".DBVarConv($PaymentRequestRow['amount'])."',
								remarks 			= '".DBVarConv($Remarks)."',
								approved			= '0',
								requested_by		= '".$this->requested_by."',
								created_by			= '".$this->created_by."',
								created				= '".$this->now."',
								updated_by			= '".$this->updated_by."',
								updated				= '".$this->now."'";
					$dbqry = new dbquery($query);
				}
			}
			$GLOBALS['LOGDATABASE']->log_action($GLOBALS['LOGDATABASE']->Agreegator_Collection_Updated,$dispatch_id,$this->tablename_payment_plan,"Collection Payment Plan scheduled.");
			$GLOBALS[SESSION_OBJ_NAME]->addstatusmessage("Agreegator Collection Payment plan scheduled successfully.");
		}
	}

	/**
	* Function Name : retrievePaymentChecklist
	* @param $dispatch_id
	* @return $arrResult
	* @author Kalpak Prajapati
	*/
	function retrievePaymentChecklist($dispatch_id=0)
	{
		$id 		= $dispatch_id;
		$tablename 	= $this->tablename_checklist;
		$pk 		= $tablename.'.dispatch_id';
		$query 		= "	SELECT $tablename.*, CB.username as Approved_By, UB.username as Last_Updated_By
						FROM $tablename 
						LEFT JOIN adminuser CB ON $tablename.created_by = CB.adminuserid
						LEFT JOIN adminuser UB ON $tablename.updated_by = UB.adminuserid
						WHERE $pk = '$id'";
        $dbqry 		= new dbquery($query);
		if($dbqry->numrows() > 0)
		{
			while ($row = $dbqry->getrowarray(MYSQL_ASSOC)) {
				$this->checkpoints[$row['para_check_list_id']] = $row;
			}
		}
	}

	/**
	* Function Name : retrieveScheduledPaymentPlan
	* @param $dispatch_id
	* @return $arrResult
	* @author Kalpak Prajapati
	*/
	function retrieveScheduledPaymentPlan($dispatch_id=0)
	{
		$id 		= $dispatch_id;
		$tablename 	= $this->tablename_payment_plan;
		$pk 		= $tablename.'.dispatch_id';
		$query 		= "	SELECT $tablename.bank_account_id,$tablename.amount,$tablename.approved,$tablename.remarks,
						IF (macro_agreegators.name IS NOT NULL, macro_agreegators.name, adminuser.username) as Requested_By,
						ABD.account_holder_name,ABD.account_no,ABD.name_of_bank,AT.para_value as Account_Type
						FROM $tablename 
						INNER JOIN macro_agreegator_bank_details ABD ON $tablename.bank_account_id = ABD.id
						INNER JOIN parameter as AT ON ABD.para_account_type_id = AT.para_id
						LEFT JOIN adminuser ON $tablename.requested_by = adminuser.adminuserid
						LEFT JOIN macro_agreegators ON $tablename.requested_by = macro_agreegators.id
						WHERE $pk = '$id'";
        $dbqry 		= new dbquery($query);
		if($dbqry->numrows() > 0)
		{
			while ($row = $dbqry->getrowarray(MYSQL_ASSOC)) {
				$this->arrPaymentAccounts[$row['bank_account_id']] = $row;
			}
		}
	}

	/**
	* Function Name : retrievePaymentCheckPoints
	* @param
	* @return $arrResult
	* @author Kalpak Prajapati
	*/
	function retrievePaymentCheckPoints()
	{
		include_once(PATH_ABSOLUTE_CLASS."parameter.cls.php");
		$PARAMETER 		= new clsparameter();
		return $PARAMETER->GetSupplierPaymentCheckPoints();		
	}

	/**
    * Function Name saveDispatchPaymentStatus
	* @param 
    * @return
	* @author Kalpak Prajapati
	*/
	function saveDispatchPaymentStatus($dispatch_id=0,$para_payment_status_type_id=0)
	{
		if (!empty($dispatch_id)) {
			if ($para_payment_status_type_id == $this->PAYMENT_PLAN_INITIATED)
			{
				$query 	= "	UPDATE ".$this->tablename." SET
						para_payment_status_type_id	= '".DBVarConv($para_payment_status_type_id)."',
						updated_by					= '".$this->updated_by."',
						updated_dt					= '".$this->now."'
	                    WHERE id 					= '".DBVarConv($dispatch_id)."'";
	            $dbqry = new dbquery($query);
				$GLOBALS['LOGDATABASE']->log_action($GLOBALS['LOGDATABASE']->Agreegator_Collection_Updated,$dispatch_id,$this->tablename,"Payment Plan scheduled by adminuser.");
	        } else if ($para_payment_status_type_id == $this->PAYMENT_APPROVED) {
	        	$query 	= "	UPDATE ".$this->tablename." SET
							para_payment_status_type_id	= '".DBVarConv($para_payment_status_type_id)."',
							payment_approved_by			= '".$this->payment_approved_by."',
							payment_approved_dt			= '".$this->now."',
							updated_by					= '".$this->updated_by."',
							updated_dt					= '".$this->now."'
		                    WHERE id 					= '".DBVarConv($dispatch_id)."'";
	            $dbqry = new dbquery($query);
				$GLOBALS['LOGDATABASE']->log_action($GLOBALS['LOGDATABASE']->Agreegator_Collection_Updated,$dispatch_id,$this->tablename,"Payment Plan approved by adminuser.");
	        }
		}
	}

	/**
    * Function Name retrievePaymentAmount
	* @param 
    * @return
	* @author Kalpak Prajapati
	*/
	function retrievePaymentAmount($agreegator_id=0)
	{
		$ProductDetailHtml['TOTAL_QTY'] 			= "";
		$ProductDetailHtml['TOTAL_AMOUNT'] 			= "";
		$ProductDetailHtml['TOTAL_AMOUNT_IN_WORDS'] = "";
		$ProductDetailHtml['TAX_PRODUCT_DETAILS'] 	= "";
		$ProductDetailHtml['TAX_AMOUNT_IN_WORDS'] 	= "";
		$TOTAL_QTY 									= 0;
		$TOTAL_AMOUNT 								= 0;
		$TOTAL_CGST 								= 0;
		$TOTAL_SGST 								= 0;
		$TOTAL_TAX_AMOUNT 							= 0;
		$TOTAL_AMOUNT_TO_PAY						= 0;
		if (!empty($agreegator_id))
		{
			include_once(PATH_ABSOLUTE_CLASS."agreegators.cls.php");
			$clsagreegator = new clsagreegator();
			$clsagreegator->retrieveAgreegator($agreegator_id);
			if (!empty($this->arrProducts))
			{
				$RowID 					= 1;
				foreach ($this->arrProducts as $arrProduct) {
					$Quantity 			= !empty($arrProduct['audit_qty'])?$arrProduct['audit_qty']:$arrProduct['qty'];
					$Amount 			= $Quantity * $arrProduct['rate'];
					$CGST 				= 0;
					$SGST 				= 0;
					if (!empty($clsagreegator->gst_no)) {
						$CGST 				= ($Amount > 0)?(($Amount * $arrProduct['CGST'])/100):0;
						$SGST 				= ($Amount > 0)?(($Amount * $arrProduct['SGST'])/100):0;
					}
					$TOTAL_AMOUNT 		+= $Amount;
					$TOTAL_QTY 			+= $Quantity;
					$TOTAL_CGST 		+= $CGST;
					$TOTAL_SGST 		+= $SGST;
					$TOTAL_TAX_AMOUNT 	+= ($TOTAL_CGST + $TOTAL_SGST);
					$RowID++;
				}
				$TOTAL_AMOUNT_TO_PAY = $TOTAL_AMOUNT + $TOTAL_TAX_AMOUNT;
			}
			$this->payment_amount 						= _FormatNumberV2($TOTAL_AMOUNT_TO_PAY);
			$this->total_collection_weight 				= _FormatNumberV2($TOTAL_QTY);
		}
		$ProductDetailHtml['TOTAL_QTY'] 				= _FormatNumberV2($TOTAL_QTY)." KGS";
		$ProductDetailHtml['TOTAL_AMOUNT'] 				= "RS "._FormatNumberV2($TOTAL_AMOUNT);
		$ProductDetailHtml['TOTAL_TAX_AMOUNT'] 			= "RS "._FormatNumberV2($TOTAL_TAX_AMOUNT);
		$ProductDetailHtml['TOTAL_AMOUNT_IN_WORDS'] 	= ($TOTAL_AMOUNT > 0)?ucwords(getIndianCurrency(floatval($TOTAL_AMOUNT))):"-";
		$ProductDetailHtml['TAX_AMOUNT_IN_WORDS'] 		= ($TOTAL_TAX_AMOUNT > 0)?ucwords(getIndianCurrency(floatval($TOTAL_TAX_AMOUNT))):"-";
		$ProductDetailHtml['TOTAL_AMOUNT_TO_PAY'] 		= "RS "._FormatNumberV2($TOTAL_AMOUNT_TO_PAY);
		$ProductDetailHtml['TOTAL_AMOUNT_TO_PAY_WORDS'] = ($TOTAL_AMOUNT_TO_PAY > 0)?ucwords(getIndianCurrency(floatval($TOTAL_AMOUNT_TO_PAY))):"-";
		return $ProductDetailHtml;
	}

	/**
    * Function Name SendPushNotificationToFactory
	* @param $dispatch_id
	* @param $wm_client_master_id
	* @param $agreegator_id
    * @return
	* @author Kalpak Prajapati
	*/
	public function SendPushNotificationToFactory($dispatch_id=0,$wm_client_master_id=0,$agreegator_id=0)
	{
		if (empty($dispatch_id) || empty($wm_client_master_id) || empty($agreegator_id)) return false;

		include_once(PATH_ABSOLUTE_CLASS."agreegators.cls.php");
		$clsagreegator 	= new clsagreegator();
		$clsagreegator->retrieveAgreegator($agreegator_id);
		$arrUsers 		= $clsagreegator->GetAgreegatorUsersByID($wm_client_master_id,true);
		if (!empty($arrUsers))
		{
			$message['title'] 		= "Let's Recycle Macro Agreegator";
			$message['message'] 	= "New Dispatch From ".$clsagreegator->name;
			$message['dispatch_id'] = $dispatch_id;
			foreach ($arrUsers as $arrUser)
			{
				if ($arrUser['device_type'] == 'A') {
					$this->SendAndroidPush($arrUser['device_id'],$message);
				} else if ($arrUser['device_type'] == 'I') {
					$this->SendiOSPush($arrUser['device_id'],$message);
				}
			}
		}
	}

	/**
    * Function Name SendUnloadNotification
	* @param $dispatch_id
	* @return
	* @author Kalpak Prajapati
	*/
	public function SendUnloadNotification($dispatch_id=0)
	{
		if (empty($dispatch_id)) return false;
		include_once(PATH_ABSOLUTE_CLASS."agreegators.cls.php");
		$arrFilter 			= array("id='".intval($dispatch_id)."'");
		$arrCollections 	= $this->retrieveAgreegatorCollectionListByFilter($arrFilter);
		if (isset($arrCollections[$dispatch_id])) 
		{
			$clsagreegator 		= new clsagreegator();
			$agreegator_id 		= $arrCollections[$dispatch_id]['agreegator_id'];
			$wm_client_master_id= $arrCollections[$dispatch_id]['wm_client_master_id'];
			$clsagreegator->retrieveAgreegator($agreegator_id);
			$arrUsers 			= $clsagreegator->GetAgreegatorUsersByID($wm_client_master_id,true,false);
			if (!empty($arrUsers))
			{
				$message['title'] 		= "Let's Recycle Macro Agreegator";
				$message['message'] 	= "Unload Dispatch From ".$clsagreegator->name;
				$message['dispatch_id'] = $dispatch_id;
				foreach ($arrUsers as $arrUser)
				{
					if ($arrUser['device_type'] == 'A') {
						$this->SendAndroidPush($arrUser['device_id'],$message);
					} else if ($arrUser['device_type'] == 'I') {
						$this->SendiOSPush($arrUser['device_id'],$message);
					}
				}
			}
			$arrUsers 	= $clsagreegator->GetAgreegatorUsersByID($agreegator_id,true,true);
			if (!empty($arrUsers))
			{
				$message['title'] 		= "Let's Recycle Macro Agreegator";
				$message['message'] 	= "Your material has been unloaded at factory.";
				$message['dispatch_id'] = $dispatch_id;
				foreach ($arrUsers as $arrUser)
				{
					if ($arrUser['device_type'] == 'A') {
						$this->SendAndroidPush($arrUser['device_id'],$message);
					} else if ($arrUser['device_type'] == 'I') {
						$this->SendiOSPush($arrUser['device_id'],$message);
					}
				}
			}
		}
	}

	/**
    * Function Name SendAuditNotification
	* @param $dispatch_id
    * @return
	* @author Kalpak Prajapati
	*/
	public function SendAuditNotification($dispatch_id=0)
	{
		if (empty($dispatch_id)) return false;
		include_once(PATH_ABSOLUTE_CLASS."agreegators.cls.php");
		$arrFilter 			= array("id='".intval($dispatch_id)."'");
		$arrCollections 	= $this->retrieveAgreegatorCollectionListByFilter($arrFilter);
		if (isset($arrCollections[$dispatch_id])) 
		{
			$clsagreegator 		= new clsagreegator();
			$agreegator_id 		= $arrCollections[$dispatch_id]['agreegator_id'];
			$wm_client_master_id= $arrCollections[$dispatch_id]['wm_client_master_id'];
			$clsagreegator->retrieveAgreegator($agreegator_id);
			$arrUsers 			= $clsagreegator->GetAgreegatorUsersByID($wm_client_master_id,true,false);

			if (!empty($arrUsers))
			{
				$message['title'] 		= "Let's Recycle Macro Agreegator";
				$message['message'] 	= "Audited Dispatch From ".$clsagreegator->name;
				$message['dispatch_id'] = $dispatch_id;
				foreach ($arrUsers as $arrUser)
				{
					if ($arrUser['device_type'] == 'A') {
						$this->SendAndroidPush($arrUser['device_id'],$message);
					} else if ($arrUser['device_type'] == 'I') {
						$this->SendiOSPush($arrUser['device_id'],$message);
					}
				}
			}
			$arrUsers 	= $clsagreegator->GetAgreegatorUsersByID($agreegator_id,true,true);
			if (!empty($arrUsers))
			{
				$message['title'] 		= "Let's Recycle Macro Agreegator";
				$message['message'] 	= "Your material audit has been finished at factory.";
				$message['dispatch_id'] = $dispatch_id;
				foreach ($arrUsers as $arrUser)
				{
					if ($arrUser['device_type'] == 'A') {
						$this->SendAndroidPush($arrUser['device_id'],$message);
					} else if ($arrUser['device_type'] == 'I') {
						$this->SendiOSPush($arrUser['device_id'],$message);
					}
				}
			}
		}
	}

	/**
    * Function Name SendAndroidPush
	* @param $user_token
	* @param $message
	* @return
	* @author Kalpak Prajapati
	*/
	public function SendAndroidPush($user_token,$message)
	{
		$server_key 	= "AIzaSyDKJVXr2dE9GXaGx-Wfsg1B69P3QKU9e6I"; // Firebase key
		$ndata 			= array("collapse_key"=>"type_a",
								"title"=>$message['title'],
								"body"=>$message['message'],
								"dispatch_id"=>$message['dispatch_id']);
		$url 			= 'https://fcm.googleapis.com/fcm/send';
		$fields 		= array();
		$fields['data'] = $ndata;
		$fields['to'] 	= $user_token;
		$headers 		= array('Content-Type:application/json','Authorization:key='.$server_key);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		$result = curl_exec($ch);
		if ($result === FALSE) {
		    return ('FCM Send Error: ' . curl_error($ch));
		}
		curl_close($ch);
	}

	/**
    * Function Name SendiOSPush
	* @param $user_token
	* @param $message
	* @return
	* @author Kalpak Prajapati
	*/
	public function SendiOSPush($user_token,$message)
	{
		return;
		$passphrase = "";
		$ctx 		= stream_context_create();
		$filename 	= PATH_ABSOLUTE_SECURE."push/iOS/developement/pushcert.pem";
		if (!file_exists($filename)) {
			return;
		}
		stream_context_set_option($ctx, 'ssl', 'local_cert',$filename);
		stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

		$fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', 
								    $err, 
								    $errstr, 
								    60, 
								    STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, 
								    $ctx);

		if (!$fp) return ("Failed to connect : $err $errstr");

		//echo 'Connected to APNS' . PHP_EOL;

		// Create the payload body
		$body['aps'] 	= array('badge' => +1,
								'alert' => $message['message'],
								'sound' => 'default',
								'dispatch_id'=>$message['dispatch_id']);
		$payload 		= json_encode($body);
		// Build the binary notification
		$msg = chr(0) . pack('n', 32) . pack('H*', $user_token) . pack('n', strlen($payload)) . $payload;
		// Send it to the server
		$result = fwrite($fp, $msg, strlen($msg));
		if (!$result)
		    return 'Message not delivered' . PHP_EOL;
		else
		    return 'Message successfully delivered user '.$message['message']. PHP_EOL;
		// Close the connection to the server
		fclose($fp);
	}
	
	/**
    * Function Name UpdateProductDispatchPhoto
	* @param 
    * @return
	* @author Kalpak Prajapati
	*/
	function UpdateProductDispatchPhoto($DocumentID=0,$ProductID=0,$DispatchID=0)
	{
		if (empty($DocumentID) || empty($ProductID) || empty($DispatchID) == 0) return;
		$query 				= "	UPDATE ".$this->tablename_products." SET
								document_id 		= '".DBVarConv($DocumentID)."'
								WHERE dispatch_id 	= '".DBVarConv($DispatchID)."'
								AND product_id 		= '".DBVarConv($ProductID)."'";
		$dbqry = new dbquery($query);
	}

	/**
	* Function Name : validateGenerateQueueCollection
	* @param
	* @return
	* @author Kalpak Prajapati
	*/
	function validateGenerateQueueCollection()
	{
		//TODO: ADD VALIDATIONS REQUIRED FOR INSERT/UPDATE
		if (empty($this->id) || !ctype_digit($this->id)) $this->error[] = "Collection ID is required field.";
		// if (empty($this->sales_invoice_no)) $this->error[] = "Invoice No. is required field.";
		$arrFilter 			= array("id='".intval($this->id)."'");
		$arrCollections 	= $this->retrieveAgreegatorCollectionListByFilter($arrFilter);
		if (empty($arrCollections) || !isset($arrCollections[$this->id])) $this->error[] = "Invalid Collection details.";
		if (!empty($arrCollections) && isset($arrCollections[$this->id])) {
			if ($arrCollections[$this->id]['queue_no'] > 0) {
				$this->error[] = "Vehicle already in queue.";
			}
		}
		return (count($this->error)>0)?false:true;
	}

	/**
	* Function Name : GenerateCreditNoteNo
	* @param $agreegator_id
	* @param $dispatch_id
	* @return $queue_no
	* @author Kalpak Prajapati
	*/
	function GenerateQueueNo($agreegator_id=0,$dispatch_id=0)
	{
		$queue_no 		= 0;
		$Dispatch 		= "SELECT created_dt FROM ".$this->tablename." WHERE id = '".intval($dispatch_id)."'";
		$DispatchRes 	= new dbquery($Dispatch);
		if ($DispatchRes->numrows() > 0)
		{
			$DispatchRow	= $DispatchRes->getrowarray(MYSQL_ASSOC);
			$StartDate 		= date("Y-m-d",strtotime($DispatchRow['created_dt']))." 00:00:00";
			$EndDate 		= date("Y-m-d",strtotime($DispatchRow['created_dt']))." 23:59:59";
			$select 		= "	SELECT MAX(queue_no) AS MAX_ID 
								FROM $this->tablename WHERE created_dt BETWEEN '".$StartDate."' AND '".$EndDate."'
								AND agreegator_id = '".intval($agreegator_id)."'";
			$result 		= new dbquery($select);
			$row			= $result->getrowarray(MYSQL_ASSOC);
			$queue_no 		= (!empty($row['MAX_ID']) ? $row['MAX_ID'] + 1 : 1);
		}
		return $queue_no;
	}

	/**
    * Function Name GenerateAgreegatorCollectionQueue
	* @param 
    * @return
	* @author Kalpak Prajapati
	*/
	function GenerateAgreegatorCollectionQueue()
	{
		$this->queue_no = $this->GenerateQueueNo($this->agreegator_id,$this->id);
		if ($this->queue_no > 0)
		{
			$query 	= "	UPDATE ".$this->tablename." SET
						queue_no					= '".$this->queue_no."',
						updated_by					= '".$this->updated_by."',
						updated_dt					= '".$this->now."'
	                    WHERE id 					= '".DBVarConv($this->id)."'";
			$dbqry = new dbquery($query);
			$GLOBALS['LOGDATABASE']->log_action($GLOBALS['LOGDATABASE']->Agreegator_Collection_Queue_Generated,$this->id,$this->tablename);
		}
	}

    /**
     * Function Name Genereate Eway Bill
     * @param
     * @return
     * @author Sachin Patel
     */
    function GenerateEwaybill($DISPATCH_ID=0)
    {
    	include_once(PATH_ABSOLUTE_CLASS.'agreegators.cls.php');
    	include_once(PATH_ABSOLUTE_CLASS.'ewaybill/EwayBill.php');

    	if (empty($DISPATCH_ID)) return;

    	$this->retrieveAgreegatorCollection($DISPATCH_ID);

    	if ($this->travel_distance < 9) //return false; //If travel distance is less than 9 KM we don't requires ewaybill

    	$productsArray      = $this->arrProducts;
        $clsagreegator		= new clsagreegator();
        $clsagreegator->retrieveAgreegator($this->agreegator_id);

        if (!empty($clsagreegator->gstin_no)) //return false; //If AGREEGATOR is registers we don't need EWAY BILL

        $CLIENT_MASTER 		= $clsagreegator->getAssignedCustomers($this->agreegator_id,true,true,$this->wm_client_master_id);
        $itemList 			= array();
        $INVOICE_NO			= $this->GetChallanNo($this->challan_no,$this->created_dt);
        $BILL_TO_NAME		= LR_COMPANY_NAME;
        $BILL_TO_ADDRESS	= LR_COMPANY_ADDRESS;
        $BILL_TO_GSTNO		= LR_COMPANY_GSTNO;
        $BILL_TO_STATE		= LR_COMPANY_STATENAME;
        $BILL_TO_CODE		= LR_COMPANY_STATECODE;
        $BILL_TO_GSTNO		= "05AAAAH1426Q1ZO"; //FOR DEMO
        $BILL_TO_ZIP		= LR_COMPANY_PINCODE;
        $SHIP_TO_NAME		= $CLIENT_MASTER[$this->wm_client_master_id]['client_name'];
        $SHIP_TO_ADDRESS    = "";
        if (!empty($CLIENT_MASTER[$this->wm_client_master_id]['address'])) {
            $ADDRESS = explode(",",$CLIENT_MASTER[$this->wm_client_master_id]['address']);
            foreach ($ADDRESS as $key => $ADDRESS_ROW) {
                if ($key > 1) {
                    $SHIP_TO_ADDRESS .= "<br />";
                }
                $SHIP_TO_ADDRESS .= $ADDRESS_ROW.",";
            }
            $SHIP_TO_ADDRESS = trim($SHIP_TO_ADDRESS,"<br />");
            $SHIP_TO_ADDRESS = trim($SHIP_TO_ADDRESS,",");
        }
        $SHIP_TO_GSTNO		= $CLIENT_MASTER[$this->wm_client_master_id]['gstin_no'];
        $SHIP_TO_GSTNO		= "05AAAAH2043K1Z1"; //FOR DEMO
        $SHIP_TO_STATE		= $CLIENT_MASTER[$this->wm_client_master_id]['statename'];
        $SHIP_TO_CODE		= $CLIENT_MASTER[$this->wm_client_master_id]['state_code'];
        $SHIP_TO_ZIP		= $CLIENT_MASTER[$this->wm_client_master_id]['pincode'];
        $TOTAL_AMOUNT       = 0;
        $TOTAL_TAX_AMOUNT   = 0;
        $TOTAL_CGST         = 0;
        $TOTAL_SGST         = 0;
        $TOTAL_IGST         = 0;
        $TAX_AMOUNT 		= 0;
        $CGST 				= 0;
        $SGST 				= 0;
        $IncludeGST			= 0; //IF WE NEED TO INCLUDE GST IN EWAY BILL ENABLE THIS FLAG.
        foreach ($productsArray as $key => $arrProduct) 
        {
            $Quantity 			= $arrProduct["net_weight"];
            $Product_Rate 		= $arrProduct['rate'];
            $Amount 			= $Quantity * $Product_Rate;
            if ($IncludeGST)
            {
	            $CGST 				= ($Amount > 0)?(($Amount * $arrProduct['CGST'])/100):0;
	            $SGST 				= ($Amount > 0)?(($Amount * $arrProduct['SGST'])/100):0;
	            $TOTAL_CGST         += $arrProduct['CGST'];
	            $TOTAL_SGST         += $arrProduct['SGST'];
	            $TAX_AMOUNT         = $Amount + $CGST + $SGST;
	            $TOTAL_TAX_AMOUNT   += $Amount + $CGST + $SGST;
	        }
            $TOTAL_AMOUNT += $Amount;
            $itemList[$key]["productName"]      = $arrProduct['Product_Name'];
            $itemList[$key]["productDesc"]      = $arrProduct['Product_Name'];
            $itemList[$key]["hsnCode"]          = (float)$arrProduct['HSN'];
            $itemList[$key]["quantity"]         = (float)$arrProduct['net_weight'];
            $itemList[$key]["qtyUnit"]          = $arrProduct['UOM'];
            if ($IncludeGST)
            {
	            $itemList[$key]["cgstRate"]         = (float)$arrProduct['CGST'];
	            $itemList[$key]["sgstRate"]         = (float)$arrProduct['SGST'];
	            $itemList[$key]["igstRate"]         = 0;
	            $itemList[$key]["cessRate"]         = 0;
	        } else {
	        	$itemList[$key]["cgstRate"]         = 0;
	            $itemList[$key]["sgstRate"]         = 0;
	            $itemList[$key]["igstRate"]         = 0;
	            $itemList[$key]["cessRate"]         = 0;
	        }
            $itemList[$key]["taxableAmount"]    = (float)$TAX_AMOUNT;
            $itemList[$key]["Amount"]           = (float)$Amount;
            $itemList[$key]["CGST"]             = (float)$CGST;
            $itemList[$key]["SGST"]             = (float)$SGST;
        }

        if ($TOTAL_AMOUNT < 50000) //return false; //If total amount > 50000 we don't requires ewaybill

        $ewaybillrequest = [
            "agreegator_id"     => $this->agreegator_id,
            "supplyType"        => "O",
            "subSupplyType"     => "1",
            "subSupplyDesc"     => "",
            "docType"           => "INV",
            "docNo"             => $this->challan_no,
            "docDate"           => date("d/m/Y",strtotime($this->created_dt)),
            "fromGstin"         => $BILL_TO_GSTNO, //05AAAAH1426Q1ZO,
            "fromTrdName"       => $BILL_TO_NAME,
            "fromAddr1"         => strip_tags($BILL_TO_ADDRESS),
            "fromAddr2"         => $BILL_TO_STATE,
            "fromPlace"         => $BILL_TO_STATE,
            "fromPincode"       => $BILL_TO_ZIP,
            "actFromStateCode"  => $BILL_TO_CODE,
            "fromStateCode"     => $BILL_TO_CODE,
            "toGstin"           => $SHIP_TO_GSTNO, //05AAAAH2043K1Z1
            "toTrdName"         => $SHIP_TO_NAME,
            "toAddr1"           => strip_tags($SHIP_TO_ADDRESS),
            "toAddr2"           => $SHIP_TO_STATE,
            "toPlace"           => $SHIP_TO_STATE,
            "toPincode"         => $SHIP_TO_ZIP,
            "actToStateCode"    => $SHIP_TO_CODE,
            "toStateCode"       => $SHIP_TO_CODE,
            "totalValue"        => (float)$TOTAL_AMOUNT,
            "cgstValue"         => (float)$TOTAL_CGST,
            "sgstValue"         => (float)$TOTAL_SGST,
            "igstValue"         => (float)$TOTAL_IGST,
            "cessValue"         => 0,
            "totInvValue"       => (float)$TOTAL_TAX_AMOUNT,
            "transporterId"     => "",
            "transporterName"   => "",
            "transDocNo"        => "",
            "transMode"         => "1",
            "transDistance"     => $this->travel_distance,
            "transDocDate"      => "",
            "transactionType"   => 1,
            "vehicleNo"         => $VEHICLE_NO = !empty($this->vehicle_no)?$this->vehicle_no:"-",
            "vehicleType"       => "R",
            "itemList"          => $itemList
        ];
        $ewayObj            = new EwayBill();
        $ewayBillApi        = $ewayObj->ewayBillApi($ewaybillrequest);
        if(isset($ewayBillApi)) {
            $result = json_decode($ewayBillApi['response']);
            if(isset($result) && $result->status == 1) {
                $decsek         = generateAesEncryption($ewayObj->SEK,$ewayObj->encAppKey,1);
                $requestData    = generateAesEncryption(json_encode($result->data),$decsek,1);
                $ewayBillNo     = json_decode($requestData);
                if (isset($ewayBillNo->ewayBillNo) && !empty($ewayBillNo->ewayBillNo)) {
                	$this->UpdateEWaybillNo($DISPATCH_ID,$ewayBillNo->ewayBillNo);
                }
            }
        }
    }

	/**
    * Function Name UpdateEWaybillNo
	* @param 
    * @return
	* @author Kalpak Prajapati
	*/
	function UpdateEWaybillNo($dispatch_id,$ewayBillNo)
	{
		$this->updated_by = !empty($this->updated_by)?$this->updated_by:0;
		$this->updated_by = isset($GLOBALS[SESSION_OBJ_NAME]->adminuserid)?$GLOBALS[SESSION_OBJ_NAME]->adminuserid:$this->updated_by;
		$this->updated_by = empty($this->updated_by)?1:$this->updated_by;
		$query 	= "	UPDATE ".$this->tablename." SET
					eway_bill_no				= '".DBVarConv($ewayBillNo)."',
					updated_by					= '".$this->updated_by."',
					updated_dt					= '".$this->now."'
                    WHERE id 					= '".DBVarConv($dispatch_id)."'";
		$dbqry = new dbquery($query);
		$GLOBALS['LOGDATABASE']->log_action($GLOBALS['LOGDATABASE']->Agreegator_Collection_EwayBill_Generated,$dispatch_id,$this->tablename);
	}

	/**
    * Function Name saveAgreegatorLocation
	* @param 
    * @return
	* @author Kalpak Prajapati
	*/
	function saveAgreegatorLocation()
	{
		include_once(PATH_ABSOLUTE_CLASS.'agreegators.cls.php');
		$clsagreegator 	= new clsagreegator();
		$created_by 	= isset($GLOBALS[SESSION_OBJ_NAME]->adminuserid)?$GLOBALS[SESSION_OBJ_NAME]->adminuserid:$this->created_by;
		$clsagreegator->saveAgreegatorAddress($this->agreegator_id,$this->source_address,$this->source_lat,$this->source_long,$created_by);
	}

	/**
    * Function Name getVehicleForQueue
	* @param 
    * @return
	* @author Kalpak Prajapati
	*/
	function getVehicleForQueue()
	{
		$arrResult	= array();
		$id 		= $this->wm_client_master_id;
		$tablename 	= $this->tablename;
		$pk 		= $tablename.'.wm_client_master_id';
		$query 		= "	SELECT $tablename.vehicle_no FROM $tablename 
						WHERE $pk = '$id' AND $tablename.queue_no <= 0
						GROUP BY $tablename.vehicle_no";
        $dbqry 		= new dbquery($query);
		if($dbqry->numrows() > 0)
		{
			while ($row = $dbqry->getrowarray(MYSQL_ASSOC)) {
				$arrResult[] = $row['vehicle_no'];
			}
		}
		return $arrResult;
	}
}
?>