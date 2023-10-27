<?php
namespace frontend\controllers;

class ImageController extends Controller
{
    public function actions()
      {
          return [
              'upload' => [
                  'class' => 'try\ImageUpload\UploadAction',
                  'successCallback' => [$this, 'successCallback'],
                  'beforeStoreCallback' => [$this,'beforeStoreCallback']
              ],
          ]
      }

      public function successCallback($store,$file)
      {
      }
      public function beforeStoreCallback($file)
      {
      }
}