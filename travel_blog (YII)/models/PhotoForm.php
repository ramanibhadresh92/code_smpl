<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class PhotoForm extends ActiveRecord
{  
    public static function collectionName()
    {
        return 'place_photo';
    }

    public function attributes()
    {
        return ['_id', 'created_date', 'is_deleted', 'user_id', 'image', 'place', 'long_place'];
    }

	public function getUser()
    {
        return $this->hasOne(LoginForm::className(), ['_id' => 'user_id']);
    }

    public function getAllPics($place)
    {
        return PhotoForm::find()->where(['is_deleted'=>'0','long_place'=>$place])->orderBy(['created_date'=>SORT_DESC])->all();
    }
 
	public function getAllPhotos()
    {
        return PhotoForm::find()->with('user')->where(['is_deleted'=>'0'])->orderBy(['created_date'=>SORT_DESC])->all();
    }
	
	public function getTotalPics($place)
    {
		$results = PhotoForm::getAllPics($place);
        $total = 0;
        foreach($results as $result)
        {
			$eximgs = explode(',',$result['image'],-1);
			$added_by = $result['user_id'];
			$tpics = 0;
			foreach ($eximgs as $eximg)
			{
				$file = '../web/uploads/placephotos/'.$added_by.'/'.$eximg;
				if(file_exists($file)){$tpics = $tpics+1;}
				else{$tpics = 0;}
			}
			$total = $tpics + $total;
        }
        return $total;
    }
	
	public function deletePlacePhotos($post_id,$user_id,$image_name,$where)
    {
		$delimage = PhotoForm::find()->where(['_id' => $post_id,'user_id' => $user_id,'is_deleted' => '0'])->one();
		if($delimage)
		{
			$imagevalue = $delimage['image'];
			$imagepath = $image_name.',';
			$updatedimagevalue = str_replace($imagepath,"",$imagevalue);
			if(strlen($updatedimagevalue) < 3)
			{
				$file = '../web/uploads/placephotos/'.$user_id.'/'.$image_name;
				if($where == 'backend')
				{
					$front_url = Yii::$app->urlManagerFrontEnd->baseUrl;
					$file = $front_url.'/uploads/placephotos/'.$user_id.'/'.$image_name;
				}
				unlink($file);
				$delimage->delete();
				$data['value'] = '1';
				return json_encode($data);
			}
			$delimage->image = $updatedimagevalue;
			if($delimage->update())
			{
				$file = '../web/uploads/placephotos/'.$user_id.'/'.$image_name;
				if($where == 'backend')
				{
					$front_url = Yii::$app->urlManagerFrontEnd->baseUrl;
					$file = $front_url.'/uploads/placephotos/'.$user_id.'/'.$image_name;
				}
				unlink($file);
				$data['value'] = '1';
				return json_encode($data);
			}
			else
			{
				$data['value'] = '0';
				return json_encode($data);
			}
		}
		else
		{
			$data['value'] = '0';
			return json_encode($data);
		}
    }
}