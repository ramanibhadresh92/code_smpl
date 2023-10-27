<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class Order extends ActiveRecord
{
    public static function collectionName()
    {
        return 'order';
    }

    public function attributes()
    {
        return ['_id', 'user_id','current_date','month','year','transaction_id','status','order_type','detail','item','paid_with','item_number','curancy','amount'];
    }
	
	function neworder($item_number,$transaction_id,$amount,$curancy,$status,$order_type,$detail)
	{
		$session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
		
		$date = time();
		$curdate = date('d-m-Y h:i:s');
		
		$date2 = date('d-m-Y');
		$time=strtotime($date2);
		$current_month=date("F Y",$time);
		
		$month = date("m",$time);
		$year  = date("Y",$time);
		
		$order = new \frontend\models\Order();
		$order->user_id=$user_id;
		$order->current_date="$curdate";
		$order->transaction_id=$transaction_id;
		$order->amount=$amount;
		$order->status=$status;
		$order->curancy=$curancy;
		$order->detail=$detail;
		$order->order_type=$order_type;
		$order->month="$month";
		$order->year="$year";
		
		if($order->insert()){
			return true;
		}
		else{
			return false;
		}	
	}
	
	public function orderhistory($user_id)
	{
		$session = Yii::$app->session;
		$date = date('d-m-Y');
		$time=strtotime($date);
		$month=date("m",$time);
		$year=date("Y",$time);
		
		$record = Order::find()->where(['user_id' => $user_id,'month' => "$month",'year' => "$year"])->orderBy(['current_date'=>SORT_DESC])->all();
		return $record;
	} 
	
	public function ordermonthlyhistory($user_id,$post_date)
	{
		$session = Yii::$app->session;
		$date = date('d-m-Y');
		$time=strtotime($post_date);
		$month=date("m",$time);
		$year=date("Y",$time);
				
        $user_id = (string)$session->get('user_id');
		$record = Order::find()->where(['user_id' => $user_id,'month' => "$month",'year' => "$year"])->orderBy(['current_date'=>SORT_DESC])->all();
		return $record;
	} 
}