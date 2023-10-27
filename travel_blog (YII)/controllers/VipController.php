<?php
namespace frontend\controllers;
use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\mongodb\ActiveRecord;
use frontend\models\LoginForm;
use frontend\models\Vip;
use frontend\models\Verify;
use frontend\models\Credits;
use frontend\models\Order;
use backend\models\AddcreditsPlans;
use backend\models\AddvipPlans;
use backend\models\AddverifyPlans;

class VipController extends Controller
{
   public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }
    
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }
	
    public function actions()
    {
        return [
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'oAuthSuccess'],
            ],
                'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
            ],
        ];           
    }

	public function actionJoinvip() 
	{
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        $email = $session->get('email');
        if ($session->get('email'))
        {
			if(isset($_POST['selected_vip_plan']))
			{
				$id = $_POST['selected_vip_plan'];
				$record = AddvipPlans::find()->where(['_id' => "$id"])->one();
				$tot_month = $record['months'];

				$mon = 'month';
				if($tot_month > 1){$mon .= 's';}
				$tot_month = '+'.$tot_month.' '.$mon;

				$join_vip = new \frontend\models\Vip();
				$date = time();
				$curdate = date('d-m-Y');
				$enddate = date('d-m-Y', strtotime($tot_month));
				
				$result = Vip::find()->where(['user_id' => "$uid",'status' => '1'])->andwhere(['ended_date'=> ['$gte'=>"$curdate"]])->orderBy(['joined_date'=>SORT_DESC])->one();
				if($result)
				{
					return 2;
				}
				else
				{
					$join_vip->user_id=$uid;
					$join_vip->joined_date="$date";
					$join_vip->ended_date="$enddate";
					if($join_vip->insert())
					{
						$user = LoginForm::find()->where(['_id' => $uid])->one();
						$user->vip_flag = '1';
						$user->update();
						
						$cre_amt = 100 * $tot_month;
						$cre_desc = 'join_vip';
						$status = '1';
						$details = $id;
						$credit = new Credits();
						$credit = $credit->addcredits($uid,$cre_amt,$cre_desc,$status,$details);
						return 1;
					}
					else {
						return 0;
					}
				}
			}
        }
        else {
            return $this->goHome();
        }
    }
	
	Public function actionBuycredits()
	{
		$session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
		
		if(isset($_POST['buy_credits']) && !empty($_POST['buy_credits']))
		{
			$credit_id = $_POST['buy_credits'];
			$record_credits = Credits::find()->where(['user_id' => $uid, 'status' => '0'])->one();
			if($record_credits)
			{
				$record_credits->delete();
			}
			
			$credit_plan = AddcreditsPlans::find()->where(['_id' => "$credit_id"])->one();
			$cre_amt = $credit_plan['credits'];
			$status = '0';
			$cre_desc = 'purchasecredits'; 
			$details = "$credit_id";
			$credit = new Credits();
			$credit = $credit->addcredits($uid,$cre_amt,$cre_desc,$status,$details);
			return 1;
			exit;
		}
		if(isset($_REQUEST['st']) && !empty($_REQUEST['st']))
		{
			if($_REQUEST['st'] == "Completed" || $_REQUEST['st'] == "Pending")
			{
				$result = Credits::find()->where(['user_id' => "$uid",'status' => '0'])->one();
				$result->status = "1";
				$result->update();
				
				$credit_plan_id = $result['detail'];
				$credit_plan = AddcreditsPlans::find()->where(['_id' => $credit_plan_id])->one();
				$detail = $credit_plan['credits'];
				$item_number = $_REQUEST['tx'];
				$transaction_id = $_REQUEST['tx'];
				$order_type = 'buycredits';
				$amount = $_REQUEST['amt'];
				$status = $_REQUEST['st'];
				$curancy = $_REQUEST['cc'];
				$order = new Order();
				$order = $order->neworder($item_number,$transaction_id,$amount,$curancy,$status,$order_type,$detail);
				
				$url = Yii::$app->urlManager->createUrl(['site/credits']);
				Yii::$app->getResponse()->redirect($url);
			}
		}

		$url = Yii::$app->urlManager->createUrl(['site/credits']);
		Yii::$app->getResponse()->redirect($url);		
	}
	
	public function actionCheckvip() 
	{
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        $email = $session->get('email');
        if ($session->get('email'))
        {
			if(isset($_POST['selected_vip_plan']))
			{
				$id = $_POST['selected_vip_plan'];
				$record = AddvipPlans::find()->where(['_id' => "$id"])->one();
				$tot_month = $record['months'];

				$mon = 'month';
				if($tot_month > 1){$mon .= 's';}
				$tot_month = '+'.$tot_month.' '.$mon;

				$join_vip = new \frontend\models\Vip();
				$date = time();
				$curdate = date('d-m-Y');
				$enddate = strtotime($tot_month);
				$enddate = date('d-m-Y',$enddate);

				Vip::deleteAll(['user_id' => "$uid",'status' => '0']);
				
				Verify::deleteAll(['user_id' => "$uid",'status' => '0']);
			
				Credits::deleteAll(['user_id' => "$uid",'status' => '0']);
				$result = Vip::isVip($uid);
				if($result)
				{
					return 2;
					exit;
				}
				else
				{
					$join_vip->user_id=$uid;
					$join_vip->joined_date="$curdate";
					$join_vip->ended_date="$enddate";
					$join_vip->status='0';
					if($join_vip->insert())
					{						
						$user = LoginForm::find()->where(['_id' => $uid])->one();
						$user->vip_flag = '0';
						$user->update();
						
						$verify = new Verify();
						$verify->user_id = $uid;
						$verify->joined_date = "$curdate";
						$verify->ended_date = "$enddate";
						$verify->status = '0';
						$verify->insert();
						
						$cre_amt = 100 * $tot_month;
						$cre_desc = 'join_vip';
						$status = '0';
						$details = $id;
						$credit = new Credits();
						$credit = $credit->addcredits($uid,$cre_amt,$cre_desc,$status,$details);
						return 1;
					}
					else
					{
						return 0;
					} 
				}
			}
        }
        else 
		{
            return $this->goHome();
        }
    }
	
	public function actionSuccessvip() 
	{
        $session = Yii::$app->session;
		$uid = (string)$session->get('user_id');
		$email = $session->get('email');
		
        if ($session->get('email'))
        {
			if($_REQUEST['st'] == "Completed" || $_REQUEST['st'] == "Pending")
			{
				$result = Vip::find()->where(['user_id' => "$uid",'status' => '0'])->orderBy(['joined_date'=>SORT_DESC])->one();
				if($result)
				{
					$result->status = '1';
					$result->update();
					
					$end_date = $result->ended_date;
					$user = LoginForm::find()->where(['_id' => "$uid"])->one();
					$user->vip_flag = "$end_date";
					$user->update();
				}
				
				$result2 = Verify::find()->where(['user_id' => "$uid",'status' => '0'])->orderBy(['joined_date'=>SORT_DESC])->one();
				if($result2)
				{
					$result2->status = '1';
					$result2->update();
				}	
				
				$credit = Credits::find()->where(['user_id' => "$uid",'status' => '0'])->orderBy(['joined_date'=>SORT_DESC])->one();
				if($credit)
				{
					$credit->status = '1';
					$credit->update();
				}
				
				$end_date = $result['ended_date']; // or your date as well
				$join_date = $result['joined_date'];
				$datediff = $end_date - $join_date;

				$detail = floor($datediff / (60 * 60 *  24)/30);
				$item_number = $_REQUEST['tx'];
				$order_type = 'joinvip';
				$transaction_id = $_REQUEST['tx'];
				$amount = $_REQUEST['amt'];
				$status = $_REQUEST['st'];
				$curancy = $_REQUEST['cc'];
				$order = new Order();
				$order = $order->neworder($item_number,$transaction_id,$amount,$curancy,$status,$order_type,$detail);
				
				$st = "success";
			}
			else 
			{
				$st = $_REQUEST['st'];
			}
			$url = Yii::$app->urlManager->createUrl(['site/addvip',$st => $st]);
			Yii::$app->getResponse()->redirect($url);

        }
        else
        {
            return $this->goHome();
        }
    }
	
	public function actionSuccessVipCard() 
	{
        $session = Yii::$app->session;
		$uid = (string)$session->get('user_id');
		$email = $session->get('email');
		
        if ($session->get('email'))
        {
			$payableAmount = $_POST['amount'];
			$nameArray = explode(' ',$_POST['name_on_card']);
			
			//Buyer information
			$firstName = $nameArray[0];
			$lastName = $nameArray[1];
			$city = 'Kolkata';
			$zipcode = '700091';
			$countryCode = 'IN';
			
			//Create an instance of PaypalPro class
			$paypal = new \frontend\models\PaypalPro();
			
			//Payment details
			$paypalParams = array(
				'paymentAction' => 'Sale',
				'amount' => $payableAmount,
				'currencyCode' => 'USD',
				'creditCardType' => $_POST['card_type'],
				'creditCardNumber' => trim(str_replace(" ","",$_POST['card_number'])),
				'expMonth' => $_POST['expiry_month'],
				'expYear' => $_POST['expiry_year'],
				'cvv' => $_POST['cvv'],
				'firstName' => $firstName,
				'lastName' => $lastName,
				'city' => $city,
				'zip'	=> $zipcode,
				'countryCode' => $countryCode,
			);
			$response = $paypal::paypalCall($paypalParams);
			
			$paymentStatus = strtoupper($response["ACK"]);
			if ($paymentStatus == "SUCCESS")
			{
				$result = Vip::find()->where(['user_id' => "$uid",'status' => '0'])->orderBy(['joined_date'=>SORT_DESC])->one();
				if($result)
				{
					$result->status = '1';
					$result->update();
					
					$end_date = $result->ended_date;
					$user = LoginForm::find()->where(['_id' => "$uid"])->one();
					$user->vip_flag = "$end_date";
					$user->update();
					
				}
				
				$result2 = Verify::find()->where(['user_id' => "$uid",'status' => '0'])->orderBy(['joined_date'=>SORT_DESC])->one();
				if($result2)
				{
					$result2->status = '1';
					$result2->update();
				}
				
				$credit = Credits::find()->where(['user_id' => "$uid",'status' => '0'])->orderBy(['joined_date'=>SORT_DESC])->one();
				if($credit)
				{
					$credit->status = '1';
					$credit->update();
				}
				
				$end_date = $result['ended_date']; // or your date as well
				$join_date = $result['joined_date'];
				$datediff = $end_date - $join_date;

				$detail = floor($datediff / (60 * 60 *  24)/30);
				$item_number = $response['TRANSACTIONID'];
				$order_type = 'joinvip';
				$transaction_id = $response['TRANSACTIONID'];
				$amount = $response['AMT'];
				$status = $response['ACK'];
				$curancy = $response['CURRENCYCODE'];
				$order = new Order();
				$order = $order->neworder($item_number,$transaction_id,$amount,$curancy,$status,$order_type,$detail);
				
				$st = "success";
				
				$url = Yii::$app->urlManager->createUrl(['site/addvip',$st => $st]);
				Yii::$app->getResponse()->redirect($url);
			}
			else 
			{
				$st = $response['ACK'];
				$url = Yii::$app->urlManager->createUrl(['site/addvip',$st => $st]);
				Yii::$app->getResponse()->redirect($url);
			}			
		}
		else {
			return $this->goHome();
		}	
	}
	
	public function actionFailvip() 
	{
        $session = Yii::$app->session;
		$uid = (string)$session->get('user_id');
		$email = $session->get('email');
		
		$result = Vip::find()->where(['user_id' => "$uid",'status' => '0'])->orderBy(['joined_date'=>SORT_DESC])->one();
		if($result)
		{
			$result->delete();
		}
		$credit = Credits::find()->where(['user_id' => "$uid",'status' => '0'])->orderBy(['joined_date'=>SORT_DESC])->one();
		if($credit)
		{
			$credit->delete();
		}
		$url = Yii::$app->urlManager->createUrl(['site/joinvip']);
		Yii::$app->getResponse()->redirect($url);
        
    }
	
	public function actionAmount() 
	{
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        $email = $session->get('email');
		
    	$id = $_POST['selected_vip_plan'];
		$record = AddvipPlans::find()->where(['_id' => "$id"])->one();
		$tot_month = $record['months'];
		$tot_amount = $record['amount'];
		$vip_amount = ($tot_month * $tot_amount);
		
		return $vip_amount;
	}
	
	public function actionAmountVerify() 
	{
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        $email = $session->get('email');
		
    	$id = $_POST['selected_verify_plan'];
		$record = AddverifyPlans::find()->where(['_id' => "$id"])->one();
		$tot_month = $record['months'];
		$tot_amount = $record['amount'];
		$verify_amount = ($tot_month * $tot_amount);
		
		return $verify_amount;
	}
	
	public function actionSuccessVerifyCard() 
	{
        $session = Yii::$app->session;
		$uid = (string)$session->get('user_id');
		$email = $session->get('email');
		
        if ($session->get('email'))
        {
			$payableAmount = $_POST['amount'];
			$nameArray = explode(' ',$_POST['name_on_card']);
			
			//Buyer information
			$firstName = $nameArray[0];
			$lastName = $nameArray[1];
			$city = 'Kolkata';
			$zipcode = '700091';
			$countryCode = 'IN';
			
			//Create an instance of PaypalPro class
			$paypal = new \frontend\models\PaypalPro();
			
			//Payment details
			$paypalParams = array(
				'paymentAction' => 'Sale',
				'amount' => $payableAmount,
				'currencyCode' => 'USD',
				'creditCardType' => $_POST['card_type'],
				'creditCardNumber' => trim(str_replace(" ","",$_POST['card_number'])),
				'expMonth' => $_POST['expiry_month'],
				'expYear' => $_POST['expiry_year'],
				'cvv' => $_POST['cvv'],
				'firstName' => $firstName,
				'lastName' => $lastName,
				'city' => $city,
				'zip'	=> $zipcode,
				'countryCode' => $countryCode,
			);
			$response = $paypal::paypalCall($paypalParams);
			
			$paymentStatus = strtoupper($response["ACK"]);
			if ($paymentStatus == "SUCCESS")
			{
					
				$result = Verify::find()->where(['user_id' => "$uid",'status' => '0'])->orderBy(['joined_date'=>SORT_DESC])->one();
				if($result)
				{
					$result->status = '1';
					$result->update();
					$ended_date = $result['ended_date'];
					
					$item_number = $response['TRANSACTIONID'];
					$transaction_id = $response['TRANSACTIONID'];
					$order_type = 'verify';
					$detail = '$ended_date';	
					$amount = $response['AMT'];
					$status = $response['ACK'];
					$curancy = $response['CURRENCYCODE'];
					$order = new Order();
					$order = $order->neworder($item_number,$transaction_id,$amount,$curancy,$status,$order_type,$detail);
				}	
				
				$st = "success";
				$url = Yii::$app->urlManager->createUrl(['site/verifyme',$st => $st]);
				Yii::$app->getResponse()->redirect($url);
			}
			else {
				$st = $response['ACK'];
				$url = Yii::$app->urlManager->createUrl(['site/verifyme',$st => $st]);
				Yii::$app->getResponse()->redirect($url);
			}	
			
		}
		else {
			return $this->goHome();
		}	
	}
	
	public function actionSuccessCreditsCard() 
	{
        $session = Yii::$app->session;
		$uid = (string)$session->get('user_id');
		$email = $session->get('email');
		
        if ($session->get('email'))
        {
			$payableAmount = $_POST['amount'];
			$nameArray = explode(' ',$_POST['name_on_card']);
			
			//Buyer information
			$firstName = $nameArray[0];
			$lastName = $nameArray[1];
			$city = 'Kolkata';
			$zipcode = '700091';
			$countryCode = 'IN';
			
			//Create an instance of PaypalPro class
			$paypal = new \frontend\models\PaypalPro();
			
			//Payment details
			$paypalParams = array(
				'paymentAction' => 'Sale',
				'amount' => $payableAmount,
				'currencyCode' => 'USD',
				'creditCardType' => $_POST['card_type'],
				'creditCardNumber' => trim(str_replace(" ","",$_POST['card_number'])),
				'expMonth' => $_POST['expiry_month'],
				'expYear' => $_POST['expiry_year'],
				'cvv' => $_POST['cvv'],
				'firstName' => $firstName,
				'lastName' => $lastName,
				'city' => $city,
				'zip'	=> $zipcode,
				'countryCode' => $countryCode,
			);
			$response = $paypal::paypalCall($paypalParams);
			
			$paymentStatus = strtoupper($response["ACK"]);
			if ($paymentStatus == "SUCCESS")
			{
				$result = Credits::find()->where(['user_id' => "$uid",'status' => '0'])->one();
				if($result)
				{
					$result->status = "1";
					$result->update();	
				
					$credit_plan_id = $result['detail'];
					$credit_plan = AddcreditsPlans::find()->where(['_id' => $credit_plan_id])->one();
					$detail = $credit_plan['credits'];
					$item_number = $response['TRANSACTIONID'];
					$transaction_id = $response['TRANSACTIONID'];
					$order_type = 'buycredits';
					$amount = $response['AMT'];
					$status = $response['ACK'];
					$curancy = $response['CURRENCYCODE'];
					$order = new Order();
					$order = $order->neworder($item_number,$transaction_id,$amount,$curancy,$status,$order_type,$detail);	
					$st = "success";
				}
				$url = Yii::$app->urlManager->createUrl(['site/credits',$st => $st]);
				Yii::$app->getResponse()->redirect($url);
			}
			else 
			{
				$st = $response['ACK'];
				$url = Yii::$app->urlManager->createUrl(['site/credits',$st => $st]);
				Yii::$app->getResponse()->redirect($url);
			}	
		}
		else {
			return $this->goHome();
		}	
	} 	
}
?>