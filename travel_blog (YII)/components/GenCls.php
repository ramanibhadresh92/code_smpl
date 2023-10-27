<?php  
namespace common\components;
 
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use frontend\models\LoginForm;

class GenCls extends Component 
{
    public function getimage($userid,$type)
    {
          
        $assetsPath = '../../vendor/bower/travel/images/';
        $resultimg = LoginForm::find()->where(['_id' => $userid])->one();
        
        if(substr($resultimg['photo'],0,4) == 'http')
        {
            if($type == 'photo')
            {
                $dp = $resultimg['photo'];
            }
            else
            {
                $dp = $resultimg['thumbnail'];
            }
        }
        else
        {
            if(isset($resultimg['thumbnail']) && !empty($resultimg['thumbnail']) && file_exists('profile/'.$resultimg['thumbnail']))
            {
                $dp = "profile/".$resultimg['thumbnail'];
            }
            else if(isset($resultimg['gender']) && !empty($resultimg['gender']) && file_exists($assetsPath.$resultimg['gender'].'.jpg'))
            {
                $dp = $assetsPath.$resultimg['gender'].'.jpg';
            }
            else
            {
                $dp = $assetsPath."DefaultGender.jpg";
            }
        }
        return $dp;
    }

    public function filtergetimage($resultimg)
    {
          
        $assetsPath = '../../vendor/bower/travel/images/';
        if(isset($resultimg['photo']) && substr($resultimg['photo'],0,4) == 'http')
        {
            if($type == 'photo') {
                $dp = $resultimg['photo'];
            } else {
                $dp = $resultimg['thumbnail'];
            }
        } else {
            if(isset($resultimg['thumbnail']) && !empty($resultimg['thumbnail']) && file_exists('profile/'.$resultimg['thumbnail']))
            {
                $dp = "profile/".$resultimg['thumbnail'];
            } else if(isset($resultimg['gender']) && !empty($resultimg['gender']) && file_exists($assetsPath.$resultimg['gender'].'.jpg'))
            {
                $dp = $assetsPath.$resultimg['gender'].'.jpg';
            } else {
                $dp = $assetsPath."DefaultGender.jpg";
            }
        }
        return $dp;
    }

    public function msgGetIdsDATA($ids)
    {
          
        $assetsPath = '../../vendor/bower/travel/images/';
        $usrData = LoginForm::find()->select(['fullname', 'photo', 'thumbnail'])->where(['in', (string)'_id', $ids])->limit(2)->offset(0)->asarray()->all();

        $result = array(); 
        foreach ($usrData as $S_usrData) {
            $uid = (string)$S_usrData['_id'];
            $fullname = $S_usrData['fullname'];
            $photo = isset($S_usrData['photo']) ? $S_usrData['photo'] : '';
            $thumb = isset($S_usrData['thumbnail']) ? $S_usrData['thumbnail'] : '';
            $gender = isset($S_usrData['gender']) ? $S_usrData['gender'].'.jpg' : 'Male.jpg';

            if($photo != '' && substr($photo,0,4) == 'http') {
                $dp = $thumb;
            } else {
                if($thumb != '' && file_exists('profile/'.$thumb))
                {
                    $dp = "profile/".$thumb;
                }
                else
                {
                    $dp = $assetsPath.$gender;
                }
            }

            $result[$uid]['fullname'] = $fullname;
            $result[$uid]['thumb'] = $dp;
        }
        
        return $result;
    }

    public function getuserdata($userid,$type)
    {
        $resultimg = LoginForm::find()->where(['_id' => $userid])->one();
        $fullname = $resultimg[$type];
        return $fullname;
    }

    public function getadrate($isvip,$type)
    {
        if($isvip)
        {
            if($type == 'impression'){return 2.40;}
            if($type == 'action'){return 0.20;}
            if($type == 'click'){return 0.40;}
        }
        else
        {
            if($type == 'impression'){return 3.00;}
            if($type == 'action'){return 0.25;}
            if($type == 'click'){return 0.50;}
        }
    }

    public function goHome()
    {
        return Yii::$app->getResponse()->redirect(Yii::$app->getHomeUrl());
    }

}