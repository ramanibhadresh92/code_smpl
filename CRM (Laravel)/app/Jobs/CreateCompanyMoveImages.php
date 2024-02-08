<?php

namespace App\Jobs;

use App\Models\CollectionTags;
use App\Models\CompanyCategoryMaster;
use App\Models\CompanyProductMaster;
use App\Models\Log;
use App\Models\MediaMaster;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;

class CreateCompanyMoveImages implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


	public $companyID;
	public $cityId;
	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct($data)
	{
		$this->companyID    = $data['company_id'];
		$this->cityId       = $data['city_id'];
	}


	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{
		//

		$companyID  = $this->companyID;
		$cityID     = $this->cityId;
		$companyPath = public_path('/images').'/company/'.$companyID;

		if(!is_dir($companyPath)) {
			mkdir($companyPath,0777,true);
		}

		$zipFile    = 'images.zip';
		$pathImages = public_path('/images').'/company/0/'.$zipFile;

		if (!copy($pathImages, $companyPath.'/'.$zipFile)) {
		}else{
		}

		if(file_exists($companyPath.'/'.$zipFile)){
			$zip = new \ZipArchive;
			$res = $zip->open($companyPath.'/'.$zipFile);
			$zip->extractTo($companyPath);
			$zip->close();
			unlink($companyPath.'/'.$zipFile);
		}

		$folders = self::dirToArray($companyPath.'/');

		$imageMedia = array();

		if(!empty($folders)){
			foreach ($folders as $key => $inner_folder){
				$image_path = $companyPath.'/'.$key;
				if(is_array($inner_folder)){
					foreach ($inner_folder  as $foldername => $files){
						if(is_array($files)) {
							$original_name = isset($files[0]) ? $files[0] : "";
							$server_name = isset($files[1]) ? $files[1] : "";
							if (!empty($original_name) || !empty($server_name)) {
								$imageMedia[$key][$foldername]['image_path'] = $image_path . '/' . $foldername;
								$imageMedia[$key][$foldername]['original_name'] = $original_name;
								$imageMedia[$key][$foldername]['server_name'] = $server_name;
							}
						}
					}
				}
			}
		}

		foreach ($imageMedia as $key => $value){
			foreach ($value as $filekey => $file){
				switch ($key){
					case 'product':
						$mediamaseter = MediaMaster::create([
							'company_id'    => $companyID,
							'image_path'    => str_replace(public_path('/images').'/','',$file['image_path']),
							'original_name' => $file['original_name'],
							'server_name'   => $file['server_name'],
						]);

						$productmaster = CompanyProductMaster::where('ref_product_id',$filekey)->where('company_id',$companyID)->first();
						if($productmaster) {
							$productmaster->normal_img = $mediamaseter->id;
							$productmaster->select_img = $mediamaseter->id;
							$productmaster->save();
						}
						break;

					case 'category':
						$mediamaseter = MediaMaster::create([
							'company_id'    => $companyID,
							'image_path'    => str_replace(public_path('/images').'/','',$file['image_path']),
							'original_name' => $file['original_name'],
							'server_name'   => $file['server_name'],
						]);

						$productmaster = CompanyCategoryMaster::where('ref_category_id',$filekey)->where('company_id',$companyID)->first();
						if($productmaster) {
							$productmaster->normal_img = $mediamaseter->id;
							$productmaster->select_img = $mediamaseter->id;
							$productmaster->save();
						}
						break;

					case 'collectiontag':
						$mediamaseter = MediaMaster::create([
							'company_id'    => $companyID,
							'image_path'    => str_replace(public_path('/images').'/','',$file['image_path']),
							'original_name' => $file['original_name'],
							'server_name'   => $file['server_name'],
						]);

						$productmaster = CollectionTags::where('ref_tag_id',$filekey)->where('company_id',$companyID)->get();
						if($productmaster && count($productmaster) > 0) {
							foreach ($productmaster as $product) {
								if ($product) {
									$product->normal_img = $mediamaseter->id;
									$product->select_img = $mediamaseter->id;
									$product->save();
								}
							}
						}
						break;
				}

			}
		}
	}

	public static function dirToArray($dir) {

		$result = array();

		$cdir = scandir($dir);
		foreach ($cdir as $key => $value)
		{
			if (!in_array($value,array(".","..")))
			{
				if (is_dir($dir . DIRECTORY_SEPARATOR . $value))
				{
					$result[$value] = self::dirToArray($dir . DIRECTORY_SEPARATOR . $value);
				}
				else
				{
					$result[] = $value;
				}
			}
		}

		return $result;
	}
}
