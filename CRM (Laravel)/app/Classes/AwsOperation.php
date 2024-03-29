<?php

namespace App\Classes;
use DB;
use Aws\Rekognition\RekognitionClient;
class AwsOperation {


	/**
	 * AddFaceByImage
	 *
	 * @param  $requestFile, ExternalImageId
	 *
	 * @return array
	 */

	public static function searchFacesByImage($requestFile,$collectionName = " ")
	{

		$client = new RekognitionClient([
			'region'    => 'us-west-2',
			'version'   => 'latest'
		]);

		if(empty($collectionName)){
			$collectionName = env('AWS_COLLECTION_ID');
		}
		$image = fopen($requestFile->getPathName(), 'r');
		$bytes = fread($image, $requestFile->getSize());
		$results = $client->searchFacesByImage([
			'Image'                 => ['Bytes' => $bytes],
			'CollectionId'          => $collectionName, // REQUIRED
			'FaceMatchThreshold'    => 95.0,
			'MaxFaces'              => 1,
		]);
		return $results;
	}

	public static function searchFacesByImageV2($requestFile,$collectionName)
	{

		$client = new RekognitionClient([
			'region'    => 'us-west-2',
			'version'   => 'latest'
		]);

		$image = fopen($requestFile->getPathName(), 'r');
		$bytes = fread($image, $requestFile->getSize());
		$results = $client->searchFacesByImage([
			'Image'                 => ['Bytes' => $bytes],
			'CollectionId'          => $collectionName, // REQUIRED
			'FaceMatchThreshold'    => 95.0,
			'MaxFaces'              => 1,
		]);
		return $results;
	}

	/**
	 * searchFaceByWebcam
	 *
	 * @param  $requestFile, ExternalImageId
	 *
	 * @return array
	 */

	public static function searchFaceByWebcam($requestFile,$collectionName = " ")
	{
		$client = new RekognitionClient([
			'region'    => 'us-west-2',
			'version'   => 'latest'
		]);
		if(empty($collectionName)){
			$collectionName = env('AWS_COLLECTION_ID');
		}
	   // $image      = fopen($requestFile, 'r');
		$bytes      = $requestFile;
		$results = $client->searchFacesByImage([
			'Image'                 => ['Bytes' => $bytes],
			'CollectionId'          => $collectionName, // REQUIRED
			'FaceMatchThreshold'    => 95.0,
			'MaxFaces'              => 1,
		]);
		return $results;
	}

	/**
	 * AddFaceByImage
	 *
	 * @param  Image File, ExternalImageId
	 *
	 * @return array
	 */

	public static function AddFaceByImage($requestFile,$ExternalImageId,$collectionName){
		$client = new RekognitionClient(['region'    => 'us-west-2','version'   => 'latest']);
		$image      = fopen($requestFile->getPathName(), 'r');
		$bytes      = fread($image, $requestFile->getSize());
		$results    = $client->indexFaces([
			'CollectionId'              => $collectionName, // REQUIRED
			'DetectionAttributes'       => ["ALL", "DEFAULT"],
			'Image'                     => ['Bytes' => $bytes],
			'ExternalImageId'           => (string)$ExternalImageId,
			'MaxFaces'                  => 1,
			'QualityFilter'             => 'AUTO',
		]);

		return $results;
	}

	/**
	 * AddFaceByImage
	 *
	 * @param  Image File, ExternalImageId
	 *
	 * @return array
	 */

	public static function AddFaceByImage2($path,$ExternalImageId){
		$client = new RekognitionClient(['region'    => 'us-west-2','version'   => 'latest']);
		$image      = fopen($path, 'r');
		$bytes      = fread($image, filesize($path));

		$results    = $client->indexFaces([
			'CollectionId'              => env('AWS_COLLECTION_ID'), // REQUIRED
			'DetectionAttributes'       => ["ALL", "DEFAULT"],
			'Image'                     => ['Bytes' => $bytes],
			'ExternalImageId'           => (string)$ExternalImageId,
			'MaxFaces'                  => 1,
			'QualityFilter'             => 'AUTO',
		]);

		return $results;
	}

	public static function deleteFaces($faceArray,$collectionName = ""){
		$client = new RekognitionClient(['region'    => 'us-west-2','version'   => 'latest']);
		$results    = $client->deleteFaces(['CollectionId'      => env('AWS_COLLECTION_ID'), // REQUIRED
											'FaceIds'           => $faceArray]);
		return $results;
	}


	public static function test($bytes,$ExternalImageId,$collectionName){
		$client = new RekognitionClient([
			'region'    => 'us-west-2',
			'version'   => 'latest'
		]);

		// $image      = fopen($requestFile->getPathName(), 'r');
		// $bytes      = fread($image, $requestFile->getSize());
		$results    = $client->indexFaces([
			'CollectionId'              => $collectionName, // REQUIRED
			'DetectionAttributes'       => ["ALL", "DEFAULT"],
			'Image'                     => ['Bytes' => $bytes],
			'ExternalImageId'           => (string)$ExternalImageId,
			'MaxFaces'                  => 1,
			'QualityFilter'             => 'AUTO',
		]);

		return $results;
	}
}

