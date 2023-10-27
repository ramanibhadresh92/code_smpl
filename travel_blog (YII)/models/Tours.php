<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
use yii\db\Expression;

class Tours extends ActiveRecord
{
    public static function collectionName()
    {
        return 'tours';
    }

    public function attributes()
    {
        return ['_id', 'Rank', 'ProductType', 'ProductCode', 'ProductName', 'Introduction', 'ProductText', 'Special', 'Duration', 'Commences', 'ProductImage', 'ProductImageThumb', 'DestinationID', 'Continent', 'Country', 'Region', 'City', 'IATACode', 'Category1', 'Subcategory1', 'Category2', 'Subcategory2', 'Category3', 'Subcategory3', 'ProductURL', 'PriceAUD', 'PriceNZD', 'PriceEUR', 'PriceGBP', 'PriceUSD', 'PriceCAD', 'PriceCHF', 'PriceNOK', 'PriceJPY', 'PriceSEK', 'PriceHKD', 'PriceSGD', 'PriceZAR', 'AvgRating', 'AvgRatingStarURL', 'BookingType', 'VoucherOption'];
    }

    public function getCities($country)
    {
        return Tours::find()->select(['City'])->where(['Country' => $country])->distinct('City');
    }
    
    public function getRandTenList($start=0)
    {
        if($start == '') {
            $start = 0;
        } 
        $cityBulk = array('London', 'Sdynie', 'Cario', 'Tokyou', 'New York', 'Amman', 'Japan');
        $result = array();


        foreach ($cityBulk as $key => $value) {
            $data = Tours::find()->where(['City' => $value])->asarray()->limit(3)->offset($start)->all();
            $result[$value] = $data;
        }

        return $result;
    }
    
    public function getTourList()
    {
        return Tours::find()->orderBy(['PriceUSD'=>SORT_ASC])->limit(100)->all();
    }
    
    public function getTourListCount()
    {
        return Tours::find()->count();
    }
    
    public function getList($start, $country)
    {  
        $result = array();  
        if($start == '') {
            $start = 0;
        }
        if($country != '')
        {
            $tours = Tours::find()->asarray()->limit(2000)->offset($start)->all();
            if(!empty($tours)) {
                foreach ($tours as $single_tour) {
                    $ProductName = isset($single_tour["ProductName"]) ? $single_tour["ProductName"] : "";
                    $Commences = isset($single_tour["Commences"]) ? $single_tour["Commences"] : "";
                    $__Country = isset($single_tour["Country"]) ? $single_tour["Country"] : "";

                    if (stripos($ProductName, $country) !== false) {
                        $result[] = $single_tour;
                    } else if (stripos($Commences, $country) !== false) {
                        $result[] = $single_tour;
                    } else if (stripos($__Country, $country) !== false) {
                        $result[] = $single_tour;
                    }
                }
            }

            return $result;
        } 
        else
        {
            return Tours::getRandTenList($start);
        }
    }  

    public function firstthreetours()
    {
        $country = 'Japan';
        $data = explode(",", $country);
        $city = isset($data[0]) ? trim($data[0]) : '';
        $country = isset($data[1]) ? trim($data[1]) : '';

        if($country != '' && $city != '') {
            return Tours::find()->where(['Country' => $country, 'City' => $city])->asarray()->limit(3)->all();
        } else if($country == '' && $city != '') {
            return Tours::find()->where(['City' => $city])->asarray()->limit(3)->all();
        } else if ($country != '' && $city == '') {
            return Tours::find()->where(['Country' => $country])->asarray()->limit(3)->all();
        } else {
            return array();
        }
    }  

    public function getListParticular($city, $name)
    {

        $city = array_map('trim', explode(',', $city));
        $city = array_filter($city);

        if(count($city) == 1) {
            $city1 = $city[0];
            return Tours::find()->where(['like', 'Country', $city1])->orwhere(['like', 'City', $city1])->andwhere(['like', 'ProductName', $name])->asarray()->all();
        } else if(count($city) == 2) {
            $city1 = $city[0];
            $city2 = $city[1];
            return Tours::find()->where(['like', 'Country', $city1])->where(['like', 'Country', $city2])->orwhere(['like', 'City', $city1])->orwhere(['like', 'City', $city2])->andwhere(['like', 'ProductName', $name])->asarray()->all();
        
        } else if(count($city) == 3) {
            $city1 = $city[0];
            $city2 = $city[1];
            $city3 = $city[2];
            return Tours::find()->where(['like', 'Country', $city1])->where(['like', 'Country', $city2])->where(['like', 'Country', $city3])->orwhere(['like', 'City', $city1])->orwhere(['like', 'City', $city2])->orwhere(['like', 'City', $city3])->andwhere(['like', 'ProductName', $name])->asarray()->all();
        } else {
            return Tours::find()->where(['like', 'ProductName', $name])->asarray()->all();
        }
    }
    
    public function getTodos($city,$country,$type,$sort,$start=0)
    {
        if($start == '') {
            $start = 0;
        } 
        if($type == 'Country')
        {
            $c = Tours::find()->where(['Country' => $city])->count();
            if($c > 0)
            {
                return Tours::find()->where(['Country' => $city])->orderBy([$sort=>SORT_ASC])->limit(12)->offset($start)->asarray()->all();
            }
            else
            {
                return Tours::find()->where(['City' => $city])->orderBy([$sort=>SORT_ASC])->limit(12)->offset($start)->asarray()->all();
            }
        }
        else if($type == 'City')
        {
            if($country != '') {
                return Tours::find()->where(['Country' => ltrim($country)])->orwhere(['City' => $city])->orderBy([$sort=>SORT_ASC])->limit(12)->offset($start)->asarray()->all();
            } else {
                return Tours::find()->where(['City' => $city])->orderBy([$sort=>SORT_ASC])->limit(12)->offset($start)->asarray()->all();
            }
        }
        else
        {
            return Tours::find()->where(['like','Commences',$city])->orderBy([$sort=>SORT_ASC])->limit(12)->offset($start)->asarray()->all();
        }
    }
    
    public function getSimilarDestinations($country)
    {
        $ad = Tours::find()->select(['City'])->where(['Country' => $Country])->orderBy(new Expression('rand()'))->limit(3)->asarray()->all();
        if(empty($ad)){$ad = array();}
        return $ad;
    }

    public function getAllToursPlace() {
        $countries = Tours::find()->select(['Country', 'City'])->asarray()->all();
        $data = array();
        foreach ($countries as $key => $countrie) {
            $City = isset($countrie['City']) ? $countrie['City'] : '';
            if($City != '') {
                $City = $City . ', ';
            }

            $Country = $countrie['Country'];
            $joiner = $City.$Country;
            $joiner = str_replace("'", "", $joiner);
           $data[$joiner] = 1;
        };

        $data = array_keys($data);  
        sort($data);
        return $data;  
    }
}