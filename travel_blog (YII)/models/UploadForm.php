<?php
namespace frontend\models;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class UploadForm extends ActiveRecord
{
    public $imageFile;

    public static function collectionName()
    {
        return 'image';
    }
    public function attributes()
    {
        return ['_id', 'imageFile'];
    }
    
      public function getUser()
    {
        return $this->hasOne(UserForm::className(), ['_id' => 'post_user_id']);
    }
   
    public function rules()
    {
        return [
            [['imageFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg'],
        ];
    }
    
    public function upload()
    {
        if ($this->validate()) 
        {
          $temp = $this->imageFile;
          $this->imageFile->saveAs('./uploads/' . $this->imageFile->baseName . '.' . $this->imageFile->extension);
          return true;
          
        } else {
            return false;
        }
    }
}