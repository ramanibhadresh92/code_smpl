<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\mongodb\ActiveRecord;
use frontend\models\Tours;

class ToursController extends Controller
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

    /* Tours */
    public function actionIndex()  
    {
        $place = Yii::$app->params['place'];
        $placetitle = Yii::$app->params['placetitle'];
        $placefirst = Yii::$app->params['placefirst'];
        $lat = Yii::$app->params['lat'];
        $lng = Yii::$app->params['lng'];

		$countries = Tours::getAllToursPlace();
        return $this->render('tours', array('countries' => $countries,'place' => $place,'placetitle' => $placetitle,'placefirst' => $placefirst,'lat' => $lat,'lng' => $lng));
    }
    
    public function actionGetcity()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        if($user_id)
        {
            $country = ucwords(strtolower($_POST['country'])); ?>
            
            <select id="chooseCity" class="select2 chooseCity"><option>Select City</option>
            <?php if(!empty($country)){
                $cities = Tours::getCities($country);
                foreach($cities as $cityname)
                { ?>
                    <option><?=$cityname?></option>
                <?php }
            } ?>
            </select>
            <?php
        }
        else
        {
            return $this->goHome();
        }
    }
    
    public function actionGetlist() 
    {
        $country = 'Japan'; 
        $lazyhelpcount = isset($_POST['$lazyhelpcount']) ? $_POST['$lazyhelpcount'] : 0;
        $start = $lazyhelpcount * 2000;

        $total_count = Tours::find()->count();

        $tourslist = Tours::getList($start, $country);
        if($country == '') {
            return $this->render('tourslistdefault',array('tourslist'=>$tourslist, 'country' => $country, 'lazyhelpcount' => $lazyhelpcount, 'total_count' => $total_count));
        } else {
            return $this->render('tourslist',array('tourslist'=>$tourslist, 'country' => $country, 'lazyhelpcount' => $lazyhelpcount, 'total_count' => $total_count));
        }
    }

    public function actionFirstthreetours() 
    {
        $tourslist = Tours::firstthreetours($start, $country);
		if($country == '') {
			return $this->render('tourslistdefault',array('tourslist'=>$tourslist, 'country' => $country, 'lazyhelpcount' => $lazyhelpcount));
		} else {
			return $this->render('tourslist',array('tourslist'=>$tourslist, 'country' => $country, 'lazyhelpcount' => $lazyhelpcount));
		}
    }
}
?>