<?php
/* NOTE : comman filed which no need to change for any company will call from parameter table*/


define('MASTER_PASSWORD','NePr@2019$3113');
#define('SMS_GATWAY_URL','http://sms.klicksoft.in/WaveSMS/SMSSender.aspx?LoginID=[SMS_USER]&pwd=[SMS_PASS]&cellNo=[MOBILE]&Message=[MESSAGE]');
#define('SMS_USER','nepra');
#define('SMS_PASS','nespl@sp');


# NEW SMS GETWAY #
// define('SMS_GATWAY_URL','http://malerts.klicksoft.in/sms-panel/api/http/index.php?username=[SMS_USER]&apikey=[SMS_PASS]&apirequest=Unicode&sender=RECYCL&mobile=[MOBILE]&message=[MESSAGE]&route=TRANS&format=JSON');
define('SMS_GATWAY_URL','http://123.108.46.13/sms-panel/api/http/index.php?username=[SMS_USER]&apikey=[SMS_PASS]&apirequest=Unicode&sender=RECYCL&mobile=[MOBILE]&message=[MESSAGE]&route=TRANS&format=JSON');

define('SMS_USER','nepra');
define('SMS_PASS','4799C-239B6'); # API KEY CONSIDER AS PASSWORD TO AVOID CHANGING CODE #
define('SMS_API_KEY','4799C-239B6');

define('SMS_ON',false);
define('STATUS_CODE_SUCCESS','200');
define("TOKEN_EXPIRE_SECONDS",10800);
define('CODE_UNAUTHORISED','401');
define('CODE_TOKEN_NOT_CREATED','500');
define('INTERNAL_SERVER_ERROR','500');
define('SUCCESS','200');
define('ERROR','201');
define('RECORD_PER_PAGE',1);
define('ALL_COLLECTION_RECORD',100);
define('WEB_PREFIX','web/v1/');
define('PARAMETER_STATUS',6);
define('PRODUCT_STATUS_ACTIVE',6001);
define('COMPANY_CATEGORY_STATUS_ACTIVE','Active');

define('PARAMETER_PRODUCT_GROUP',14);
define('CAN_EDIT_ALLOW_USER',1);
define('PARA_RATE_IN_DEFAULT',5002);
define('PRICE_GROUP_TYPE_VERIABLE','V');
define('DEFAULT_PRICE_GROUP_TYPE','F');
define('DEFAULT_PRICE_GROUP_ACTIVE_STATUS','A');
define('CUSTOMER_NEW_PRICE_GROUP','new');
define('CUSTOMER_COPY_PRICE_GROUP','copycustomer');
define('CUSTOMER_EXITING_PRICE_GROUP','existing');
/*Parameter table*/
define('PARA_UOM',8); // parameter table

define('PARA_CUSTOMER_STATUS',3); // parameter table
define('CUSTOMER_STATUS_ACTIVE',3001); // parameter table child of customer status
define('CUSTOMER_STATUS_INACTIVE',3002);// parameter table child of customer status
define('CUSTOMER_STATUS_PENDING',3003);// parameter table child of customer status


define('PARA_COLLECTION_DETAIL_TYPE',11); // parameter table
define('PARA_COLLECTION_DETAIL_PENDING',11001); // parameter table child of collection detail type
define('PARA_COLLECTION_DETAIL_APPROVED',11002); // parameter table child of collection detail type

define('PARAMETER_COST_IN',5);// parameter table
define('PARA_RATE_IN_AMOUNT',5001); // parameter table child of cost in
define('PARA_RATE_IN_PERCENTAGE',5002); // parameter table child of  cost in

define('PARAMETER_PRODUCT_UNIT',8);// parameter table
define('PARA_PRODUCT_UNIT_IN_KG',8001); // parameter table child of product unit
define('PARA_PRODUCT_UNIT_IN_GRAM',8002); // parameter table child of product unit
define('PARA_PRODUCT_UNIT_IN_UNIT',8003); // parameter table child of product unit
define('PARA_PRODUCT_UNIT_IN_DAY',8004); // parameter table child of product unit
define('PARA_PRODUCT_UNIT_IN_OTHER',8005); // parameter table child of product unit
define('PARA_PRODUCT_UNIT_IN_NOS',8006); // parameter table child of product unit
define('PARA_COLLECTION_STATUS',7); // parameter table
define('COLLECTION_PENDING',7001); // parameter table child of collection status
define('COLLECTION_NOT_APPROVED',7002); // parameter table child of collection status
define('COLLECTION_APPROVED',7003); // parameter table child of collection status


define('PARA_CUSTOMER_COMMUNICATION_TYPE',1008); // parameter table
define('COMMUNICATION_TYPE_APP',1008001); // parameter table child of Customer Communication Types
define('COMMUNICATION_TYPE_APP_RECEIPT',1008002); // parameter table child of Customer Communication Types
define('COMMUNICATION_TYPE_APP_PAYMENT_RECEIPT',1008003); // parameter table child of Customer Communication Types
define('COMMUNICATION_TYPE_PROMOTIONAL',1008004); // parameter table child of Customer Communication Types
define('COMMUNICATION_TYPE_COLLECTION_CERTIFICATE',1008005); // parameter table child of Customer Communication Types


define('PARA_CUSTOMER_PAYMENT_MODE',1010); // parameter table
define('CUSTOMER_PAYMENT_MODE_DAILY',1010001); // parameter table child of customer payment mode
define('CUSTOMER_PAYMENT_MODE_WEEKLY',1010002); // parameter table child of customer payment mode
define('CUSTOMER_PAYMENT_MODE_EVERY_15_DAYS',1010003); // parameter table child of customer payment mode
define('CUSTOMER_PAYMENT_MODE_MONTHLY',1010004); // parameter table child of customer payment mode
define('CUSTOMER_PAYMENT_MODE_EVERY_TIME',1010005); // parameter table child of customer payment mode
define('CUSTOMER_PAYMENT_MODE_NO_PAYMENT',1010006); // parameter table child of customer payment mode

define('PARA_COLLECTION_TYPE',1023); // parameter table
define('COLLECTION_TYPE_PAID',1023001); // parameter table child of collection type
define('COLLECTION_TYPE_FOC',1023002); // parameter table child of collection type
define('COLLECTION_TYPE_FOC_PAID',1023003); // parameter table child of collection type

define('PAYMENT_TYPE_PARAMETER',1030); // parameter table child of collection status

define('DISPATCH_TYPE_PARAMETER',1032); // parameter table
define('DISPATCH_CHILD_TYPE_PARAMETER',1032002); // parameter table
define('RECYCLEBLE_TYPE',1032001); // parameter table child
define('NON_RECYCLEBLE_TYPE',1032002); // parameter table child

define('NON_RECYCLEBLE_FOC',103200201); // parameter table child
define('NON_RECYCLEBLE_PAID',103200202); // parameter table child

define('SALES_FOC_PRODUCT',341); // parameter table child

define('SALES_FOC_PRODUCT_ARRAY',array(341,395,420,434,438)); // parameter table child
define('SALES_AFR_PRODUCT',395); // parameter table child



define('PARA_CUSTOMER_TYPE',1007);  // parameter table CUSTOMER TYPE
define('CUSTOMER_TYPE_RECIDENTIAL',1007001);
define('CUSTOMER_TYPE_COMMERCIAL',1007002); // parameter table child of customer type
define('CUSTOMER_TYPE_SCHOOL',1007003); // parameter table child of customer type
define('CUSTOMER_TYPE_INDUSTRIAL',1007004); // parameter table child of customer type
define('CUSTOMER_TYPE_BOP',1007005); // parameter table child of customer type
define('CUSTOMER_TYPE_AGGREGATOR',1007006); // parameter table child of customer type
define('CUSTOMER_TYPE_RESIDENTIAL_SOCIETY',1007007); // parameter table child of customer type
define('CUSTOMER_TYPE_FOC_ROUTE',1007008); // parameter table child of customer type
define('CUSTOMER_TYPE_WASTE_PICKER',1007009); // parameter table child of customer type
define('CUSTOMER_TYPE_COMMERCIAL_FIX_PAYMENT',1007010); // parameter table child of customer type
define('CUSTOMER_TYPE_HOTEL',1007011); // parameter table child of customer type
define('CUSTOMER_TYPE_HOSPITAL',1007012); // parameter table child of customer type
define('CUSTOMER_TYPE_MALL',1007013); // parameter table child of customer type
define('CUSTOMER_TYPE_CORPORATE_OFFICE',1007014); // parameter table child of customer type
define('CUSTOMER_TYPE_BULK_AGGREGATOR',1007015); // parameter table child of customer type
define('CUSTOMER_TYPE_CFM',1007020); // parameter table child of customer type
define('APPOINTMENT_RADIUS',150);

define('CHART_FILED_TYPE',1031); // parameter for chart filed type
define('CHART_FILED_PRODUCT',1031001); // parameter child for chart filed type
define('CHART_FILED_CATEGORY',1031002); // parameter child for chart filed type
define('CHART_FILED_VEHICLE',1031003); // parameter child for chart filed type
define('CHART_FILED_COLLECTION_GROUP',1031004); // parameter child for chart filed type
define('CHART_FILED_CUSTOMER',1031005); // parameter child for chart filed type
define('CHART_FILED_COLLECTIONBY',1031006); // parameter child for chart filed type
define('CHART_FILED_SALES_PRODUCT',1031007); // parameter child for chart filed type
define('LINE_CHART','linechart');

define('PARA_APPOINTMENT_STATUS',2); // parameter table
define('PARA_SALUTION',1); // parameter table
define('PARA_VEHICLE_ASSETS',1018); // parameter table
define('PARA_VEHICLE_DOC_TYPE',1017); // parameter table
define('PARA_VEHICLE_TYPE',1027); // parameter table
define('CUSTOMER_CONVERTED_STATUS',4);

define('PARA_POTENTIAL',1026);  // might be its static
/* GOING TO INSERT IN COMPANY PARAMETER TABLE - AXAY SHAH 04-10-2018*/
define('PARA_CUSTOMER_GROUP',13);
define('PARA_WASTE_TYPE_ID',12);
define('PARA_CUSTOMER_CONTACT_ROLE',1009);
define('PARA_CUSTOMER_REFFERED_BY',1011);
define('PARA_COLLECTION_SITE',1025);
define('PARA_TYPE_OF_COLLECTION',1024);

define('PARA_COLLECTION_ROUTE',19);
/* End */
define('IS_ACCOUNT_MANAGER',1);
define('ADMIN','A'); // default main admin for company
define('FRU','FRU'); // TO ADD DRIVER IN COMPANY
define('CRU','CRU'); // TO ADD DRIVER IN COMPANY
define('GDU','GDU'); // TO ADD DRIVER IN COMPANY
define('CLFS','CLFS');// TO ADD SUPERVISOR IN COMPANY


define('SUPV','SUPV'); // TO ADD DRIVER IN COMPANY
define('CLAG','CLAG'); // TO ADD DRIVER IN COMPANY
define('TRN_LIST_REQ_APPROVAL',10002);
define('CONTACT_TYPE_SMS',1);
define('CONTACT_TYPE_EMAIL',2);
define('CONTACT_TYPE_BOTH',3);

/*READING*/
define('KM_DIGIT_LENGTH',7);
define('FORM_VEHICLE_ID',1);
define('FORM_CUSTOMER_ID',2);
define('FILED_NAME_CUSTOMER','customer_id');
define('FILED_NAME_VEHICLE','vehicle_id');


/*APPOINTMENT*/
define('APPOINTMENT_SCHEDULED',2001);
define('APPOINTMENT_CANCELLED',2002);
define('APPOINTMENT_RESCHEDULED',2003);
define('APPOINTMENT_DELETED',2004);
define('APPOINTMENT_NOT_ASSIGNED',2005);
define('APPOINTMENT_COMPLETED',2006);
define('APPOINTMENT_SCHEDULED_CANCELLED',2007);
define('NEXT_APP_TIME_FROM_MOBILE',0); //in minite
define('MISSED_OR_LATE_RUNNING_TIME',1800); //used in daily summary for widget
define('MISSED_OR_LATE_RUNNING_TIME_FOC',2700); //used in daily summary for widget
define('IDEAL_TIME',15);
define('REOPEN_HOURS',24); // REOPEN APPOINTMENT HOURS

define('EARN_TYPE_REWARD',1);
define('EARN_TYPE_CASH',2);
define('EARN_TYPE_FREE',3);
define('IS_FREE',1); //Appointment is FREE no payment to be done against appointment.
define('APP_TYPE',1);
/*FOC APPOINTMENT */
define('FOC_APPOINTMENT_PENDING',0);
define('FOC_APPOINTMENT_COMPLETE',1);
define('FOC_APPOINTMENT_CANCEL',2);

/*CUSTOMER*/
define('CUSTOMER_POTENTIAL_CONVERTED','CONVERTED');
define('QC_REQ_TRANSACTION_LIMIT',3);
define('CUSTOMER_QC_REQUIRED',1);
define('CUSTOMER_QC_NOT_REQUIRED',0);


/*SCHEDULER*/

define("APP_START_TIME","00:00:00");
define("APP_END_TIME","23:59:59");
define("IS_SCHEDULER_24_HOUR",1);
define("MINUTE_TO_PIXEL",20);
define("VEHICLE_HOUR_SPEED",25);
define("MIN_TRAVEL_TIME", 5);
define("MIN_COLL_AVG_TIME", 15);

/*APPOINTMENT REPORT*/
define("APPOINTMENT_ACCEPTED",9001);
define("COLLECTION_STARTED", 9002);
define("COLLECTION_COMPLETED", 9003);

/*APPOINTMENT TIME REPORT*/
define("APPOINTMENT_REPORT_DEFAULT_END_TIME", "0000-00-00 00:00:00");


/* NOTE THIS WILL DEFINE HERE IN FETURE IF WE CHANGE STATUS THEN WE DONT NEED
TO CHANGE IN WHOEL PROJECT*/
define('SHORT_ACTIVE_STATUS','A');

/*  */

/*static data*/

define('PAYMENT_TYPE_FULL',1); // in customer module filed name payment type
define('MOBILE_COUNTRY_CODE',91); // SENDSMS CLASS
define('MOBILE_DIGIT_LENGHT',10); // SENDSMS CLASS

define("SMS_APPOINTMENT_CONFIRMATION","Thank you for calling [COMPANY_NAME], Executive: - [NAME] will reach at approx [TIME] as per the appointment, T&C Apply. Visit www.letsrecycle.in");
define("SMS_DONATION_CONFIRMATION","Thank you for donating Rs [AMOUNT].Rs [AMOUNT]/- will be donated to [CHARITYNAME].");
//define("SMS_APPOINTMENT_DONE","Thank you, Rs [AMOUNT]/- is the amount you should have received from Lets Recycle Volunteers, Incase of any problem Please Call 9824107807. Thank you Let's Recycle");
define("SMS_APPOINTMENT_DONE","Rs.[AMOUNT]/- is the AMT you should get from [COMPANY_NAME] Executive, Feedback Call [FEED_BACK]. Details visit - www.letsrecycle.in");

define('FOC_RECEIVE_COLLECTION',1);

/*Transcation OTP*/
define('TRNGENERATE_OTP_CODE',123);

/*REQUEST APPROVAL MODULE*/

define('REQUEST_APPROVED',1);
define('REQUEST_REJECT',2);
define('REQUEST_PENDING',0);

define('PRICE_GROUP_ACCEPT',1);
define('PRICE_GROUP_REJECT',2);
define('PRICE_GROUP_PENDING',0);

define('VEHICLE_STATUS_ACTIVE','A');
define('VEHICLE_STATUS_INACTIVE','I');
define('VEHICLE_STATUS_PENDING','P');
define('VEHICLE_STATUS_REJECT','R');

define("GLOBAL_START_TIME","00:00:00");
define("GLOBAL_END_TIME","23:59:59");

//     define('APPOINTMENT_TRACK_HOURS_DB',array("07"=>0,"08"=>0,
//     "09"=>1,"10"=>1,"11"=>1,
//     "12"=>2,"13"=>2,"14"=>2,
//     "15"=>3,"16"=>3,"17"=>3,
//     "18"=>4,"19"=>4,"20"=>4));

// define('APPOINTMENT_TRACK_HOURS',
//     array("0"=>"07:00 AM - 09:00 AM","1"=>"09:00 AM - 12:00 PM",
//         "2"=>"12:00 PM - 03:00 PM","3"=>"03:00 PM - 06:00 PM",
//         "4"=>"06:00 PM - 08:00 PM","5"=>"No Appointment Time"));

 define('APPOINTMENT_TRACK_HOURS_DB',array(
	"00"=>0,"01"=>0,
	"02"=>0,"03"=>0,
	"04"=>0,"05"=>0,
	"05"=>1,"06"=>1,
	"07"=>2,"08"=>2,
	"09"=>3,"10"=>3,"11"=>3,
	"12"=>4,"13"=>4,"14"=>4,
	"15"=>5,"16"=>5,"17"=>5,
	"18"=>6,"19"=>6,"20"=>6));

define('APPOINTMENT_TRACK_HOURS',
	array(
		"0"=>"00:00 AM - 05:00 AM",
		"1"=>"05:00 AM - 07:00 AM",
		"2"=>"07:00 AM - 09:00 AM",
		"3"=>"09:00 AM - 12:00 PM",
		"4"=>"12:00 PM - 03:00 PM",
		"5"=>"03:00 PM - 06:00 PM",
		"6"=>"06:00 PM - 08:00 PM",
		"7"=>"No Appointment Time"
	)
);


/* APPOINTMENT TYPE IN APPOINTMENT SCHEDULAR*/
define("TYPE_DAILY","0");
define("TYPE_WEEKLY","1");
define("TYPE_MONTHLY","2");
define("TYPE_ON_CALL","3");
define("TYPE_OTHER","99");

define("CUS","CUS-");
define('FOC_PRODUCT','50');
define('RDF_PRODUCT','103');
define('PUCHASE_PRODUCT_STOCK_AVG_ZERO',array(103,50));
define('SALES_PRODUCT_STOCK_AVG_ZERO',array(352,201,341));
define('FOC_PRODUCT_QUALITY','51');
define('FOC_CATEGORY','5');
define('PARA_STATUS_NOT_APPROVED','11001');
define('PARA_STATUS_APPROVED','11002');

define("APP_TYPE_NORMAL",1);
define("APP_TYPE_FOC",2);
define("APP_TYPE_GODOWN",3);
define("APP_TYPE_CUSTOMER_GROUP",4);
define("APP_STATUS_REACH",1);

define("APPOINTMENT_TYPE_FOC",1);


define("CUSTOMER_DEFAULT_COUNTRY","India");
define("CUSTOMER_DEFAULT_COUNTRY_ID",1);
define("IS_FINALIZE_RADIUS_FALSE",0);
define("IS_FINALIZE_RADIUS_TRUE",1);
define("VAT_VALUE",5);
define("MOBILE_APP_APPOINTMENT_LIMIT",3);

define('MIN_FOC_COLL_AVG_TIME',60);
define('INERT_STATUS_APPROVE',1);

define('PARA_STATUS_ACTIVE',1);
define('SUPERVISOR',array("A","CRA","FRA","SA","BDM","BRD","CEO","CLAG","CLCO","CLFS","CLSH","DRCT","RLM","YTSA","YTA","BPSH","OPH","FNYP"));
define("ALLOW_APP_BY_ID_BY_DRIVER", 1);


/*EXPORT ALL RECORD IN QUERY OF LISTING*/
define('EXPORT_ALL','1');
define('VALIDATE_GPS_TIME_CHECK_MINS','10');

define('UPLOAD_FILED_NAME','image');
/*######################## MRF MODULE ########################*/
define('AUDIT_STATUS',1);
define('COLLECTION_DATE','2017-01-01 00:00:00');
define('BATCH_PRIFIX','BATCH-00');
define('UNLOAD_MRF_RANGE',200); // RANGE TO UNLOAD FLAG FOR MRF
// IF MULTIPLE BATCH FLAG ON THEN WE NEED TO CREATE MULTIPLE BATCH OTHER WISE NO NEED OF THIS
define('MULTIPLE_BATCH_FLAG_ON',0);
define('SLEEP_TYPE_GROSS_WEIGHT','G');
define('VIRTUAL_MRF_ID','-1');
define('VIRTUAL_UNLOAD_ON','0');


/* HELPER ATTENDANCE FLAG*/
define('NOT_TAKEN',0);
define('FULL_DAY',1);
define('HALF_DAY',2);

/** USED IN UNITWISE COLLECTION REPORT */
define('PROCESS_LOSS',3);

/* HIDDEN BATCH FOR BATCH LISTING*/
define("DISPLAY_HIDDEN_BATCH",0);

/*################## AUDIT BATCH #################*/
define("TRANSFER_BATCH_TYPE","3");
define("TRANSFER_MERGE_BATCH_TYPE","5");
define("IS_AUDITED_FLAG_TRUE",1);

define('AUDIT_STATUS_YES','yes');
define('AUDIT_STATUS_NO','no');
/*################## AUDIT BATCH #################*/

/*################## GPS TRACK PAGE VARS #################*/
define("NOT_AUDITED_COLLECTION",0);
/*################## GPS TRACK PAGE VARS #################*/

/*################## COLLECTION SUMMARY REPORT CONSTANTS ##################*/
define("SUMMARY_REPORT_TO_EMAILS","kalpak@nepra.co.in");
define("CW_DISPOSAL_PRODUCT_ID",50); //USED FOR FOC PRODCT ID NEED TO HAVE FLAG NOW AGAINST PRODUCT

/** Missed Appointment Email To */
define("PAID_MISSED_REPORT_EMAIL_TO","ankit.shah@nepra.co.in");
define("FOC_MISSED_REPORT_EMAIL_TO","ankit.shah@nepra.co.in");
/** Missed Appointment Email To */

/** Collection Variance Email To */
define("COLLECTION_VARIANCE_EMAIL_TO","d.patel@nepra.co.in,ankit.shah@nepra.co.in,priyanka.grover@nepra.co.in");
/** Collection Variance Email To */

/** Batch Summary Email To */
define("BATCH_SUMMARY_EMAIL_TO","jatin@nepra.co.in,ankit.shah@nepra.co.in");
/** Batch Summary Email To */

/** Daily Purchase/Sales Summary Email To */
define("DAILY_SUMMARY_EMAIL_TO","jatin@nepra.co.in");
/** Daily Purchase/Sales Summary Email To */

/** Duplicate Collection Email To */
define("DUPLICATE_COLLECTION_REPORT_TO","raju@nepra.co.in,samir.jani@nepra.co.in,vijendra@nepra.co.in,meenal.modi@nepra.co.in,anamika.sarswat@nepra.co.in");
/** Duplicate Collection Email To */

/** COLLECTION_NOT_UNLOADED_TO_EMAIL Email To */
define("COLLECTION_NOT_UNLOADED_TO_EMAIL","jatin@nepra.co.in,ankit.shah@nepra.co.in");
/** COLLECTION_NOT_UNLOADED_TO_EMAIL Email To */

define("BCC_ALL_REPORTS_TO","reports@letsrecycle.co.in");

/*################## COLLECTION SUMMARY REPORT CONSTANTS ##################*/

/*################## VISIBLE_STATUS_ADMIN_USER ##################*/
define("VISIBLE_STATUS",1);
/*################## VISIBLE_STATUS_ADMIN_USER ##################*/

/*################### DASHBOARD STATUS ############*/

define("DASHBOARD_STATUS",1);
define("APP_TYPE_RUNNING_LATE",1);
define("APP_TYPE_MISSED",2);
define("APP_TYPE_COMPLETED",3);
define("APP_TYPE_CANCEL",4);
define("MIN_COLLECTION_QTY",5);

/*Customer Status*/
define("CUSTOMER_ACTIVE","Active");
define("DEFAULT_DATE_TIME_BLANK","0000-00-00 00:00:00");

/*TEST USER*/
define("TEST_USER_TRUE","Y");
define("TEST_USER_FALSE","N");

/*NOT USE IN APILOG*/
// define('NO_LOG_API',array('get-admin-geo-code','get-inert-deduction','get-monitoring-data','apiDataLogger','report-main-page','customerwise-collection','collection-variance','unitwise-collection','audit-collection','inert-collection-list','vehicle-statistics','today-appointment-summary','today-bop-appointment-summary','duplicate-collection','tallyreport','customerwise-tallyreport','product-variance-report','vehicle-fill-level-report','route-collection','customer-typewise-collection','customer-typewise-year-to-date','batch-summary','gross-margin-productwise','vehicle-tracking-report','action-log-report','paid-missed-appointment','foc-missed-appointment','ActionList','TableList','outward-report','appointment-status','task-group','get-state-with-code','get-state-list','city-state-all','get-usertype-list','dashboard-list-Widget','today-appointment','today-bop-appointment','GetDetailsSummeryOfTodayAppointment','GetDetailsSummeryOfTodayBOPAppointment','dashboard-list-Widget','GetTopTenProductGraph','GetTopFiveSupplierGraph','GetTotalQtyByCategoryGraph','GetTopFiveCollectionGroupGraph','GetCollectionByTypeOfCustomerGraph','ListChart','ListChartFiledType','CreateChartProperty','GetFiledNameByType','GetCustomChartValue','deleteChart','GetDefaultChart','AddDefaultChart','GetLadgerChart','GetDepartmentWiseInwardOutwardChart','GetVehicleWeight','GetCustomerCollectionChart','ProductWithPriceGroupChart','GetRouteCollectionChart','CustomerCollectionTrandChart','GetCustomerCollectionByDate','GetEjbiVehicleList','GetInwardOutwardProductList','EarningReport','VehicleEarningChart','MonthWiseParameter','MonthWiseEarningReport','VehicleTotalEarningInPercent','VehicleAttendanceInPercent'));

define('NO_LOG_API',array( 	
							'DownloadPaymentPlanCSV',
							'get-admin-geo-code',
							'get-inert-deduction',
							'get-monitoring-data',
							'apiDataLogger',
							'report-main-page',
							'customerwise-collection',
							'collection-variance',
							'unitwise-collection',
							'audit-collection',
							'inert-collection-list',
							'vehicle-statistics',
							'today-appointment-summary',
							'today-bop-appointment-summary',
							'duplicate-collection',
							'tallyreport',
							'customerwise-tallyreport',
							'product-variance-report',
							'vehicle-fill-level-report',
							'route-collection',
							'customer-typewise-collection',
							'customer-typewise-year-to-date',
							'batch-summary',
							'gross-margin-productwise',
							'vehicle-tracking-report',
							'action-log-report',
							'paid-missed-appointment',
							'foc-missed-appointment',
							'ActionList',
							'TableList',
							'outward-report',
							'verify-token',
							'listDriverAttendance',
							'complain-list',
							'complain-edit',
							'WGNAReportEmailSend',
							'complaintType',
							'complaintStatus',
							'getById',
							'ListCustomerComplaint',
							'helper-list',
							'helper-edit',
							'company-category-list',
							'company-category-edit',
							'company-category-dropdown',
							'company-category-dropdown',
							'get-all-products',
							'company-product-status',
							'company-product-unit',
							'company-product-group',
							'company-product-list',
							'company-product-detail',
							'company-product-detail',
							'company-veriable-detail',
							'company-scoping-list',
							'company-scoping-edit',
							'company-scoping-list',
							'company-scoping-edit',
							'getCollectionBy',
							'searchSchedular',
							'getSchedularById',
							'editCustomerContact',
							'customerPaymentMode',
							'company-customerStatus',
							'company-customerStatus',
							'company-collectionRoute',
							'company-getWardList',
							'company-getZoneList',
							'company-getSocietyList',
							'company-customer-group',
							'company-customer-refferedBy',
							'company-customer-group',
							'company-potential',
							'company-salution',
							'company-collectionType',
							'company-collectionSite',
							'company-typeOfCollection',
							'company-customer-contactRole',
							'company-customer-communication-types',
							'company-accountManager',
							'company-customer-detail',
							'company-customer-product',
							'company-customer-clonePriceGroup',
							'company-getLastPriceGroupCode',
							'company-priceGroupByCompany',
							'paymentType',
							'getAllCustomerList',
							'get-customer-products',
							'get-state',
							'get-city',
							'city-state-all',
							'generateeprcertificate',
							'checkLastCustomerOtp',
							'generateCustomerotp',
							'getCustomerContactNos',
							'verifyOTPAllowDustbin',
							'viewReceipt',
							'viewCertificate',
							'searchCustomer',
							'appointment-search',
							'foc-appointment-list',
							'foc-appointment-getbyid',
							'foc-customer-list',
							'getUnAssignedAppointmentList',
							'getCanclledAppointmentList',
							'getYearterdayAppointments',
							'getAppointmentByDate',
							'get-monitoring-data',
							'showCurrentAppointmentClientData',
							'get-productBycategory',
							'get-testgps',
							'collection-status',
							'vehicleList',
							'vehicleAssets',
							'vehicleDocType',
							'vehicle-list',
							'vehicleType',
							'vehicle-detail',
							'vehicleReadingReport',
							'retrieveKMReading',
							'getMaxReading',
							'GetVehicleUnMappedUserList',
							'getAllVehicle',
							'listVehicleOwner',
							'request-list',
							'ListPriceGroupApproval',
							'request-detail',
							'getByTrackId',
							'company-parameter-list',
							'company-parameter-type-list',
							'company-parameter-detail',
							'company-baselocation-list',
							'company-baselocation-byId',
							'getCityList',
							'collection-list',
							'collection-detail',
							'getDepartment',
							'getVirtualDepartment',
							'batch-list',
							'unload-list',
							'unload-product',
							'batch-list',
							'getAuditCollectionData',
							'getBatchCollectionData',
							'getBatchReportData',
							'getBatchReportData',
							'batchApprovalSingleList',
							'batchDetailsById',
							'batch-realization-report',
							'customerwise-collection',
							'collection-variance',
							'unitwise-collection',
							'audit-collection',
							'inert-collection-list',
							'vehicle-statistics',
							'today-appointment-summary',
							'today-bop-appointment-summary',
							'duplicate-collection',
							'tallyreport',
							'customerwise-tallyreport',
							'product-variance-report',
							'vehicle-fill-level-report',
							'gross-margin-productwise',
							'action-log-report',
							'paid-missed-appointment',
							'foc-missed-appointment',
							'ActionList',
							'TableList',
							'outward-report',
							'dashboard-list-Widget',
							'list-Dashboard',
							'pending-appointment',
							'today-appointment',
							'today-bop-appointment',
							'GetDetailsSummeryOfTodayAppointment',
							'GetDetailsSummeryOfTodayBOPAppointment',
							'listredeemproductorder',
							'schedulelist',
							'CustomerGetById',
							'GetOrigin',
							'GetDestination',
							'GetSaleProductByPurchaseProduct',
							'ListDispatch',
							'SalesProductDropDown',
							'GetById',
							'SearchInvoice',
							'GetInvoiceById',
							'PaymentHistoryList',
							'GetCustomerShippingAddress',
							'dispatch_type_dropdown',
							'nonrecyclable_type_dropdown',
							'GetInvoice',
							'invoice-approval-list',
							'invoice-approval-by-id',
							'departmenttitle',
							'ListProduct',
							'GetProductGroup',
							'GetById',
							'SalesToPurchaseById',
							'PurchaseToSalesById',
							'SearchWayBridgeSlip',
							'GetWayBridgeById',
							'ClientList',
							'ClientDropDown',
							'ClientAutoCompleteList',
							'ClientGetById',
							'GetGSTStateCode',
							'ListDepartment',
							'GetDepartmentById',
							'GetTopTenProductGraph',
							'GetTopFiveSupplierGraph',
							'GetTotalQtyByCategoryGraph',
							'GetTopFiveCollectionGroupGraph',
							'GetCollectionByTypeOfCustomerGraph',
							'ListChart',
							'ListChartFiledType',
							'GetFiledNameByType',
							'GetCustomChartValue',
							'GetDefaultChart',
							'GetLadgerChart',
							'GetDepartmentWiseInwardOutwardChart',
							'GetVehicleWeight',
							'GetCustomerCollectionChart',
							'ProductWithPriceGroupChart',
							'GetRouteCollectionChart',
							'CustomerCollectionTrandChart',
							'GetCustomerCollectionByDate',
							'GetEjbiVehicleList',
							'GetInwardOutwardProductList',
							'EditEarning',
							'GetDifferenceMappingList',
							'GetAuditedQtyOfVehicle',
							'GetEarningById',
							'ListVehicleEarning',
							'EarningReport',
							'VehicleEarningChart',
							'MonthWiseParameter',
							'MonthWiseEarningReport',
							'VehicleTotalEarningInPercent',
							'VehicleAttendanceInPercent',
							'VehicleEarningSummeryReport',
							'listDriverAttendance',
							'listHelperAttendance',
							'EditAttendance',
							'EditHelperAttendance',
							'ListTransfer',
							'GetTransferById',
							'ListStock',
							'company-driver-list',
							'edit-company-driver',
							'GtsNameList',
							'InwardRemarkList',
							'InwardPlantDetailsById',
							'ListInwardPlantDetails',
							'ListInwardVehicle',
							'EditSegregation',
							'ListInwardSegregation',
							'GetDetailsList',
							'EditBailInward',
							'BailGetById',
							'ListBailInwardList',
							'ListBailStock',
							'filelist',
							'listProject',
							'displayprojectdetails',
							'details',
							'displayclient',
							'showclientaddress',
							'showjobworkdetails',
							'getbyid',
							'getCollectionBy',
							'searchSchedular',
							'getSchedularById',
							'editCustomerContact',
							'customerPaymentMode',
							'customer-list',
							'company-customerStatus',
							'company-collectionRoute',
							'company-getWardList',
							'company-getZoneList',
							'company-customer-refferedBy',
							'company-customer-group',
							'company-potential',
							'collection-dropdown','company-priceGroupByCustomer','company-customer-refferedBy','company-customer-group','generate-transfer-challan',
							'export-purchase-to-sales-excel','sales-item-wise-report-excel','jobwork-report-excel','print_asset_invoice','print_service_invoice','print_jobwork_challan','print_transfer_challan','getChallan','GetInvoice','print_inv','CheckProductReportDone','credit-note-invoice',"mrf_invoice","import-collection"));




define('REPORT_LOG_API',array(
							'customerwise-collection',
							'collection-variance',
							'unitwise-collection',
							'audit-collection',
							'inert-collection-list',
							'vehicle-statistics',
							'today-appointment-summary',
							'today-bop-appointment-summary',
							'duplicate-collection',
							'tallyreport',
							'customerwise-tallyreport',
							'product-variance-report',
							'vehicle-fill-level-report',
							'route-collection',
							'customer-typewise-collection',
							'customer-typewise-year-to-date',
							'batch-summary',
							'gross-margin-productwise',
							'vehicle-tracking-report',
							'action-log-report',
							'paid-missed-appointment',
							'foc-missed-appointment',
							'outward-report',
							'listDriverAttendance',
							'complain-list',
							'WGNAReportEmailSend',
							'getById',
							'vehicleReadingReport',
							'retrieveKMReading',
							'collection-detail',
							'getAuditCollectionData',
							'getBatchCollectionData',
							'getBatchReportData',
							'getBatchReportData',
							'batchApprovalSingleList',
							'batchDetailsById',
							'batch-realization-report',
							'customerwise-collection',
							'collection-variance',
							'unitwise-collection',
							'audit-collection',
							'today-appointment-summary',
							'today-bop-appointment-summary',
							'duplicate-collection',
							'tallyreport',
							'customerwise-tallyreport',
							'product-variance-report',
							'vehicle-fill-level-report',
							'gross-margin-productwise',
							'paid-missed-appointment',
							'foc-missed-appointment',
							'outward-report',
							'GetDetailsSummeryOfTodayAppointment',
							'GetDetailsSummeryOfTodayBOPAppointment',
							'GetInvoice',
							'GetTopTenProductGraph',
							'GetTopFiveSupplierGraph',
							'GetTotalQtyByCategoryGraph',
							'GetTopFiveCollectionGroupGraph',
							'GetCollectionByTypeOfCustomerGraph',
							'ListChart',
							'ListChartFiledType',
							'GetFiledNameByType',
							'GetCustomChartValue',
							'GetDefaultChart',
							'GetLadgerChart',
							'GetDepartmentWiseInwardOutwardChart',
							'GetVehicleWeight',
							'GetCustomerCollectionChart',
							'ProductWithPriceGroupChart',
							'GetRouteCollectionChart',
							'CustomerCollectionTrandChart',
							'GetCustomerCollectionByDate',
							'GetEjbiVehicleList',
							'GetInwardOutwardProductList',
							'ListVehicleEarning',
							'EarningReport',
							'VehicleEarningChart',
							'MonthWiseEarningReport',
							'VehicleTotalEarningInPercent',
							'VehicleAttendanceInPercent',
							'VehicleEarningSummeryReport',
							'listDriverAttendance',
							'listHelperAttendance',
							'ListStock',
							'export-purchase-to-sales-excel',
							'sales-item-wise-report-excel','jobwork-report-excel',"import-collection","DownloadInvoiceByChallan"));

/*ANDROID APP LINK*/
define('ANDROID_APP_LINK','https://i.diawi.com/8R5jJh');

/*Pusher Event*/
define('PUSHER_EVENT_TRACK_VEHICLE','track_vehicle');

// LIVE LINK - 17 APRIL ,2019
// define('ANDROID_APP_LINK','https://i.diawi.com/Do2Gmx');

/*CORPORATE APP*/
define('TYPE_OKAY','ok');
define('TYPE_ERROR','error');
define('APP_CUSTOMER_TYPE_FILTER',"1007002,1007004,1007007,1007003");
define('PARA_PARENT_REQUEST_TYPE_ID',"1015");
define('PARA_REPORT_ISSUE_TYPE_ID',"1016");
define('ARR_APPOINTMENT_TYPES', array(array("id"=>2001,"value"=>"Scheduled"),array("id"=>2002,"value"=>"Cancelled"),array("id"=>2003,"value"=>"Re-Scheduled by customer"),array("id"=>2004,"value"=>"Deleted"),array("id"=>2005,"value"=>"Allocation Pending"),array("id"=>2006,"value"=>"Completed")));
define('ARR_SCHEDULE_TYPES',array(array("id"=>0,"value"=>"Daily"),array("id"=>1,"value"=>"Weekly"),array("id"=>2,"value"=>"Monthly"),array("id"=>3,"value"=>"On Call")));
define('ARR_APPOINTMENT_DAYS',array(array("id"=>1,"value"=>"Monday"),array("id"=>2,"value"=>"Tuesday"),array("id"=>3,"value"=>"Wednesday"),array("id"=>4,"value"=>"Thursday"),array("id"=>5,"value"=>"Friday"),array("id"=>6,"value"=>"Saturday")));

define("TITLE", "LETS-RECYCLE");
define("GENERAL_FROM_EMAIL","info@letsrecycle.in");
define('ARR_COLLECTION_REMARK', array("0"=>"Dustbin not available on place.","1"=>"No waste available in dustbin.","2"=>"Non recyclable (leaves, food waste, soil etc.)","3"=>"Customer not available."));
define('ON_CALL_SCHEDULE', 3);
define('ORDER_REJECTED_BY_CUSTOMER',1);
define('ORDER_STATUS_ACTIVE',1);
define('ORDER_STATUS_REJECT',2);
define('ORDER_STATUS_PENDING',0);
define("URL_HTTP_IMAGES_REDEEM_PRODUCT","images/redeem_product/");
define("SCHEDULE_STATUS_PENDING",0);
define("SCHEDULE_STATUS_APPROVE",1);
define("SCHEDULE_STATUS_REJECT",2);
define("SCHEDULE_APPOINTMENT_TYPE",['0'=>'daily', '1'=>'weekly','2'=>'monthly','3'=>'on call','99'=>'others']);
define('VEHICLE_TRACKING_HOUR',4);
define('PUSHER_DISTANCE_METER',500);
define('PUSHER_DIFF_MINITE',30);

############## BASE LOCATION ######################
define('DEFAULT_BASE_LOCATION_NAME','BASE STATION');

############### AWS COLLECTION LIST ######################
define('AWS_FACE_THRESHOLD','90');

############## DISPATCH MODULE ####################
define('ORIGIN_TYPE',43);
define('DESTINATION_TYPE',44);
define("EXCLUDE_STOCK_DATE","2015-05-29 00:00:00"); //FOR EXCLUDING STOCK
define("SALES_BATCH_TYPE","2");
define("DISPATCH_APPROVED","1");
define("DISPATCH_REJECTED","2");
define("CONVERT_EXT_PDF","pdf");
define("TCS_TEX_PERCENT",0.1);
############# LOGIN TYPE MOBILE #############
define('PASSWORD_LOGIN',1);
define('FACE_LOGIN',2);

########### CUSTOMER COMPLAINT MODULE ############

define('PARA_COMPALINT_TYPE',1028); // parameter table
define('PARA_COMPLAINT_STATUS',1029); // parameter table
define('PARA_COMPLAINT_TYPE_OPEN',1029001);
define('PARA_COMPLAINT_TYPE_CLOSE',1029002);


######## SALES MODULE ###########
define("RATE_ADD_STATUS",1);
define("FIRST_LEVEL_APPROVAL_STATUS",2);
define("SECOND_LEVEL_APPROVAL_STATUS",3);
define("APPROVAL_REJECT_STATUS",4);

define("FIRST_LEVEL_APPROVAL_RIGHTS",56002);
define("HEAD_LEVEL_APPROVAL_RIGHTS",56003);

define("PROCESS_TYPE_GRINDING",1);
define("PROCESS_TYPE_WASHING",2);
define("PROCESS_TYPE_GRANULATING",3);
define("PROCESS_TYPE_BAILING",4);
define("PROCESS_TYPE_LUMPING",5);


/*########## FACE LOGIN MANDATORY FOR DEFINE CITY ###############*/
define("FACE_LOGIN_CITY",["115"]);
define("FACE_LOGIN_ENBLE_FOR_CITY",false);

/*########## AUTH LOGIN USING OTP ###############*/
define("OTP_LOGIN_ON",0);
define("OTP_NUMBER_LENGTH",6);
define("MASTER_OTP",357259);

/*###############PRODUCT VERIANCE COLOR CODE #############*/
define("High_color_code","#e8616e");
define("Medium_color_code","#FFC107");
define("Low_color_code","#65c2fc");

/*########## STOCK ###############*/
define("TYPE_TRANSFER","T");
define("TYPE_SALES","S");
define("TYPE_DISPATCH","D");
define("TYPE_PURCHASE","P");
define("TYPE_PRODUCTION_REPORT","PR");
define("PRODUCT_SALES",2);
define("PRODUCT_PURCHASE",1);
define("TYPE_INWARD","I"); // PURCHASE PRODUCT
define("TYPE_MRF_SHIFT","MS"); //SALES PRODUCT
define("TYPE_JOBWORK","J"); //SALES PRODUCT
define("PRODUCT_INERT",352); //INERT PRODUCT ID FOR STOCK ADJUSTMENT



define("VEHICLE_FIXED","FIXED");
define("TOTAL_FOC_ROUTE_WEIGHT",2500);
define("ROUTE_WEIGHT_PER_CUSTOMER",500);
define("DEFAULT_VEHICLE_FILL_LEVEL",150);

define("DRIVER_USER_TYPE",7);

define("OWNER_AADHAR_CARD",1017001);
define("OWNER_ELECTION_CARD",1017002);
define("OWNER_PAN_CARD",1017009);

define("INWARD_DETAIL_REMARK",1033);
define("MONTH_PARAMETER",1034);
define("APPROVED_COLOR_CODE","#51f0ed");
define('PARA_PROJECT_ID',1036);  // parameter table
define('PARA_FILETYPE_ID',1037);  // parameter table
define('PARA_FILE_AUDIO',103701); // parameter table
define('PARA_FILE_VIDEO',103702); // parameter table
define('PARA_FILE_URL',103703); // parameter table
define('STATUS_ACTIVE', 1);
define("MRF_CITY",array("294")); // DISPATCH MODULE

// rk.patel@nepra.co.in 9824654441
// jatin 9998333325
define('RATE_APRROVAL_EMAILS', array("jatin@nepra.co.in")); /* SEND RATE APPROVAL EMAIL TO THIS EMAIL ID IN DISPATCH - 10*/
define('RATE_APRROVAL_EMAILS_ALL_CITY', array("jatin@nepra.co.in"));
define('RATE_APRROVAL_MOBILE_ALL_CITY', array("9998333325"));
define('RATE_APRROVAL_MOBILE', array("9824654441","9998333325"));
define("FILE_UPLOAD_PATH","uploads");
define("JOBWORK_SR_NO","JOBWORK");
############ INCENTIVE MASTER MODULE ##############
define('OTP_EDIT_ALLOW_USER',1);
define('WGNA_RULE','WGNA_RULE');
define('THREE_MONTH_RULE','THREE_MONTH_RULE');
define('MAX_ATTENDANCE_DAY',28);
define('EWAY_BILL_MIN_AMOUNT',50000);
define("ATTENDANCE_DAY_COUNT","6");
define("ADD_PAST_APPOINTMENT",11010);


define("TYPE_HELPER","H");
define("TYPE_DRIVER","D");

define('PARA_JOBWORK_TYPE',1035);  // parameter table
define('PARA_JOBWORK_WASHING',103501); // parameter table
define('PARA_JOBWORK_LUMPS',103502); // parameter table
define('PARA_JOBWORK_GRINDING',103503); // parameter table
define('PARA_JOBWORK_SORTING',103504); // parameter table



############ SHIFT TYPE ###########

define('PARA_SHIFT_TYPE',1038);
define('PARA_SHIFT_TYPE_1',103800);
define('PARA_SHIFT_TYPE_2',103801);

######### TRANSACTION MASTER CODES ###########
define('RETAIL_SALES_TRANS','RETAIL_SALES_TRANS');
define('JOBWORK_TRANS','JOBWORK_TRANS');
define('TRANSFER_TRANS','TRANSFER_TRANS');
define('RDF_TRANS','RDF_TRANS');
define('CORPORATE_SALES_TRANS','CORPORATE_SALES_TRANS');
define('SALES_OF_SERVICE','SALES_OF_SERVICE');
define('TRANSFER_OF_ASSETS','TRANSFER_OF_ASSETS');
define("PURCHASE_CREDIT_NOTE","PURCHASE_CREDIT_NOTE");
define("PURCHASE_DEBIT_NOTE","PURCHASE_DEBIT_NOTE");
define("DELIVERY_CHALLAN_FLAG",'DELIVERY_CHALLAN_FLAG');
####### APPROVAL RULE MASTER #############
define('ATTENDENCE_APPROVAL',50005);
define('INCENTIVE_APPROVAL',50003);
define('INVOICE_REOPEN_APPROVAL',50002);
define('REQUEST_APPROVAL',10002);
define('CUSTOMER_SCOOPING_APPROVAL',14021);
define('PRICE_GROUP_APPROVAL',50001);

define('RULES_TYPE',1039);
define('HOURS_RULE',1039001);
define('DAILY_RULE',1039002);
define('MONTHLY_RULE',1039003);

###### DISPATCH AUTO GENERATE #########
define('TYPE_OF_TRANSACTION',1040);
define('PARA_CORPORATE_SALES',1040001);
define('PARA_RETAIL_SALES',1040002);
define('PARA_RDF',1040003);
define('TRN_CORPORATE_SALES',56015);
define('TRN_RETAIL_SALES',56016);
define('TRN_RDF',56017);
####### TYPE OF PRODUCT TAGGING 2D OR 3D ###########
define('TYPE_OF_PRODUCT_TAGGING',1041);
define('PARA_2D_TAGGING',1041001);
define('PARA_3D_TAGGING',1041002);
define('PARA_AGREEGATOR_SALES',1041003);


############ 2D 3D REPORT ########
define('SYNOPSIS_INWARD',array("FOC","Paid","Others","RDF"));
define('SYNOPSIS_OUTWARD',array("Sales","Transfer","Unsharedded RDF","Sharedded RDF","Pet Transfer"));
define('SYNOPSIS_3D',array("Inward","Sorting","Grinding","Transfer","Washing","Sales"));
######### 2D SYSNOPSIS ###########
define('SYNOPSIS_FOC',"FOC");
define('SYNOPSIS_PAID',"Paid");
define('SYNOPSIS_OTHERS',"Others");
define('SYNOPSIS_RDF',"RDF");
define('SYNOPSIS_SALES',"Sales");
define('SYNOPSIS_TRANSFER',"Transfer");
define('SYNOPSIS_UNSHAREDDED',"Unsharedded RDF");
define('SYNOPSIS_SHAREDDED',"Sharedded RDF");
define('SYNOPSIS_PET_TRANSFER',"Pet Transfer");
######### 3D SYSNOPSIS ###########
define('SYNOPSIS_3D_INWARD',"Inward");
define('SYNOPSIS_3D_SORTING',"Sorting");
define('SYNOPSIS_3D_GRINDING',"Grinding");
define('SYNOPSIS_3D_TRANSFER',"Transfer");
define('SYNOPSIS_3D_WASHING',"Washing");
define('SYNOPSIS_3D_SALES',"Sales");
define('IN_HOUSE_ID',2);
define('CLIENT_RADIUS',500);


########## RIGHTS ##########
define("RATE_APPROVAL_RIGHTS",56005);

define('BILL_TEE_TYPE',1);
define('WAY_BRIDGE_TYPE',2);
define('CHALLAN_TYPE',3);
define('EWAY_BILL_TYPE',4);
define('BILL_OF_SUPPLY',5);
define('EPR_CRON_ENABLE',true);
define('EPR_START_DATE',"2022-02-02");

###### EPR_EXPENSE_TYPE_ID ######
define("EPR_EXPENSE_TYPE_ID",20);
######## PRODUCTION REPORT ###########
// define("PRODUCTION_REPORT_START_DATE",'2020-09-09');
// define("PRODUCTION_REPORT_START_DATE",'2021-12-15');
// define("PRODUCTION_REPORT_START_DATE",'2022-06-23');
// define("PRODUCTION_REPORT_START_DATE",'2022-12-06');
define("PRODUCTION_REPORT_START_DATE",'2023-05-13');
define("ADJUST_STOCK_EVERY_DAY_MRF_IDS",array());
########## TRANSFER MODULE #########
define("TRANSFER_FINAL_LEVEL_APPROVAL",3);


########## SALES ORDER PLAN MODULE #########
define("SALES_RATE_TEXTBOX_RIGHTS",56030);
######## CREDIT NOTE ##########
define("TCS_STATE_DATE_TIME","2021-02-08 13:00:00");
define("CREDIT_NOTE_PENDING_APPROVAL_EMAIL",array("axay.shah@nepra.co.in"));
define("CREDIT_NOTE","CREDIT_NOTE");
define("DEBIT_NOTE","DEBIT_NOTE");
define("CHANGE_IN_RATE",1);
define("CHANGE_IN_QTY",2);
define("CHANGE_IN_BOTH",3);



######## WAY BRIDGE MODULE ##############
define("TRAN_TAG_INWARD","1");
define("TRAN_TAG_OUTWARD","2");
define("TRAN_TAG_PENDING","0");


define("MODULE_TYPE_JOBWORK","JOBWORK");
define("MODULE_TYPE_TRANSFER","TRANSFER");

define("WAYBRIDGE_MODULE_DISPATCH","1");
define("WAYBRIDGE_MODULE_BATCH","2");



###### MRF WISE TRANSACTION MASTER NEW CHANGES SINCE 24 MARCH 2021 #########

define('RECYCLABLE_TYPE_TRANS','RECYCLABLE_TYPE_TRANS');
define('NON_RECYCLABLE_TYPE_TRANS','NON_RECYCLABLE_TYPE_TRANS');


###### NET SUIT SALES MASTER #########
define('INTERVAL_TIME','-2 hour');
define('INVENTORY_TYPE_PURCHASE','PURCHASE');
define('INVENTORY_TYPE_SALES','SALES');

define('PROCESS_FAILD','3');
define('PROCESS_PENDING','0');

########### E INVOICE ###########
define('E_INVOICE_VERSION','1.1');
define('TAX_SCH','GST');
define('CANCEL_RSN_ARRAY',array("1"=>"Duplicate","2"=>"Data entry mistake","3"=>"Order Cancelled","4"=>"Others"));
define('FREIGHT_SAC_CODE','996519');



define('DISPATCH_RATE_APPROVAL_RIGHTS',56005);
define('DISPATCH_VIRTUAL_RATE_APPROVAL_RIGHTS',56054);
define('PARA_RATE_APPROVAL_REMARK_LIST',1042);


define('APPOINTMENT_COLLECTION_CREDIT_DEBIT',array("0"=>"Select","1"=>"Rate","2"=>"Quantity","3"=>"Rate & Quantity"/*,"4"=>"Quality Difference"*/));
define('CHANGE_IN_LUMSUM',4);
define('LUMSUM_ADD_MULTIPLE',0);

define('PARA_SERVICE_TYPE',1043);  // might be its static

define('PARA_COLLECTION_CYCLE_TERMS',1044);  // might be its static

define('SALES_CN_DN_FIRST_LEVEL_APPROVAL',50016);
define('SALES_CN_DN_FINAL_LEVEL_APPROVAL',50019);
define('PURCHASE_CN_DN_FIRST_LEVEL_APPROVAL',50017);  
define('PURCHASE_CN_DN_FINAL_LEVEL_APPROVAL',50018);  
define('PARA_TRANSPORTER_NAME',1045);  

define('DISPATCH_OFF',0);
define('DISPATCH_OFF_MSG',array("production_date" => array("We are upgrading the GST Rates as per the new guideline. We will inform you when the dispatch will start again.")));

########## EXPORT CUSTOMER CREDENTIAL #############
define('ExportUserName','admin');
define('ExportUserPassword','admin');
define('VALIDATION_ERROR',422);
define('SUCCESS_STATUS',200);
define('ERROR_STATUS',201);
########## EXPORT CUSTOMER CREDENTIAL #############
define('IOT_MRF_SCREENIDS',array(61052));
define('IOT_MRF_ALLOWED',array(22,48));
define('ALLOW_FOR_UNLOAD_ANY_MRF_USER',array(572,1,426));
define('BASE_MRF_SCREEN_IDS',array(850006,880002,61023,47002,56001,56027,63001,56022,68000,60006,56036,50014,56055,72000,47001));
define('ALL_MRF_UNLOAD_SCREEN_IDS',array(61031,800000));
define('BASE_MRF_UNLOAD_SCREEN_IDS',array(47001));
define('ASSIGN_BASE_DEPT_IDS',array(800011,66006,35,61023,790004,61029,790002,880003));
define('ASSIGN_BASE_DEPT_EXCLUDING_SERVICE',array(35,66005,61023,66001,17001,17007,75005,830004,90001,47001,61035,61036,61037,61038,61039,61043));
define('PARA_EPR_SERVICE',1043001);  
define('PARA_OTHER_SERVICE',1043002);

###########  INVOICE REMARKS REASON ################
define("PARA_INVOICE_REMARKS_REASON",1047);
define("CAN_SEE_ALL_INVOICE_REMARKS",61027);
###########  INVOICE REMARKS REASON ################

############################################  EPR SERVICE INVOCE CONSTANTS #################################################
define("EPR_INVOICE_URL","https://wma.eprconnect.in/api/TBWeb/ManageInvoice/LRUpdateGenerateInvoice");
define("EPR_INVOICE_CRON_ENABLE",0);
############################################  EPR SERVICE INVOCE CONSTANTS #################################################

define('CLIENT_ID_MAP_INVOICE',598);  
define('PARA_NET_SUIT_CLASS',1048);  
define('PARA_NET_SUIT_DEPARTMENT',1049); 
define('PARA_CLIENT_CHARGE',1050); 
define('PARA_PAYMENT_VENDOR_TYPE',1051);
define('WITHOUT_SIGNATURE_INVOICE',array(3201133,3201135,3201136,3201143,3201148,3201154,3201158,3201161,3201162,3201163,3201167,3201169,3201036,3201037,3201038,3201039,3201040,3201042,3201043,3201044,3201046,3201048,3201049,3201050,3201051,3201052,3201053,3201058,3201065,3201069,3201078,3201088,3201089,3201092,3201098,3201101,3201102,3201105,3201107,3201114,3201116,3201117,3201119,3201121,3201123,3201127,3201129,3201130,3201131,3201176,3201177,3201181,3201185,3201190,3201193,3201195,3201198,3201202,3201205,3201206,3201207,3201212,3201216,3201218,3201221,3201223,3201227,3201229,3201230,3201236,3201238,3201239,3201240,3201241,3201243,3201253,3201254,3201255,3201256,3201258,3201260,3201263,3201268,3201270,3201274,3201275,3201276,3201277,3201278,3201279,3201280,3201281,3201282,3201283,3201286,
3201287,3201291,3201292,3201296,3201300,3201303,3201305,3201307,3201308,3201316,3201317,3201319,3201321,
3201325,3201326,3201327,3201329,3201332,3201334,3201336,3201337,3201338,3201342,3201343,3201346,3201350,
3201352,3201353,3201354,3201356,3201360,3201363,3201366,3201369,3201374,3201375,3201376,3201377,3201378,
3201379,3201380,3201381,3201382,3201385,3201386,3201388,3201389,3201391,3201393,3201395,3201396,3201397,
3201398,3201399,3201400,3201401,3201402,3201403,3201404,3201405,3201406,3201409,3201410,3201411,3201412,
3201413,3201414,3201417,3201419,3201422,3201424,3201426,3201428,3201431
));

############################################  EXCLUDE PRODUCTS IN PROJECTION PLAN AVG SALES RATES #################################################
define("EXCLUDE_PID_SALES_PROJECTION",array(395,434,420,341));
############################################  EXCLUDE PRODUCTS IN PROJECTION PLAN AVG SALES RATES #################################################
define('PARA_PAYMENT_PLAN_PRIORITY',1046);
define('PARA_PRIORITY_HIGH',1046001);  
define('PARA_PRIORITY_MIDIUM',1046002); 
define('PARA_PRIORITY_LOW',1046003); 
define('FLEXI_PRODUCT_ARRAY',array(20,420,430,434,438,445,446,447,448,449,450,451,452,453,457,367,18,370,371,470,8,469,467,254,468,256,379,13,388,470,469,467,367,369,368,76,468,469,370,379));
define('RIGID_PRODUCT_ARRAY',array(7,74,76,417,112,123,124,115,296,116,197,114,119,117,137,417,76,343,314));
define('EPR_FLEXI_ID',5);
define('EPR_RIGID_ID',4);

define('SALES_SECOND_LEVEL_APPROVAL_RIGHTS',50019); 
define('DISPLAY_ALL_MRF_UNLOAD',47012); 
define('ROLE_WISE_TRN_FLAG',true);

define('RC_BOOK_ID',1017010); 
define('PRINT_INVOICE_WITHOUT_SIGN',56069); 
########## Multiple Dispatch Document Upload #########
define('PARA_BILLT',105201);
define('PARA_WAYBRIDGE',105202);  
define('PARA_EWAY_BILL',105203);  
define('PARA_TRANSPORTER_INV',105204);  
define('PARA_UNLOADING_SLIP',105205); 
define('PARA_STRICK_OUT_EWAY_BILL',105206);
define('TRANSPORTER_APPROVAL_FLAG',0); 
########## Multiple Dispatch Document Upload #########

/** CLIENT CREDIT LIMIT VALIDATION FLAG */
define("VALIDATE_CLIENT_CREDIT_LIMIT",1);
/** CLIENT CREDIT LIMIT VALIDATION FLAG */

/** PARA_KYC_DOCUMENT_TYPES */
define("PARA_KYC_DOCUMENT_TYPES",1053);
/** PARA_KYC_DOCUMENT_TYPES */

/** PARA_DEBTOR_CATEGORY_TYPES */
define("PARA_DEBTOR_CATEGORY_TYPES",1054);
/** PARA_DEBTOR_CATEGORY_TYPES */

/** PARA_DISPATCH_QUALITY_TYPES */
define("PARA_DISPATCH_QUALITY_TYPES",1056);
/** PARA_DISPATCH_QUALITY_TYPES */

/** WEBPORTL_CRED BASIC AUTH */
define("WEBPORTL_CRED","d2ViLXBvcnRhbC1hcGk6cVdpQDZKM08yQGdO");
/** WEBPORTL_CRED BASIC AUTH */
define('PARA_TRANSPORTER_COST_CALCULATION_TYPE',1055);
define('BAMS_TRANSPORTE_ITEM_ID',180);
define('BAMS_TRANSPORTE_UOM_ID',8007);
define("PRODUCT_TYPE_UNIT",8003);
define('BAMS_ADVANCE_PERCENTAGE_PARAMETER',6001);
define('DEMURRAGE_APPLICABLE_TRN_ID',56074);
define("EPR_DIGI_SIGNATURE_START_DATE","10-11-2022");
define("GROUP_CODE","GROUP_CODE");

define("PER_TONNE_ACTUAL_CAPACITY",105501);
define("PER_TONNE_AT_VEHICLE_CAPACITY",105502);
define("FIX_TRANSPORTATION_COST_PER_TRIP",105503);
define("ADMIN_RIGHT_WITHOUT_GST_NO_IN_SERVICE",56075);

define("ROUTEGROUP_USER","user");
define("PARA_PO_PRODUCT_TYPE","1057");
define("TYPE_INTERNAL_TRANSFER","IT");
define("MRF_MAPPED_WITH_BAMS",array(27,23,3,26,125,22,48,125,161,128,11,170));
define("IOT_DASHBOARD_GRAPH_DEVICE_CODE",400260);
define("IOT_DASHBOARD_SLAVE_ID_MRF",5);
define("IOT_DASHBOARD_SLAVE_ID_AFR",60);

define("CGST_RATE","9");
define("SGST_RATE","9");
define("IGST_RATE","18");
define("GST_RATE","18");


#################RECYCLEBLE_TYPE_DISPATCH_TO_TRANSFER FOR EPR#####################
// define("RECYCLEBLE_TYPE_DISPATCH_TO_TRANSFER",array(53799,53902));
// define("RECYCLEBLE_TYPE_DISPATCH_TO_TRANSFER",array(55592,55800,55845,55846,55849,55850,55909,55952,55954,55955,56088,56091,56093,56218,55375,55416,55575,55896,55897,55959,56075,56077,56089));
// define("RECYCLEBLE_TYPE_DISPATCH_TO_TRANSFER",array(55848,55890,55951,55956,56019,56084,56086,56118,56248));
// define("RECYCLEBLE_TYPE_DISPATCH_TO_TRANSFER",array(57347,57145,57040,57039,57007,56972,56932,56758,56757,56701,56525,56523));
// define("RECYCLEBLE_TYPE_DISPATCH_TO_TRANSFER",array(57234,57231,57084,57008,56942,56912,56902,56806,56761,56760,56636,56604,56591,56519,56373,57278,57086,57078,56901,56827,56674,56635,56471,56444,56371,56258));
// define("RECYCLEBLE_TYPE_DISPATCH_TO_TRANSFER",array(57368,57402,57483,57484,57709,57822,57908,57909,57976,58236,58286,58329,58330,58685,58709,58712,58713,58717,58839,58988,58990,59232,59396,59442,59454,59504,59571,59575,59584,59587,59593,59649,59667,59721,59728,59759,59778,59834,59835,59837,59854,59894,59908,59966,59984,59988,60010,60044,60184,60241,60250,60276,60357,60420,60702,58328,58414,58483,59308,59310,59311,59399,59601,59650,59653,59657,59658,59729,59730,59801,59806,59807,59833,59865,59870,59887,59898,59952,60122,60131,60213));
// define("RECYCLEBLE_TYPE_DISPATCH_TO_TRANSFER",array(62134,62178,63133,57471,57541,57788,57988,61524,61783,61786,62083,62328,62550,62938,63056,61670,57804,57868,57869,58042,58962,59359,59360,59510,59669,59719,59720,59743,59744,60669,60684,60725,60953,60965,61003,61092,61121,61239,61674,61675,61732,61773,61778,62736,63084,60843,60924,60950,61008,61019,61099,61120,61201,61212,61236,61237,61352,62112));
define("RECYCLEBLE_TYPE_DISPATCH_TO_TRANSFER",array(54119,54277,54278,54334,54403,54507,54551,54553,54625,59753,59783,59792,59881,59899,60020,60047,60087,60126,60126,60237,60463,60506,60703,60783,60846,60847,60944,60980,61012,61016,61187,61284,61346,61398,61534,61538,61541,61637,61707,61763,61803,61883,62106,62252,62305,62306,62439,62600,62641,62732,62767,62832,62851,62943,63026,63155));
#################RECYCLEBLE_TYPE_DISPATCH_TO_TRANSFER FOR EPR#####################
define("PARA_PO_FOR","1058");
define("PARA_PO_FOR_TRANSFER","1058001");
define("PARA_PO_FOR_COLLECTION","1058002");
define("PARA_PO_FOR_SALES","1058003");
define("PARA_PO_FOR_JOBWORK","1058004");
define('SALES_PRODUCT_INERT_CONTAMINATED',array(352));

define("COMPANY_PAN_ARRAY_NET_SUIT",array("C","F","T"));
define('PARA_CLIENT_PO_PRIORITY',1059);
define('PARA_CLIENT_PO_PRIORITY_P1',1059001);
define('PARA_CLIENT_PO_PRIORITY_P2',1059002);
define('PARA_CLIENT_PO_PRIORITY_P3',1059003);

define('PARA_PRODUCT_UNIT_IN_TRIP',8005); // parameter table child of product unit
define("PARA_FOR_GARDEN_WASTE",264);  //  parameter for Garden Waste
define('CUSTOMER_PRINT_INVOICE',1);  // Customer Print Invoice Button Show/Hide Flag
define('GENERATE_SERVICE_INVOICE_SUCCESS',1);  // Flag Give If Service Invoice generate Successfully
define('GENERATE_SERVICE_INVOICE_ALREADY',2);  // Flag Give If Service Invoice already generated
define("ELCITA_SERVICE_PRODUCT_ID","2");
define("ELCITA_PRODUCT_HSN","999424");
define("ELCITA_MRF_ID",168);
define('COLLECTION_PARTNER_FLAG',1); // EPR PO Collection Partner Flag
define('VAF_REPORT_SCREEN_ID',66010); // EPR PO Collection Partner Flag
define('VAF_MRF',125); // EPR PO Collection Partner Flag
define('EMAIL_PENDING_EINVOICE',array('sejal.banker@nepra.co.in','kiran.prajapati@nepra.co.in','axay.shah@nepra.co.in','sachin.patel@nepra.co.in'));
define('TRADEX_SERVICE_CHARGE_PRODUCT_ID',26); // TRADEX SERVICE CHARGE PRODUCT ID
define('TRADEX_MRF_ID',59); // TRADEX MRF ID
define("NOTINLIST",99999); //ADDED BY KALPAK
define("IOT_ENABLED_PLANT_SCREEN_ID",array(61052,61037,61036,61039,61038,61035,61037,195001,195002));
define('PARA_CREDIT_TRANSFER_SERVICE_TYPE',1043006); //ADDED BY KALPAK FOR INVOICE API FROM EPR
define("MAINTANANCE_CODE_PREFIX","M-"); //ADDED BY KALPAK