<?php
namespace frontend\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\mongodb\ActiveRecord;
use frontend\models\UserForm;
use frontend\models\LoginForm;
use frontend\models\Trip;
use frontend\models\TripNotes;

class TripController extends Controller
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

	public function actionIndex()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if($user_id)
        {
			$place = '';
			$getusercity = LoginForm::find()->where(['_id' => "$user_id"])->one();
			$place = $getusercity['city'];
			if(!isset($place) && empty($place))
			{
				$ip = $_SERVER['REMOTE_ADDR'];
				$getplaceapi = 'http://freegeoip.net/json/'.$ip;
				$location = file_get_contents($getplaceapi);
				$location = json_decode($location);
				$place = $location->city;
			}
			else
			{
				$place = $place;
			}
			$place = str_replace("'","",$place);
			return $this->render('trip',array('user_id' => $user_id,'place' => $place));  
        }
        else
        {
			$place = Yii::$app->params['place'];
			$user_id = '';
			$place = str_replace("'","",$place);
			return $this->render('trip',array('user_id' => $user_id,'place' => $place));
            //return $this->goHome();
        }
    }
	
	public function actionMytrips()
    {   
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $trips = array();
        $checkuserauthclass = 'checkuserauthclassg';

        if(isset($user_id) && $user_id != '') {
            $checkuserauthclass = UserForm::isUserExistByUid($user_id);
            if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {        
                $trips = Trip::getMyTrips($user_id);  
            }
        }

        return $this->render('mytrips',array('trips' => $trips, 'checkuserauthclass' => $checkuserauthclass));
    }
	
	public function actionNewtrip()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if($user_id)
        {
 			$place = $_POST['place'];
			return $this->render('newtrip',array('user_id' => $user_id,'place' => $place));
        }
        else
        {
            return $this->goHome();
        }
    }
	
	public function actionStoplists()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if($user_id)
        {
			$tripid = $_POST['tripid'];
			$stops = Trip::getTripDetails($tripid);
			return $this->render('stoplists',array('user_id' => $user_id,'tripid'=>$tripid,'stops'=>$stops));
        }
        else
        {
            return $this->goHome();
        }
    }
	
	public function actionDeltrplace()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if($user_id)
        {
            $tripid = $_POST['tripid'];
            $place = trim($_POST['place']);
            $q = Trip::find()->where(['_id' => "$tripid"])->one();
            $stop = $q['end_to'];
            $stop = array_map('trim', explode("**", $stop));
            if(in_array($place, $stop)) {
                $pos = array_search($place, $stop);
                unset($stop[$pos]);
                $stop = implode("**", $stop);
                if(trim($stop) != '')
                {
                    $q->end_to = trim($stop);
    				$q->update();
    			}
    			
                return $tripid;
            }

            return false;
        }
        else
        {
            return $this->goHome();
        }
    }
	
	public function actionDelnote()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if($user_id)
        {
			$tripid = $_POST['tripid'];
			$noteid = $_POST['noteid'];
			$q = TripNotes::find()->where(['_id' => "$noteid",'tripid' => "$tripid"])->one();
			$q->delete();
			return $tripid;
        }
        else
        {
            return $this->goHome();
        }
    }
	
	public function actionEditnote()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if($user_id)
        {
			$noteid = $_POST['noteid'];
			$ntitle = ucfirst($_POST['ntitle']);
			$ntext = ucfirst($_POST['ntext']);
			$q = TripNotes::find()->where(['_id' => "$noteid"])->one();
			$q->notetitle = $ntitle;
			$q->notetext = $ntext;
			$q->update();
			return true;
        }
        else
        {
            return $this->goHome();
        }
    }
	
	public function actionDeltrp()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if($user_id)
        {
			$tripid = $_POST['tripid'];
			Trip::delTrip($tripid);
			return Trip::getMyTripCount($user_id);
        }
        else
        {
            return $this->goHome();
        }
    }
	
	public function actionStopdiv()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if($user_id)
        { 
			$tripid = $_POST['tripid'];  
			$stops = Trip::getTripDetails($tripid);
			return $this->render('stopdiv',array('user_id' => $user_id,'tripid'=>$tripid,'stops'=>$stops));
        }
        else
        {
            return $this->goHome();
        }
    }
	
	public function actionNotescount()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if($user_id)
        {
			$tripid = $_POST['tripid'];
			$notescount = TripNotes::getNotesCount($tripid);
			return $this->render('notescount',array('user_id' => $user_id,'tripid'=>$tripid,'notescount'=>$notescount));
        }
        else
        {
            return $this->goHome();
        }
    }
	
	public function actionEdittrip()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if($user_id)
        {
			$tripid = $_POST['tripid'];
			$stops = Trip::getTripDetails($tripid);
			$notescount = TripNotes::getNotesCount($tripid);
			return $this->render('edittrip',array('user_id' => $user_id,'tripid'=>$tripid,'stops'=>$stops,'notescount'=>$notescount));
        }
        else
        {
            return $this->goHome();
        }
    }
	
	public function actionViewtrip()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if($user_id)
        {
			$tripid = $_POST['tripid'];
			$stops = Trip::getTripDetails($tripid);
			$notescount = TripNotes::getNotesCount($tripid);
			return $this->render('viewtrip',array('user_id' => $user_id,'tripid'=>$tripid,'stops'=>$stops,'notescount'=>$notescount));
        }
        else
        {
            return $this->goHome();
        }
    }

	public function actionNotes()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if($user_id)
        {
			$tripid = $_POST['tripid'];
			$notes = TripNotes::getTripNotes($tripid);
			return $this->render('notes',array('user_id' => $user_id,'tripid' => $tripid,'notes' => $notes,));
        }
        else
        {
            return $this->goHome();
        }
    }

	public function actionBookmarks()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if($user_id)
        {
			return $this->render('bookmarks',array('user_id' => $user_id));
        }
        else
        {
            return $this->goHome();
        }
    }

	public function actionShowbookmark()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if($user_id)
        {
			$baseUrl = $_POST['baseUrl'];
			return $this->render('placedetails',array('user_id' => $user_id,'baseUrl' => $baseUrl));
        }
        else
        {
            return $this->goHome();
        }
    }
	
	public function actionMap()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
       
			$baseUrl = $_POST['baseUrl'];
			$place = $_POST['place'];
			$which = $_POST['which'];
			return $this->render('mapdisplay',array('user_id' => $user_id,'baseUrl' => $baseUrl,'place' => $place,'which' => $which));
    }
	
	public function actionPostmap()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if($user_id)
        {
			$trip = $_POST['trip_id'];
			return $this->render('postmapdisplay',array('user_id' => $user_id,'trip' => $trip));
        }
        else
        {
            return $this->goHome();
        }
    }
	
	public function actionMobilemap()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if($user_id)
        {
			$baseUrl = $_POST['baseUrl'];
			$place = $_POST['place'];
			$which = $_POST['which'];
			return $this->render('mobilemapdisplay',array('user_id' => $user_id,'baseUrl' => $baseUrl,'place' => $place,'which' => $which));
        }
        else
        {
            return $this->goHome();
        }
    }
	
	public function actionMapacco()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if($user_id)
        {
			$baseUrl = $_POST['baseUrl'];
			$place = $_POST['place'];
			$which = $_POST['which'];
			$place = str_replace(' ','+',$place);
			return $this->render('accomapdisplay',array('user_id' => $user_id,'baseUrl' => $baseUrl,'place' => $place,'which' => $which));
        }
        else
        {
            return $this->goHome();
        }
    }
	
	public function actionMapdrops()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if($user_id)
        {
			$baseUrl = $_POST['baseUrl'];
			$tripid = $_POST['place'];
			$which = $_POST['which'];
			$stops = Trip::getTripDetails($tripid);
			return $this->render('mapdrops',array('user_id' => $user_id,'baseUrl' => $baseUrl,'tripid' => $tripid,'which' => $which,'stops'=>$stops));
        }
        else
        {
            return $this->goHome();
        }
    }

	public function actionAddtrip()
    {

       // $_POST = array("afterstop" => "BEFORE--Gondal, Gujarat, India", "trip_name" => "Five", "addtripdate" => "02-09-2017", "startfrom" => "rajkot", "nextstop" => "", "trip_summary" => "", "mapline" => "lines", "tripcolor" => "bluedot", "privacy" => "Public", "type" => "stop", "tripid" => "59aab3049fb00f200f00002b", "triptype" => "new");


        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if($user_id)
        {
          	$date = time();
			$tripid = $_POST['tripid'];
			$triptype = $_POST['triptype'];
			if($triptype == 'new')
			{
				$type = $_POST['type'];
				$trip_name = ucfirst($_POST['trip_name']);
				$addtripdate = $_POST['addtripdate'];
				$startfrom = $_POST['startfrom'];
				$nextstop = $_POST['nextstop'];
				$trip_summary = ucfirst($_POST['trip_summary']);
				$mapline = $_POST['mapline'];
				$tripcolor = $_POST['tripcolor'];
				$privacy = $_POST['privacy'];
				if($tripid == 'empty')
				{
					$trip = new Trip();
					$trip->user_id = $user_id;
					$trip->created_date = "$date";
					$trip->status = '0';
				}
				else
				{
                    $trip = Trip::find()->where(['_id' => (string)$tripid])->andWhere(['not','flagger', "yes"])->one();
					if(isset($_POST['afterstop']) && $_POST['afterstop'] != '' && $type == 'stop')
					{
                        $afterstop = trim($_POST['afterstop']);
                        $afterstopword = str_replace("AFTER--", "", $afterstop);
                        $afterstopword = str_replace("BEFORE--", "", $afterstopword);


                        $end_to = trim($trip['end_to']);
                        $end_to = explode('**', $end_to);
                        $end_to = array_filter($end_to);
                        if(!empty($end_to)) {
                            $key = array_search($afterstopword, $end_to);
                            if (stripos($afterstop, 'AFTER') === 0) {
                                $position = $key + 1;
                            } else {
                                if($key == 0) {
                                    $position = 0;
                                } else {
                                    $position = $key - 1;
                                }
                            }
                            array_splice($end_to, $position, 0, $nextstop);  
                            $nextstop = implode("**", $end_to);
                        }
					}
					else
					{
						$nextstop = $trip['end_to'];
					}
				}

				$trip->updated_date = "$date";
				$trip->trip_name = $trip_name;
				$trip->trip_summary = $trip_summary;
				$trip->tripcolor = $tripcolor;
				$trip->mapline = $mapline;
				$trip->trip_start_date = $addtripdate;
				$trip->start_from = $startfrom;
				$trip->end_to = $nextstop;
				$trip->privacy = $privacy;
                if($tripid == 'empty')
                {
					$trip->insert();
					$tripid = $trip->_id;
				}
				else
				{
					$trip->update();
				}
				if($type == 'note')
				{
					$tripnote = new TripNotes();
					$tripnote->user_id = $user_id;
					$tripnote->created_date = "$date";
					$tripnote->updated_date = "$date";
					$tripnote->status = '0';
					$tripnote->notetitle = ucfirst($_POST['notetitle']);
					$tripnote->notetext = ucfirst($_POST['notetext']);
					$tripnote->tripid = "$tripid";
					$tripnote->insert();
				}
			}
			if($triptype == 'view')
			{
				$tripnote = new TripNotes();
				$tripnote->user_id = $user_id;
				$tripnote->created_date = "$date";
				$tripnote->updated_date = "$date";
				$tripnote->status = '0';
				$tripnote->notetitle = ucfirst($_POST['notetitle']);
				$tripnote->notetext = ucfirst($_POST['notetext']);
				$tripnote->tripid = "$tripid";
				$tripnote->insert();
			}
			return $tripid;
        }
        else
        {
            return $this->goHome();
        }
    }
	
	public function actionEmail()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if($user_id)
        {
			$trips = Trip::getMyTripCount($user_id);
			if($trips == 0)
			{
				$data['msg'] = 'fail';
			}
			else
			{
				$which = $_POST['which'];
				if($which == 'empty')
				{
					$this->actionSendMailTrips($which);
					$data['msg'] = 'success';
				}
				else
				{
					$trip = Trip::getTripDetails($which);
					if($trip)
					{
						$this->actionSendMailSpecificTrip($which);
						$data['msg'] = 'success';
					}
					else
					{
						$data['msg'] = 'fail';
					}
				}
			}
			return json_encode($data);
        }
        else
        {
            return $this->goHome();
        }
    }
	
	public function actionSendMailTrips($which) 
    {
		$session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if($user_id)
        {
			$trips = Trip::getMyTripCount($user_id);
			if($trips == 0)
			{
				$data['msg'] = 'fail';
			}
			else
			{
				$email = $this->getuserdata($user_id,'email');
				$fname = $this->getuserdata($user_id,'fname');
				//$getcss = $this->actionGetmailcss($which);
				$getcss = '@import url("http://iaminjapan.com/frontend/web/assets/baf1a2d0/css/email-trip.css")';
				$triplink = "http://iaminjapan.com/frontend/web/index.php?r=trip";

				$tripcontent = '';
				$trips = Trip::getMyTrips($user_id);
				foreach($trips as $trip)
				{
					$tripid = $trip['_id'];
					$trip_name = $trip['trip_name'];
					$trip_start_date = $trip['trip_start_date'];
					$start_from = $trip['start_from'];
					$trip_summary = $trip['trip_summary'];
					$stops = explode('**',$trip['end_to'],-1);
					$notescount = TripNotes::getNotesCount($tripid);
					
					$tripcontent .= "<li style='float: left;width: 100%;padding: 20px 20px 0;padding-top:0;box-sizing: border-box;margin:0;'>
						<div class='tripitem' style='float: left;width: 100%;position: relative;'>
							<div class='tripicon' style='position: relative;width:20px;float:left;font-size: 17px;'>
								<img src='http://iaminjapan.com/frontend/web/images/tripicon.png' style='display:inline-block;'/>
							</div>
							<div style='float:right;width:525px;'>
								<h6 style='margin: 0;font-weight:normal;color: #464646;padding-right: 60px;font-size: 14px;font-weight:500;'>
									".$trip_name."
								</h6>
								<div class='starttrip' style='float: left;width: 100%;color: #999;font-size: 13px;line-height: 30px;margin: 0 0 20px;'>
									Trip starts on - <span style='color: #333;line-height: 30px;margin-right: 10px;font-size: 13px;'>".$trip_start_date."</span>
								</div>
								<div class='starttrip' style='float: left;width: 100%;color: #999;font-size: 13px;line-height: 30px;margin: 0 0 20px;'>
									Trip starts on - <span style='color: #333;line-height: 30px;margin-right: 10px;font-size: 13px;'>".$start_from."</span>
								</div>";
							if(isset($trip_summary) && !empty($trip_summary)){
							$tripcontent .= "<div class='drow' style='clear: both;float: left;width: 100%;margin: 0 0 20px;'>
								<label style='color: #313131;font-weight:500;font-size: 14px;margin: 0 0 10px;display:inline-block;'>Your trip summery</label>
								<p style='font-size: 13px;color: #999;margin: 0 0 10px;'>".$trip_summary."</p>
								<span class='notetext' style='color: #dbbb24;font-size:14px;'>".$notescount." trip notes added</span>
							</div>";
							}
						$tripcontent .= "</div></div>
						<ul class='tripstops-list' style='padding: 10px 0 0;margin: 0;float: left;width: 100%;box-sizing:border-box;list-style:none;border-bottom: 1px solid #ddd;'>";
							$i = 1; foreach ($stops as $name) {
							$tripcontent .= "<li style='float: left;width: 100%;margin: 0 0 25px;'>
								<div class='tripstop' style='width: 100%;float: left;position: relative;'>
									<div class='title' style='width:100%;float: left;'>
										<div class='dest-col' style='width: 33.33%;float: left;position: relative;'>
											<span class='numbering' style='width: 18px;height: 18px;text-align: center;font-size: 11px;background: #ddd;line-height: 18px;border-radius: 50%;position: relative;top: 0;left: 0;display:inline-block;'>".$i."</span>
											<h5 style='margin: 0;line-height: 18px;font-size: 14px;font-weight: 500;float: right;width: 155px;'>".$name."</h5>
										</div>
										<div class='date-col' style='width: 33.33%;float: left;position: relative;font-size:13px;color:#999;'>
											Arrives on 22-11-2016
										</div>
										<div class='bm-col'>
											2 Bookmarks
										</div>
									</div>
								</div>
							</li>";
							$i++; }
						$tripcontent .= "</ul>
					</li>";
				}
				try
				{
					$test = Yii::$app->mailer->compose()
						->setFrom(array('csupport@iaminjapan.com' => 'Iaminjapan Team'))
						->setTo($email)
						->setSubject('Iaminjapan Trips')
						->setHtmlBody('<html>
							<head>
								<meta charset="utf-8" />
								<title>I am in Japan</title>
								<style>'.$getcss.'</style>
							</head>
							<body class="bodyclass" style="margin:0;padding:0 0 40px;background:#fff;float: left;width: 100%;">
								<div  class="maindiv" style="color: #353535; float:left; font-size: 13px;width:100%;text-align:center;padding:40px 0 0;box-sizing:border-box;">
									<div class="main-wrapper" style="width:600px;display:inline-block;box-sizing:border-box;">
										<img src="http://iaminjapan.com/frontend/web/images/black-logo.png" class="logo" style="margin:0 0 10px;width:130px;float:left;"/>
										<div class="clear" style="clear:both;"></div>
										<div class="box" style="border:1px solid #ddd;margin:0 0 10px;">
											<div class="box-wrapper" style="background:#fff;padding:20px 0;border-top:10px solid #333;text-align:left;">
												<div class="rowcls" style="color: #333;font-size: 13px;margin: 0 0 20px;padding:0 20px;display:inline-block;width:100%;box-sizing:border-box;">Hello '.$fname.',</div>
												<div class="rowcls" style="color: #333;font-size: 13px;margin: 0 0 20px;padding:0 20px;display:inline-block;width:100%;box-sizing:border-box;">Thank you for considering us for your trip plan. Please find your trip plans below:</div>
												<div class="trip-holder" style="padding:20px 0;display:inline-block;width:100%;box-sizing:border-box;">
													<ul class="triplist" style="margin: 0;float: left;width: 100%;padding:0;box-sizing:border-box;list-style:none;">'.$tripcontent.'</ul>
												</div>
												<div class="btmrow" style="color: #333;font-size: 13px;padding:0 20px;display:inline-block;width:100%;box-sizing:border-box;">Thank you for using Iaminjapan!</div>
												<div class="btmrow" style="color: #333;font-size: 13px;padding:0 20px;display:inline-block;width:100%;box-sizing:border-box;">The Iaminjapan Team</div>
											</div>
										</div>
										<div class="clear" style="clear:both;"></div>
										<div class="bottom" style="width:600px;display:inline-block;font-size:11px;">
											<div class="copyright" style="color: #777;text-align: left;">&copy;  www.iaminjapan.com All rights reserved.</div>
											<div class="support" style="text-align: left;width: 100%;margin:5px  0 0;color:#777;">For support, you can reach us directly at <a href="csupport@iaminjapan.com" style="color:#4083BF">csupport@iaminjapan.com</a></div>
									   </div>
									</div>
								</div>
							</body>
						</html>')
					->send();
				}
				catch (ErrorException $e)
				{
					return $e->getMessage();
				}
			}
		}
        else
        {
            return $this->goHome();
        }
    }
	
	public function actionSendMailSpecificTrip($tripid) 
    {
		$session = Yii::$app->session;
		$user_id = (string)$session->get('user_id');
		if($user_id)
        {
			$trip = Trip::getTripDetails($tripid);
			$tripname = $trip['trip_name'];
			if($trip)
			{
				$email = $this->getuserdata($user_id,'email');
				$fname = $this->getuserdata($user_id,'fname');
				//$getcss = $this->actionGetmailcss($tripid);
				$triplink = "http://iaminjapan.com/frontend/web/index.php?r=trip";
				$tripcontent = '';
				$trip = Trip::getTripDetails($tripid);
				$trip_name = $trip['trip_name'];
				$trip_start_date = $trip['trip_start_date'];
				$start_from = $trip['start_from'];
				$trip_summary = $trip['trip_summary'];
				$tripcolor = str_replace('dot','',$trip['tripcolor']);
				$mapline = $trip['mapline'];
				$stops = explode('**',$trip['end_to'],-1);
				$notescount = TripNotes::getNotesCount($tripid);
				$notes = TripNotes::getTripNotes($tripid);

				$tripcontent .= "<ul class='triplist' style='margin: 0;float: left;width: 100%;padding:0;box-sizing:border-box;list-style:none;'>
					<li style='float: left;width: 100%;padding: 20px 20px 0;border-bottom: 1px solid #ddd;padding-top:0;box-sizing: border-box;margin:0;'>
						<div class='tripitem' style='float: left;width: 100%;position: relative;'>
							<div class='tripicon' style='position: relative;width:20px;float:left;font-size: 17px;'>
								<img src='http://iaminjapan.com/frontend/web/images/tripicon.png' style='display:inline-block;'/>
							</div>
							<div style='float:right;width:525px;'>
								<h6 style='margin: 0;font-weight:normal;color: #464646;padding-right: 60px;font-size: 14px;font-weight:500;'>												
									".$trip_name."
								</h6>
								<div class='starttrip' style='float: left;width: 100%;color: #999;font-size: 13px;line-height: 30px;margin: 0 0 20px;'>
									Trip starts on - <span style='color: #333;line-height: 30px;margin-right: 10px;font-size: 13px;'>".$trip_start_date."</span>
								</div>
								<div class='starttrip' style='float: left;width: 100%;color: #999;font-size: 13px;line-height: 30px;margin: 0 0 20px;'>
									Trip starts from - <span style='color: #333;line-height: 30px;margin-right: 10px;font-size: 13px;'>".$start_from."</span>
								</div>";
							if(isset($trip_summary) && !empty($trip_summary)){
							$tripcontent .= "<div class='drow' style='clear: both;float: left;width: 100%;margin: 0 0 20px;'>
								<label style='color: #313131;font-weight:500;font-size: 14px;margin: 0 0 10px;display:inline-block;'>Your trip summery</label>
								<p style='font-size: 13px;color: #999;margin: 0 0 10px;'>".$trip_summary."</p>
								<span style='color: #dbbb24;font-size:14px;'>".$notescount." trip notes added</span>
							</div>";
							}
						$tripcontent .= "</div></div>
						<ul class='tripstops-list' style='padding: 10px 0 0;margin: 0;float: left;width: 100%;box-sizing:border-box;list-style:none;'>";
							$i = 1; foreach ($stops as $name) {
							$tripcontent .= "<li style='float: left;width: 100%;margin: 0 0 25px;'>
								<div class='tripstop' style='width: 100%;float: left;position: relative;'>
									<div class='title' style='width:100%;float: left;'>
										<div class='dest-col' style='width: 33.33%;float: left;position: relative;'>
											<span class='numbering' style='width: 18px;height: 18px;text-align: center;font-size: 11px;background: #ddd;line-height: 18px;border-radius: 50%;position: relative;top: 0;left: 0;display:inline-block;'>".$i."</span>
											<h5 style='margin: 0;line-height: 18px;font-size: 14px;font-weight: 500;float: right;width: 155px;'>".$name."</h5>
										</div>
										<div class='date-col' style='width: 33.33%;float: left;position: relative;font-size:13px;color:#999;'>
											Arrives on 30-11-2016
										</div>
										<div style='width: 33.33%;float: right;position: relative;font-size:13px;color:#3399cc;text-align:right;'>
											5 Bookmarks
										</div>
									</div>
									<div class='bm-detail' style='margin:20px 0 0;width:100%;float: left;padding:0 0 0 30px;'>
										<span class='tspan' style='width:auto;color:#3399cc;font-size:13px;margin:0 0 10px;float:left;'>Bookmarks</span>
										<div class='strow' style='width:100%;float:left;margin:3px 0;'>
											<span style='width:29%;color:#999;font-size:13px;display:inline-block;'>XYZ Hotel</span>
											<span style='width:29%;color:#999;font-size:13px;display:inline-block;'>999-999-999</span>
										</div>
										<div class='strow' style='width:100%;float:left;margin:3px 0;'>
											<span style='width:29%;color:#999;font-size:13px;display:inline-block;'>XYZ Hotel</span>
											<span style='width:29%;color:#999;font-size:13px;display:inline-block;'>999-999-999</span>
										</div>
									</div>
								</div>
							</li>";
							$i++; }
						$tripcontent .= "</ul>
					</li>
				</ul>
				";
				if($notescount != 0)
				{
					$tripcontent .= "<div class='notedetails' style='width:100%;float:left;padding:10px 20px 0;box-sizing: border-box;'>
						<span class='notetext' style='font-size:15px;'>Trip Notes</span>
						<ul class='notelist' style='float:left;width:100%;padding:0;list-style:none;'>";
							foreach($notes as $note){
								$notetitle = $note['notetitle'];
								$notetext = $note['notetext'];
								$tripcontent .= "<li style='float:left;width:100%;margin:10px 0;'>
									<h5 style='font-weight:500;font-size:13px;margin:0 0 10px;'>".$notetitle."</h5>
									<p style='padding:10px 15px;border-radius:3px;border:1px solid #f3e89d;width:100%;margin:0;color:#999;box-sizing: border-box;'>".$notetext."</p>
								</li>";
							}
						$tripcontent .= "</ul>
					</div>";
				}
				try
				{
					$test = Yii::$app->mailer->compose()
						->setFrom(array('csupport@iaminjapan.com' => 'Iaminjapan Team'))
						->setTo($email)
						->setSubject('Iaminjapan Trip - '.$tripname.'')
						->setHtmlBody('<html>
							<head>
								<meta charset="utf-8" />
								<title>Iaminjapan</title>								
							</head>
							<body class="bodyclass" style="margin:0;padding:0;background:#fff;float: left;width: 100%;">
								<div  class="maindiv" style="color: #353535; float:left; font-size: 13px;width:100%;text-align:center;padding:40px 0 0;box-sizing:border-box;">
									<div class="main-wrapper" style="width:600px;display:inline-block;box-sizing:border-box;">
										<img src="http://iaminjapan.com/frontend/web/images/black-logo.png" class="logo" style="margin:0 0 10px;width:130px;float:left;"/>
										<div class="clear" style="clear:both;"></div>
										<div class="box" style="border:1px solid #ddd;margin:0 0 10px;">
											<div class="box-wrapper" style="background:#fff;padding:20px 0;border-top:10px solid #333;text-align:left;">
												<div class="rowcls" style="color: #333;font-size: 13px;margin: 0 0 20px;padding:0 20px;display:inline-block;width:100%;box-sizing:border-box;">Hello '.$fname.',</div>
												<div class="rowcls" style="color: #333;font-size: 13px;margin: 0 0 20px;padding:0 20px;display:inline-block;width:100%;box-sizing:border-box;">Thank you for considering us for your trip plan. Please find your trip plan for '.$tripname.' below:</div>
												<div class="trip-holder" style="padding:20px 0 0;display:inline-block;width:100%;box-sizing:border-box;">'.$tripcontent.'</div>
												<div class="btmrow" style="color: #333;font-size: 13px;padding:0 20px;display:inline-block;width:100%;box-sizing:border-box;">Thank you for using Iaminjapan!</div>
												<div class="btmrow" style="color: #333;font-size: 13px;padding:0 20px;display:inline-block;width:100%;box-sizing:border-box;">The Iaminjapan Team</div>
											</div>
										</div>
										<div class="clear" style="clear:both;"></div>
										<div class="bottom" style="width:600px;display:inline-block;font-size:11px;">
											<div class="copyright" style="color: #777;text-align: left;">&copy;  www.iaminjapan.com All rights reserved.</div>
											<div class="support" style="text-align: left;width: 100%;margin:5px  0 0;color:#777;">For support, you can reach us directly at <a href="csupport@iaminjapan.com" style="color:#4083BF">csupport@iaminjapan.com</a></div>
									   </div>
									</div>
								</div>
							</body>
						</html>')
					->send();
				}
				catch (ErrorException $e)
				{
					return $e->getMessage();
				}
			}
			else
			{
				$data['msg'] = 'fail';
			}
		}
        else
        {
            return $this->goHome();
        }
    }
	
	public function actionPrinttrip()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if($user_id)
        {
			$which = $_POST['which'];
			return $this->render('printcontent',array('user_id' => $user_id,'which' => $which));
        }
        else
        {
            return $this->goHome();
        }
    }
}
?>