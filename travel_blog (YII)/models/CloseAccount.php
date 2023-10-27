<?php
namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
use frontend\models\PostForm;
use frontend\models\UserForm;

class CloseAccount extends ActiveRecord
{
    public static function collectionName()
    {
        return 'user_close_account';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'reason_of_leaving', 'why_close_account', 'created_date'];
    }
    
     public function getUserDetail()
    {
        return $this->hasOne(UserForm::className(), ['_id' => 'user_id']);
    }
    
     public function getPostData()
    {
        return $this->hasOne(PostForm::className(), ['_id' => 'post_id']);
    }
	
	public function CloseUserAccount($post, $uid)
    {

    	if(isset($post['reason_of_leaving']) && $_POST['reason_of_leaving']) {
    		if(isset($post['why_close_account']) && $_POST['why_close_account']) {
    			$reason_of_leaving = trim($post['reason_of_leaving']);
    			$why_close_account = $post['why_close_account'];
    			$selectedReasonArray = array("test1" => "This is temporary, I will be back.", "test2" => "I don't understand how to use Iaminjapan.", "test3" => "My account was hacked.", "test4" => "I spent too much time using Iaminjapan.", "test5" => "I get too many emails, invitations, and requests from Iaminjapan.", "test6" => "I have a privacy concern.", "test7" => "I have another Iaminjapan account.", "test8" => "I don't find Iaminjapan useful.", "test9" => "Other");
    			
    			if(array_key_exists($reason_of_leaving, $selectedReasonArray)) {
    				$reason_of_leaving = $selectedReasonArray[$reason_of_leaving];
    			} else {
    				$reason_of_leaving = $selectedReasonArray['test1'];
    			}

    			$CloseAccount = new CloseAccount();
    			$CloseAccount->reason_of_leaving = $reason_of_leaving;
    			$CloseAccount->why_close_account = $why_close_account;
    			$CloseAccount->created_date = time();
    			if($CloseAccount->save()) {
				}	
			}
		}
	}
    
}