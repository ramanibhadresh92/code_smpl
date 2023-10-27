<?php 
namespace frontend\models;
use Yii;
use yii\helpers\ArrayHelper;
use yii\mongodb\ActiveRecord;
use yii\mongodb\Query;
use yii\db\ActiveQuery;
use yii\db\Expression;
use frontend\models\LoginForm;
use yii\helpers\Url;
use backend\models\Googlekey;

class Connect extends ActiveRecord 
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;
	
    public static function collectionName()
    {
        return 'friend';
    }

     public function attributes()
    {
        return ['_id', 'from_id', 'to_id', 'status', 'action_user_id','created_date','updated_date'];
    }
    
    public function getUserdata()
    {
        return $this->hasOne(UserForm::className(), ['_id' => 'from_id']);
    }
       
    public function scenarios()
    {
        $scenarios = parent::scenarios();     
        return $scenarios;
    }
   
    public function getuserConnections($user_id)
    {
      $user_Connections =  Connect::find()->with('userdata')->Where(['status'=>'1'])->andWhere(['to_id'=> "$user_id"])->asarray()->all();
      return $user_Connections;
    }

    public function getuserConnectionsIds($user_id)
    {   
      $ids = ArrayHelper::map(Connect::find()->where(['to_id' => (string)$user_id, 'status' => '1'])->all(), 'from_id', 'to_id');
  		if(!empty($ids)) {
        $connectids = array_keys($ids);
        return $connectids;
      }  
  	}
	
	   public function getuserConnectionscount($user_id)
    {
		
		$user_Connections = array();
		$user_Connections1 =  Connect::find()->with('userdata')->Where(['status'=>'1'])->andWhere(['to_id'=> "$user_id"])->all();
		
		foreach($user_Connections1 as $user_Connections2)
		{
			$result = UserForm::find()->where(['_id' => $user_Connections2['from_id']])->one();
			if(empty($result))
			{
				continue;
			}
			$user_Connections[] = $user_Connections2; 	
		}
		return $user_Connections;
	}

     public function getuserConnectionsWithLike($user_id, $like)
     {
        $user_Connections =  Connect::find()
            ->with('userdata')
            ->andWhere(['to_id'=> "$user_id"])
            ->andWhere(['status'=>'1'])
            ->all();
            
            return $user_Connections;
     }
   
    public function connectPendingRequests()
    {
		$session = Yii::$app->session;
		$uid = (string)$session->get('user_id');
		$pending_request =  Connect::find()->with('userdata')->Where(['not','action_user_id', "$uid"])->andwhere(['status'=>'0'])->andwhere(['to_id'=>"$uid"])->andWhere(['not','from_id', "$uid"])->all();
        return $pending_request;
    }
	
	public function connectPendingRequestsAPI($uid)
    {
        $pending_request =  Connect::find()->with('userdata')->Where(['not','action_user_id', "$uid"])->andwhere(['status'=>'0'])->andwhere(['to_id'=>"$uid"])->andWhere(['not','from_id', "$uid"])->all();
        return $pending_request;
    }
   
    public function connectRequestbadge()
     {
            $session = Yii::$app->session;
            $uid = (string)$session->get('user_id');
            $array = [$uid];  
            $result_requests = Connect::find()->Where(['not','action_user_id', "$uid"])->andwhere(['status'=>'0'])->andwhere(['to_id'=>"$uid"])->andWhere(['not','from_id', "$uid"])->all();
         
          $count = count($result_requests);
        
          return $count;  
     }
      
     public function userlistFirstfive($uid)
     {
		$array = [$uid];
		$exist_ids = $pending_ids = array();   
		$requestexists =  Connect::find()->where(['from_id'=>"$uid"])->all();
		if(!empty($requestexists) && count($requestexists)>0)
		{
			foreach($requestexists AS $requestexist)
			{
				$exist_ids[] = $requestexist['from_id'];
			}
		}
		$requestpendings =  Connect::find()->where(['to_id'=>"$uid"])->all();
		 if(!empty($requestpendings) && count($requestpendings)>0)
		{
			foreach($requestpendings AS $requestpending)
			{
				$pending_ids[] = $requestpending['from_id'];
			}
		}
                 
		$array_final = array_unique (array_merge ($exist_ids, $pending_ids,$array));
		$result_Connections = LoginForm::find()->where(['not in','_id',$array_final])->andwhere(['status'=>'1'])->orderBy(['ontop' => SORT_DESC,
        'rand()' => SORT_DESC,])->limit(3)->all();
		$count = count($result_Connections);
		return $result_Connections;      
     }
	 
    public function userlist()
    {
      $session = Yii::$app->session;
      $uid = (string)$session->get('user_id');
      $connectids = Connect::getconnectids($uid);
      $connectids[] = $uid;

      $result_Connections = LoginForm::find()->where(['not in', '_id', $connectids])->andwhere(['status'=>'1'])->orderBy(['_id'=>SORT_DESC])->all();
      return $result_Connections;
    }
	 
	   public function getmyconnect()
	   {

      $session = Yii::$app->session;
      $uid = (string)$session->get('user_id');
      $newData = array();
      $result_Connections = Connect::find()->where(['to_id' => $uid, 'status' => '1'])->orwhere(['from_id' => $uid, 'status' => '1'])->asArray()->all();
      if(!empty($result_Connections)) {
        foreach($result_Connections as $frd) {
          $id = $frd['from_id'];
          if($id == $uid) {
            $id = $frd['to_id'];
          }
          if($id != $uid) {
            $fnm = UserForm::find()->select(['fname', 'lname', 'fullname'])->where([(string)'_id' => $id])->asarray()->one();
            if(!empty($fnm)) {
              $fullname = $fnm['fullname'];
              $newData[$id] = $fullname;
            }
          }
        }
      }
      
      return $newData;
			exit;
	   }

    public function getmyconnectcloseaccount()
    {
      $session = Yii::$app->session;
      $uid = (string)$session->get('user_id');
      $newData = array();
      $result_Connections = Connect::find()->where(['to_id' => $uid, 'status' => '1'])->orwhere(['from_id' => $uid, 'status' => '1'])->asArray()->all();
      if(!empty($result_Connections)) {
        foreach($result_Connections as $frd) {
          $id = $frd['from_id'];
          if($id == $uid) {
            $id = $frd['to_id'];
          }
          if($id != $uid) {
            if(!in_array($id, $alreaystored)) {
              $alreaystored[] = $id;
              $fnm = UserForm::find()->select(['fname', 'lname', 'fullname', 'city', 'country'])->where([(string)'_id' => $id])->asarray()->one();
              if(!empty($fnm)) {
                $thumb = $this->context->getimage($id, 'thumb');
                $fnm['thumb'] = $thumb; 
                $newData[] = $fnm;
              }
            }
          }
        }
      }
      
      return $newData;
      exit;
    }
     
     public function searchconnect()
     {

		$session = Yii::$app->session;
		$email = $session->get('name');

		$result_Connections = LoginForm::find()->where(['like','email',$email])
				->orwhere(['like','fname',$email])
				->orwhere(['like','lname',$email])
				->orwhere(['like','photo',$email])
				->orwhere(['like','phone',$email])
				->orwhere(['like','fullname',$email])
				->andwhere(['status'=>'1'])->orderBy(['_id'=>SORT_DESC])->all();


		return $result_Connections;
        
     }
	 
     function userlistsuggetions($sug)
     {
        if (\Yii::$app->user->isGuest)
        {
            return Yii::$app->GenCls->goHome();
        }
        else
        {
            return LoginForm::find()->where(['like','username',$sug,false])->andwhere(['status'=>'1'])->orderBy(['_id'=>SORT_DESC])->limit(10)->all();
        }
     }
     
     
     public function getMutualConnect($id)
     {
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
    
        $Connections_of_a = Connect::find()->where(['from_id'=>"$uid",'status'=>'1'])->all();
        $Connections_of_b  = Connect::find()->where(['from_id'=>"$id",'status'=>'1'])->all();
       
        $arr_a = $arr_b = array();
        foreach($Connections_of_a as $t)
        {
            $arr_a[$t->to_id] = $t->attributes;
        }

        foreach($Connections_of_b as $t1)
        {
            $arr_b[$t1->to_id] = $t1->attributes;
        }
        $result_mutual = array_intersect_key($arr_a,$arr_b);
        $rmData = array();

        if(!empty($result_mutual)) {
          foreach ($result_mutual as $rm) {
            $id = $rm['from_id'];
            if($id == $id) {
              $id = $rm['to_id'];
            }

            $usrDta = LoginForm::find()->where([(string)'_id' => $id])->andwhere(['status'=>'1'])->asArray()->one();
            if(!empty($usrDta)) {
              $rm['otherid'] = $id;
              $rm['userdata'] = $usrDta;
              $rmData[] = $rm;
            }
          }
        }
        
        return $rmData;
     }

     public function getMutualConnectIds($id)
     {
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
    
        $Connections_of_a = Connect::find()->where(['from_id'=>"$uid",'status'=>'1'])->all();
        $Connections_of_b  = Connect::find()->where(['from_id'=>"$id",'status'=>'1'])->all();
       
        $arr_a = $arr_b = array();
        foreach($Connections_of_a as $t)
        {
            $arr_a[$t->to_id] = $t->attributes;
        }

        foreach($Connections_of_b as $t1)
        {
            $arr_b[$t1->to_id] = $t1->attributes;
        }
        $result_mutual = array_intersect_key($arr_a,$arr_b);

        $ids = array_keys($result_mutual);

        return $ids;
     }

     public function mutualconnectcount($id)
     {
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
		
        $Connections_of_a = Connect::find()->where(['from_id'=>"$uid",'status'=>'1'])->all();
        $Connections_of_b  = Connect::find()->where(['from_id'=>"$id",'status'=>'1'])->all();
       
        $arr_a = $arr_b = array();
        foreach($Connections_of_a as $t)
        {
            $arr_a[$t->to_id] = $t->attributes;
        }

        foreach($Connections_of_b as $t1)
        {
            $arr_b[$t1->to_id] = $t1->attributes;
        }
        $result_mutual = array_intersect_key($arr_a,$arr_b);
        $count = count($result_mutual);
        return $count;
     }
	 
	public function mutualconnectcountAPI($id,$uid)
	{
		$Connections_of_a = Connect::find()->where(['from_id'=>"$uid",'status'=>'1'])->all();
		$Connections_of_b  = Connect::find()->where(['from_id'=>"$id",'status'=>'1'])->all();
		$arr_a = $arr_b = array();
		foreach($Connections_of_a as $t)
		{
			$arr_a[$t->to_id] = $t->attributes;
		}

		foreach($Connections_of_b as $t1)
		{
			$arr_b[$t1->to_id] = $t1->attributes;
		}
		$result_mutual = array_intersect_key($arr_a,$arr_b);
		$count = count($result_mutual);
		return $count;
	}
     
     public function requestexists($id)
     {
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
        
        $result_exists = Connect::find()->where(['from_id'=>"$id",'to_id'=>"$uid"])->all();
        $count = count($result_exists);
        return $count;
     }
      
     public function requestalreadysend($id)
     {
        $session = Yii::$app->session;
        $uid = (string)$session->get('user_id');
  
        $result_exists = Connect::find()->where(['from_id'=>"$uid",'to_id'=>"$id"])->all();
        $count = count($result_exists);

        return $count;
     }
   
    public function getConnectionsCity($uid)
     {
        $user_Connections =  Connect::find()->with('userdata')->Where(['status'=>'1'])->andWhere(['to_id'=> "$uid"])->all();
        $cities = '';
        foreach($user_Connections AS $user_connect)
        {
			$city = Personalinfo::getCity($user_connect['userdata']['_id']);
			$cities .=  "'".$user_connect['userdata']['city']."',";
        }
        return $cities;   
     }
   
    public function getConnectionsNames($uid)
     {
        $user_Connections =  Connect::find()->with('userdata')->Where(['status'=>'1'])->andWhere(['to_id'=> "$uid"])->all();
        $names = '';
        foreach($user_Connections AS $user_connect)
        {
           $names .=  "'".$user_connect['userdata']['fname'].' '.$user_connect['userdata']['lname']."',";
        }
		return $names;
     }
   
    public function getConnectionsLinks($uid)
	{
		$user_Connections =  Connect::find()->with('userdata')->Where(['status'=>'1'])->andWhere(['to_id'=> "$uid"])->all();
		$names = '';
		foreach($user_Connections AS $user_connect)
		{
		   $names .=  "'".$user_connect['userdata']['_id']."',";
		}
		return $names;
	}
	  
    public function getConnectionsImages($uid,$from)
     {
        $user_Connections =  Connect::find()->with('userdata')->Where(['status'=>'1'])->andWhere(['to_id'=> "$uid"])->all();
        $images = '';
        foreach($user_Connections AS $user_connect)
        {
            if($from == 'wallajax')
            {
                $mapdp = $this->context->getimage($user_connect['userdata']['_id'],'thumb');
            }
            else
            {
                $mapdp = $this->context->getimage($user_connect['userdata']['_id'],'thumb');
            }
           $images .=  "'".$mapdp."',";
        }
        return $images;  
     }
	 
     function Connections_or_tobe_Connections($to_id,$from_id)
     {
        $is_Connections =  Connect::find()->with('userdata')->Where(['from_id'=>"$from_id",'to_id'=> "$to_id"])->orWhere(['from_id'=> "$to_id",'to_id'=>"$from_id"])->all();
        
        if(count($is_Connections)>0)
            return true;
        else
            return false;
     }
     
     public function getConnectionsCityValue($uid)
     {
        $user_Connections =  Connect::find()->with('userdata')->Where(['status'=>'1'])->andWhere(['to_id'=> "$uid"])->all();
        $cities = '';
        foreach($user_Connections AS $user_connect)
        {
            $prepAddr = str_replace(' ','+',$user_connect['userdata']['city']);
            $prepAddr = str_replace("'",'',$prepAddr);
			$citylat = $user_connect['userdata']['citylat'];
		    $citylong = $user_connect['userdata']['citylong'];
			if(isset($citylat) && !empty($citylat) && isset($citylong) && !empty($citylong))
			{
				$latitude = $citylat;
				$longitude = $citylong;
				$cities .=  "['".$prepAddr."',".$latitude.",".$longitude."],";
			}
			if(isset($prepAddr) && !empty($prepAddr) && !isset($citylat) && empty($citylat) && !isset($citylong) && empty($citylong))
            {
				$connected = @fsockopen("www.google.com", 80); //website, port  (try 80 or 443)
				if($connected)
				{
          $GApiKeyL = $GApiKeyP = Googlekey::getkey();

					$geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?key='.$GApiKeyL.'&address='.$prepAddr.'&sensor=false');
					$output = json_decode($geocode);
					if(!empty($output)){
						$latitude = @$output->results[0]->geometry->location->lat;
						$longitude = @$output->results[0]->geometry->location->lng;	
					}
					else
					{
						$latitude = '';
						$longitude = '';
					}
					
					$cities .=  "['".$prepAddr."',".$latitude.",".$longitude."],";
				}
				else
				{
					$cities = '[],';
				}
            }
        }
        return substr($cities,0,-1);   
     }
     
     public function getConnectionsMapDetails($uid)
     {
        $user_Connections =  Connect::find()->with('userdata')->Where(['status'=>'1'])->andWhere(['to_id'=> "$uid"])->all();
        $fredetails = '';
        foreach($user_Connections AS $user_connect)
        {
            $freid = $user_connect['userdata']['_id'];
            $frename = $user_connect['userdata']['fullname'];
			$prepAddr = str_replace(' ','+',$user_connect['userdata']['city']);
            $mapdp = $this->context->getimage($freid,'thumb');
            $frelink = Url::to(['userwall/index', 'id' => "$freid"]);
			if(isset($prepAddr) && !empty($prepAddr))
			{
				$connect_content = '\'<img height="18" width="18" src="'.$mapdp.'"/> <a href="'.$frelink.'">'.$frename.'</a>\'';
				$fredetails .=  "[$connect_content],";
			}
        }
        return substr($fredetails,0,-1);   
    }
 
    public function isConnect($user_id, $id) {
        $isConnect = Connect::find()->where(['from_id' => $user_id, 'to_id' => $id])->orwhere(['from_id' => $id, 'to_id' => $user_id])->one();
        if(!empty($isConnect)) {
            return $isConnect;
        }
        return false;
    }
 
    public function connectrequestupdate($user_id, $id) {
        $data = Connect::find()->where(['from_id' => $user_id, 'to_id' => $id])->orwhere(['from_id' => $id, 'to_id' => $user_id])->one();
        if(!empty($data)) {
            $data->delete();
            return 'AF';
        } else {
            $date = time();
            $data = new Connect();
            $data->from_id = $user_id;
            $data->to_id = $id;
            $data->action_user_id = $user_id;
            $data->status = '0';
            $data->created_date = $date;
            $data->updated_date = $date;
            $data->insert();
            return 'CFR';
        }
    }

    public function getconnectids($user_id) {
        $data = Connect::find()->where(['from_id' => $user_id])->orwhere(['to_id' => $user_id])->asarray()->all();
        $ids = array();
        if(!empty($data)) {
            foreach ($data as $key => $value) {
                if($value['from_id'] == $user_id) {
                    $ids[] = $value['to_id'];
                } else {
                    $ids[] = $value['from_id'];
                }
            }
        }
        return array_unique($ids);
    }

    public function AddUserForTag($user_id, $start) {
        $getconnectids = Connect::getconnectids($user_id);
        $results = array();
        if(!empty($getconnectids)) {
          $results = UserForm::find()->select(['fullname'])->where(['in', '_id', $getconnectids])->orderBy(['created_at'=>SORT_DESC])->asarray()->all();
        }
        return json_encode($results, true);
    }

    public function AddUserForTagSearch($user_id, $key, $start, $isall=false) {
        $getconnectids = Connect::getconnectids($user_id);
        $results = array();

        if($isall) {
          $results = UserForm::find()
            ->select(['fname', 'lname', 'fullname'])
            ->where(['like','fullname',$key])
            ->orwhere(['like','fname',$key])
            ->orwhere(['like','lname',$key])
            ->andwhere(['status'=>'1'])->orderBy(['_id'=>SORT_DESC])
            ->asarray()->all();
        } else if(!empty($getconnectids)) {
          if($key != '') {
            $results = UserForm::find()
            ->select(['fname', 'lname', 'fullname'])
            ->where(['like','fullname',$key])
            ->orwhere(['like','fname',$key])
            ->orwhere(['like','lname',$key])
            ->andwhere(['in', '_id', $getconnectids])
            ->andwhere(['status'=>'1'])->orderBy(['_id'=>SORT_DESC])
            ->asarray()->all();
          } else {
            $results = UserForm::find()->select(['fullname'])->where(['in', '_id', $getconnectids])->orderBy(['created_at'=>SORT_DESC])->asarray()->all(); 
          }
        }

        return json_encode($results, true);
    }

    public function frdandfrdoffrdIDS() {
      $session = Yii::$app->session;
      $user_id = (string)$session->get('user_id');
      $result = array();

      if($user_id) {
        $connect = Connect::find()->select(['from_id', 'to_id'])->where(['from_id' => $user_id])->orwhere(['to_id' => $user_id])->asarray()->all();
        
        if(!empty($connect)) {
          foreach ($connect as $key => $value) {
            $tempResult = array(); 
            $id = $value['to_id'];
            if($value['from_id'] == $user_id) {
              $id = $value['to_id'];
            }
            $result[] = $id;

            $subConnect = ArrayHelper::map(Connect::find()->select(['from_id', 'to_id'])->where(['from_id' => $user_id, 'to_id' => $id])->orwhere(['from_id' => $id, 'to_id' => $user_id])->asArray()->all(), 'from_id', 'to_id');
            if(!empty($subConnect)) {
              $T_ = array_keys($subConnect);
              $TT_ = array_values(array_unique($subConnect));

              $T_Merge = array_merge($T_,  $TT_);  
              $result = array_merge($result, $T_Merge); 
            }
          }
        }

        $result = array_values(array_unique($result));
        if (($key = array_search($user_id, $result)) !== false) {
            unset($result[$key]);
        }

        if(!empty($result)) {
          foreach ($result as $resultSingle) {
              $subconnectID = $resultSingle;
              $subconnect = Connect::find()->select(['from_id', 'to_id'])->select(['from_id', 'to_id'])->where(['from_id' => $subconnectID])->orwhere(['to_id' => $subconnectID])->asarray()->all();
      
              if(!empty($subconnect)) { 
                foreach ($subconnect as $s_subconnect) {
                  $s_subconnectID = $s_subconnect['from_id'];
                  if($user_id == $s_subconnectID) {
                    $s_subconnectID = $s_subconnect['to_id'];
                  }
                  $result[] = $s_subconnectID;
                }
              }
          }
        }

        $result = array_values(array_unique($result));

        return $result;
      }
    }

    public function getfrdandfrdoffrd($user_id, $itretion=0) {
      $start = $itretion * 20;
      $end = 20;
      $ids = Connect::frdandfrdoffrdIDS();
      if(!empty($ids)) {
        $idsCount = count($ids);
        if($start >= $idsCount) {
            echo 'FINISH';
        }
        $slice_ids = array_slice($ids, $start, $end);
        if(!empty($slice_ids)) {
          $usrDta = LoginForm::find()->select(['photo', 'thumbnail', 'fullname'])->where(['in', (string)'_id', $slice_ids])->andwhere(['status'=>'1'])->asArray()->all();
          foreach ($usrDta as $singleusrDta) {
            $subfid = (string)$singleusrDta['_id']; 
            $dp = Yii::$app->GenCls->filtergetimage($singleusrDta);
            $fullname = $singleusrDta['fullname'];
            ?>
            <a href="javascript:void(0)" id="msg_<?=$subfid?>" onclick="openMessage(this)" class="add_to_group_container">
              <span class="add_to_group_personprofile imgholder">
                <img src="<?=$dp?>"/>
              </span>
              <div class="add_to_group__personlabel">
                <h6 class="group_person_name" id="checkPerson0"><?=$fullname?></h6>
              </div>          
            </a>
            <?php
          }
        }
      }
    }

    public function getfrdandfrdoffrdsearch($user_id, $searchkey) {
      $usrDta = LoginForm::find()->select(['photo', 'thumbnail', 'fullname', 'fname', 'lname'])->where(['status'=>'1'])->andwhere(['not', (string)'_id', $user_id])->asArray()->all();
      foreach ($usrDta as $singleusrDta) {
        $fullname = $singleusrDta['fullname'];
        $fname = $singleusrDta['fname'];
        $lname = $singleusrDta['lname'];
        if (stripos($fname, $searchkey) === 0) {
        } else if(stripos($lname, $searchkey) === 0) {
        } else if(stripos($fullname, $searchkey) === 0) {
        } else {
          continue;
        }  

        $subfid = (string)$singleusrDta['_id'];
        $dp = Yii::$app->GenCls->filtergetimage($singleusrDta);
        ?>
         <a href="javascript:void(0)" id="msg_<?=$subfid?>" onclick="openMessage(this)" class="add_to_group_container">
          <span class="add_to_group_personprofile imgholder">
            <img src="<?=$dp?>"/>
          </span>
          <div class="add_to_group__personlabel">
            <h6 class="group_person_name" id="checkPerson0"><?=$fullname?></h6>
          </div>          
        </a>
        <?php
      }
    }

    public function getconnectcount($user_id)
    {
      return Connect::find()->with('userdata')->Where(['status'=>'1'])->andWhere(['to_id'=> "$user_id"])->count();
    }

    public function friendPendingRequests()
    {
      $session = Yii::$app->session;
      $uid = $session->get('user_id');
      $pending_request =  Connect::find()->with('userdata')->Where(['not','action_user_id', "$uid"])->andwhere(['status'=>'0'])->andwhere(['to_id'=>"$uid"])->andWhere(['not','from_id', "$uid"])->all();
        return $pending_request;
    }
}
