<?php

$rootDir = $_SERVER['DOCUMENT_ROOT'];
//$rootDir = "/var/www/vhosts/nepra.co.in/letsrecycle.co.in";
$protocol = env('APP_URL');
// (strpos(strtolower($_SERVER['SERVER_PROTOCOL']),'https') === FALSE) ? $protocol='http://' : $protocol='https://';
define("ERROR_LOG_EMAILS",array("error@letsrecycle.in","axay.shah@nepra.co.in"));
define("URL_HTTP_LOCAL_PREFIX", $protocol);
define("APP_ENVIRONMENT","PROD");

define("EMAIL_FROM_NAME","Nepra Resource Management Private Limited");
define("EMAIL_FROM_ID","donotreply@nepra.co.in");

// define('EPR_URL','http://master.eprconnect.in/api/TBWeb/LRCollectionDispatchController/CreateLRCollectionDispatch');
define('EPR_URL','https://wma.eprconnect.in/api/TBWeb/LRCollectionDispatchController/CreateLRCollectionDispatch');
define('SUPER_ADMIN_COMPANY','0');
define('MASTER_ADMIN','10');
define('CONST_HTTP_NAME','http://');
define("URL_HTTP",URL_HTTP_LOCAL_PREFIX);
define("URL_HTTP_IMAGES",URL_HTTP_LOCAL_PREFIX."/images/");
define("URL_HTTP_COMPANY",URL_HTTP_IMAGES."company/");
define("PATH_ABSOLUTE_LOCAL_PROJECT",$rootDir."/");
define("PATH_ABSOLUTE_HTTP",PATH_ABSOLUTE_LOCAL_PROJECT);
define("PATH_ABSOLUTE_HTTP_IMAGES",PATH_ABSOLUTE_HTTP."images/");
define("PATH_ABSOLUTE_HTTP_IMAGES_CATEGORY",PATH_ABSOLUTE_HTTP_IMAGES."category/");
define("PATH_ABSOLUTE_HTTP_IMAGES_COMPANY_CATEGORY",PATH_ABSOLUTE_HTTP_IMAGES."/company/category/");
define("PATH_COLLECTION_RECIPT_PDF","pdf/");
define("URL_COLLECTION_RECIPT_PDF",URL_HTTP_LOCAL_PREFIX."/pdf/");
define("PATH_IMAGE",'images');
define("PATH_SERVICE",'service');
define("PATH_CHALLAN",'challan');
define("PATH_EPR",'EPR');
define("PATH_JOBWORK",'jobwork');
define("PATH_CREDIT_NOTE",'credit_notes');
define("PATH_AWS_FAILED_IMAGES",'aws_failed_images');
define("PATH_DISPATCH_PLAN",'dispatch_plan');
// define("FULL_UPLOAD_PATH",public_path('/'));
define("PATH_COMPANY",'company');
define("PATH_COMPANY_CATEGORY",'category');
define("PATH_COMPANY_PRODUCT",'product');
define("PATH_COMPANY_COLLECTIONTAG",'collectiontag');
define("PATH_COMPANY_CUSTOMER",'customer');
define("PATH_COMPANY_ADMIN",'adminuser');
define("PATH_COMPANY_HELPER",'helper');
define("PATH_COMPANY_VEHICLE",'vehicle_document');
define("PATH_COMPANY_SCOPING_IMG",'scoping_img');
define("PATH_VISITOR_CARD_IMG",'visitor_card');
define("PATH_EXPANSE_SHEET",'expanse_sheet');
define("PATH_AGGREMENT_SHEET",'aggrement_sheet');
define("PATH_APPOINTMENT_IMG",'appointment_images');
define("PATH_INVOICE_REMARK",'invoice_remark_doc');
define("MASTER_CODE_CLIENT",'CLIENT');
define("MASTER_CODE_TRANSPORTER",'TRANSPORTER');
define("MASTER_CODE_MRF",'MRF');
define("MASTER_CODE_CUSTOMER",'CUSTOMER');
define("MASTER_CODE_HELPER",'HELPER');
define("MASTER_CODE_COMPANY",'COMPANY');
define("MASTER_CODE_BOP_SURVEY",'BOP_SURVEY');
define("MASTER_CODE_SOCIETY",'SOCIETY');
define("MASTER_CODE_BIN",'BIN');
define("PRICE_GROUP_PRIFIX",'PG-');
define("RESIZE_HIGHT",116);
define("RESIZE_WIDTH",116);
define("RESIZE_PRIFIX","resize_");
define("IMAGE_EXTENSTION",array('jpg','jpeg','gif','png'));
define("PATH_CUSTOMER_DOC",'doc');
define("PATH_CUSTOMER_PROFILE",'profile_picture');
define("PATH_ADMIN_PROFILE",'profile_photo');
define("PATH_TRANSFER",'transfer');
define("SUPERADMIN_TYPE",10);
define("ATTENDANCE_FOLDER",'attendance');
define("WEIGHT_SCALE_FOLDER",'weightscale');
define("PATH_DISPATCH",'dispatch');
define("PATH_COMPANY_CLIENT",'client');
define("PATH_CLIENT_DOC",'docs');
// Default pagination query parameter
define('DEFAULT_SIZE',10);

######################################## RECYCLE REWARD API CONSTANTS ########################################
define("RR_API_URL","http://www.recyclereward.in/");
define("RR_HMAC_HASH_PRIVATE_KEY","e249c439ed7697df2a4b045d97d4b9b7e1854c3ff8dd668c779013653913572e");
define("RR_AUTH_USER","RR_API_USER");
define("RR_AUTH_PASS","RecycleReward@2014");
define("RR_COMPANY_ID",3);
define("ADANI_MUNDRA_ZIPCODES",serialize(array("370421")));



/*############## IMAGES MOVE MODULES NAME AND PATH ####################*/
define("CATEGORY_MOVE",'CATEGORY');
define("COLLECTION_TAG_MOVE",'COLLECTION_TAG');

/*##################### MASTER IMAGES PATH ###############*/
define("MASTER_PATH",URL_HTTP_COMPANY."0");
define("MASTER_CATEGORY",'category');
define("COMPANY_CATEGORY",'category');
define("COMPANY_COLLECTION_TAG",'collectiontag');
define("MASTER_COLLECTION_TAG",'collectiontag');

/*###############PAGINATION CONSTANT################*/
define('PAGE_NUMBER_ATTR','pageNumber');

############ SALES MODULE ##########
define('SALES_MODULE_IMG','sales_module');
define('DIRECT_DISPATCH_IMG','direct_dispatch');
define('PAYMENT_SLIP','payment_slip');
define("INVOICE_URL","invoice");

############## NRMPL DETAILS ################
define('NESPL_TITLE','NEPRA ENVIRONMENTAL SOLUTIONS PRIVATE LIMITED');
define('NESPL_GST','24AACCP0273F1ZU');
define('NESPL_CIN','U74900GJ2000PTC037540');
define('NESPL_ADDRESS','207,Kalasagar Shooping Hub, Opp Sai Temple,Sattadhar Crossing Ghatlodia,Ahmedabad,Gujarat 380061');
define("PURCHASE_TO_SALES_EXPORT","export-purchase-to-sales-excel");
define("EWAY_BILL_PORTAL_URL","https://ewaybill.yugtia.com/api/v1/");
// define("EWAY_BILL_PORTAL_URL","https://ewaybill.eprconnect.in/api/v1/");
define('NRMPL_TITLE','Nepra Resource Management Private Limited');

#################### NET SUIT #####################
define("NETSUITE_URL",'https://7024172.restlets.api.netsuite.com/app/site/hosting/restlet.nl');
define("NETSUITE_ACCOUNT",'7024172');
######### Old credential PRATIK SOFT ############
// define("NETSUITE_CONSUMER_KEY",'c70def8105e6bf60dc6cc63e6665df2e93bb47b07dac83f6b1251cad3f2dd725');
// define("NETSUITE_CONSUMER_SECRET",'a877fa5187d5aa07bf23116e5718404094be36fa613ef455a400667e29033090');
// define("NETSUITE_TOKEN_ID",'59e0bc884baf8ad5c111a24b496da8631f9c587ad7e08d9b648e44db4c7f46d3');
// define("NETSUITE_TOKEN_SECRET",'093356ae4d99331d84c0fc157aa0a6e9c2ee16f404fab4140405dc5defd79ccd');
######### Old credential PRATIK SOFT ############
######### NEW credential Shivanand ############
define("NETSUITE_CONSUMER_KEY",'3646b083278380d32942f01a2dc509f8469b13af729dd155871847004317bc02');
define("NETSUITE_CONSUMER_SECRET",'7b22f0dec5cba9d142d8993ff359c456461e16a9d39eec50a926d57977ce1d00');
define("NETSUITE_TOKEN_ID",'b9687065dc72b9697e4056f8ac43ccfe3a3bbe5c10ed49038dcf226a14366425');
define("NETSUITE_TOKEN_SECRET",'9aa53dc5f1916c3771a2b7a65869855444e361e8e48fa22ec894536258adacf2');
######### NEW credential Shivanand ############

define("VENDOR_API_SCRIPT",'6');
define("ITEM_API_SCRIPT",'129');
define("CUSTOMER_API_SCRIPT",'128');
define("DRIVER_API_SCRIPT",'130');
define("STOCK_API_SCRIPT",'127');
define("DEPLOY",'1');
/* NOTE IN PURCHASE ITEM WE ARE SENDING EXPENCE ACCOUNT ID 58 IN ASSETACCOUNT PARAMETER 
IN SALES SEND INCOME ACCOUNT ID 514 SEND IN ASSET ACCOUNT PARAMETER */
define("SALES_ASSET_ACCOUNT",'514');
define("SALES_COGS_ACCOUNT",'113');
define("PURCHASE_ASSET_ACCOUNT",'58');
define("PURCHASE_COGS_ACCOUNT",'113');
define('PATH_SERVICE_INVOICE','invoice');

#################### NET SUIT #####################
define('PROJECT_LR',1); 
define('PROJECT_IMS',2); 
define('PROJECT_BAMS',3); 
define('PROJECT_NCA',4); 
define("PROJECT_LIST",array(
		array("project_name"=>"IMS","porject_code"=>passencrypt(PROJECT_IMS)),
		array("project_name"=>"BAMS","porject_code"=>passencrypt(PROJECT_BAMS)),
		array("project_name"=>"NCA","porject_code"=>passencrypt(PROJECT_NCA)),
		// array("project_name"=>"LR","porject_code"=>passencrypt(PROJECT_LR))
	));
define('PROJECT_LR_URL',"https://v2-api.letsrecycle.co.in/api/user/auto-login/".passencrypt(date("Y-m-d H:i:s"))); 
define('PROJECT_NCA_URL',"https://cfm.yugtia.com/auto-login/".passencrypt(date("Y-m-d H:i:s"))); 
define('PROJECT_IMS_URL',"https://ims-api.nepra.co.in/api/company/v1/auto-login/".passencrypt(date("Y-m-d H:i:s"))); 
define('PROJECT_BAMS_URL',"https://bams.nepra.co.in/company/auto-login/".passencrypt(date("Y-m-d H:i:s"))); 
define('PROJECT_BAMS_VENDOR_DATA_URL',"https://bams.nepra.co.in/api/company/vendor/vendor-data-for-lr"); 
define('PROJECT_BAMS_PO_CREATE_URL',"https://bams.nepra.co.in/api/company/po/transporter/create"); 

######### LIVE DIGITAL SIGNATURE ############
define('DIGITAL_SIGNATURE_FLAG',1); 
define('DIGI_SIGN_URL',"https://remotesigning-prod.emudhra.com/api/Doc/v1/Signature"); 

define('DIGI_SIGN_CLIENT_ID',"3026701539"); 
define('DIGI_SIGN_KEY_ID',"pHXJL8eI"); 
define('DIGI_SIGN_ACCESS_KEY',"64c2e1450bc14f978cc97028da9c9b45"); 
define('DIGI_SIGN_CERTIFIED',1); 
define('DIGI_SIGN_APPEARANCE_ID',1); 
// define('DIGI_SIGN_POSITION',"A-XY|390,68,560,138"); 
define('DIGI_SIGN_POSITION',"L-BR");
define('DIGI_SIGN_LOCATION_TXT',""); 
define('DIGI_SIGN_TICK',1); 
define('DIGI_SIGN_BORDER',1); 
define('DIGI_SIGN_SESSION_ID',""); 
define('DIGI_SIGN_ID',1); 
define('DIGI_SIGN_DOC_TYPE',"PDF"); 
define('DIGI_VERSION',"1.0"); 
define('DIGI_SIGN_STANDALONE',"no"); 
define('DIGI_SIGN_VERSION',"1.0"); 
######### DIGITAL SIGNATURE ############
define("PATH_VENDOR_REGISTER_FORM",'vendor_registerform');
define("PATH_PAN_DOC",'pan_document');
define("PATH_GST_DOC",'gst_document');
define("PATH_MSME_DOC",'msme_document');

define('PATH_ASSET_INVOICE','asset');
define('PATH_ASSET','assetinvoice');
define('ASSET_FILE_PREFIX','assetinvoice_');
define('ASSET_DATE_CHECK','2010-03-15');

######## ELCITA MODULE RELETED CONSTANT - 27-04-2023####
define("MASTER_CODE_ELCITA",'ELCITA_INV');
define("PATH_ELCITA_PDF","elcita/");
define('EPR_PO_CHECK_URL',"https://wma.eprconnect.in/api/TBWeb/LRCollectionDispatchController/GetVendorPOForLR"); 
define('PROJECT_BAMS_LR_VENDOR_DATA_URL',"https://bams.nepra.co.in/api/company/vendor/vendor-data-for-lr");
define("REDUCTION_AWS_URL",'http://ec2-65-2-73-13.ap-south-1.compute.amazonaws.com:8000/redaction');

define('PROJECT_TRADEX_SERVICE_INVOICE_GENERATE_URL',"https://staging.eprtradex.com/api/Web/LRToEPRTradex/UpdatePendingTaxInvoiceForBuyerSeller");
define("MASTER_CODE_CLIENT_NET_SUIT_CODE",'CLIENT_NET_SUIT_CODE');
