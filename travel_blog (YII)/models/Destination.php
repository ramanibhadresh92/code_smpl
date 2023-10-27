<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
use frontend\models\Personalinfo;



class Destination extends ActiveRecord
{
    public static function collectionName()
    {
        return 'user_destinations';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'place', 'created_date', 'updated_date', 'type'];
    }
   
    public function getUser()
    {
		return $this->hasOne(LoginForm::className(), ['_id' => 'user_id']);
    }

    public function getUserDestType($user,$place)
    {
		$record = array();
    	$Destination = Destination::find()->where(['user_id' => (string)$user])->asArray()->one();

		if(!empty($Destination)) {
			foreach ($Destination as $key => $value) {
				$p = $value['place'];
				if (stripos($p, $place) !== false) {
				    $record[] = $value;
				}
			}
		}

		return $record;
    }

    public function getDestUsers($place,$type, $user_id)
    {	
    	$record = array();
    	$Destination = Destination::find()->with('user')->where(['type' => (string)$type])->andwhere(['not', 'user_id', (string)$user_id])->orderBy(['updated_date'=>SORT_DESC])->asarray()->all();

		if(!empty($Destination)) {
			foreach ($Destination as $key => $value) {
				$p = $value['place'];
				if (stripos($p, $place) !== false) {
				    $record[] = $value;
				}
			}
		}

		return $record;
    }

    public function getDestUsersCount($place,$type)
    {
    	$record = array();
    	$Destination = Destination::find()->where(['type' => (string)$type])->asarray()->all();

		if(!empty($Destination)) {
			foreach ($Destination as $key => $value) {
				$p = $value['place'];
				if (stripos($p, $place) !== false) {
				    $record[] = $value;
				}
			}
		}

		return count($record);
    }
	
    public function getDestUsersTotal($place)
    {
    	$record = array();
    	$Destination = Destination::find()->with('user')->orderBy(['updated_date'=>SORT_DESC])->all();

		if(!empty($Destination)) {
			foreach ($Destination as $key => $value) {
				$p = $value['place'];
				if (stripos($p, $place) !== false) {
				    $record[] = $value;
				}
			}
		}

		return count($record);
    }

    public function getDestUsersCountTotal($place)
    {
    	$record = array();
    	$Destination = Destination::find()->asArray()->all();

		if(!empty($Destination)) {
			foreach ($Destination as $key => $value) {
				$p = $value['place'];
				if (stripos($p, $place) !== false) {
				    $record[] = $value;
				}
			}
		}

		return count($record);
    }

    public function getAllDestination($user)
    {
		return Destination::find()->with('user')->where(['user_id' => (string)$user])->orderBy(['updated_date'=>SORT_DESC])->all();
    }
	
    public function getAllDestinationType($user,$type)
    {
		return Destination::find()->with('user')->where(['user_id' => (string)$user,'type' => (string)$type])->orderBy(['updated_date'=>SORT_DESC])->all();
    }

    public function getAllDestinationTypeNew($user,$type)
    {
		return Destination::find()->where(['user_id' => $user,'type' => $type])->orderBy(['updated_date'=>SORT_DESC])->all();
    }

    public function getDestinationCount($user)
    {
        return Destination::find()->where(['user_id' => (string)$user])->count();
    }
	
    public function addUserDest($user,$type,$place)
    {
		$place = strtolower($place);
		
		if(isset($user) && !empty($user) && isset($type) && !empty($type) && isset($place) && !empty($place))
		{
			$dest_exist = Destination::find()->where(['user_id' => (string)$user, 'place' => (string)$place, 'type' => $type])->one();
			
			$dest_exist_profile = Personalinfo::find()->where(['user_id' => "$user"])->one();
			$dest_exist_profile2 = strtolower($dest_exist_profile['visited_countries']);
			$place = strtolower($place);
			if(strstr($dest_exist_profile2,(string)$place))
			{
				$inprofile = "true";
			}
			else
			{
				$inprofile = "false";
			}
			$date = time();
			$data = array();
			$dest = new Destination();
			if($dest_exist)
			{
				$data['msg'] = 'alredy exist';
				$data['code'] = '0';
			}
			else if($inprofile == "false")
			{
				$dest->user_id = (string)$user;
				$dest->place = (string)$place;
				$dest->created_date = "$date";
				$dest->updated_date = "$date";
				$dest->type = (string)$type;
				if($dest->insert())
				{
					$data['msg'] = 'insert success';
					$data['code'] = '1';
				}
				else
				{
					$data['msg'] = 'insert fail';
					$data['code'] = '0';
				}
			}
			else
			{
				$data['msg'] = 'oops..';
				$data['code'] = '0';
			}
		}
		else
		{
			$data['msg'] = 'invalid parameters';
			$data['code'] = '0';
		}
		return json_decode($data['code']);
    }
	
    public function removeUserDest($did)
    {
		if(isset($did) && !empty($did))
		{
			$dest_exist = Destination::find()->where(['_id' => (string)$did])->one();
			if($dest_exist)
			{
				$dest = Destination::find()->where(['_id' => (string)$did])->one();
				if($dest->delete())
				{
					$data['msg'] = 'delete success';
					$data['code'] = '2';
				}
				else
				{
					$data['msg'] = 'delete fail';
					$data['code'] = '0';
				}
			}
			else
			{
				$data['msg'] = 'not exist';
				$data['code'] = '0';
			}
		}
		else
		{
			$data['msg'] = 'invalid parameters';
			$data['code'] = '0';
		}
		return json_decode($data['code']);
    }
}