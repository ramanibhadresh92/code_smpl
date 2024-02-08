<?php
use Modules\MasterAdmin\Http\Controllers\UserManagementController as UserManagementController;
use Modules\MasterAdmin\Http\Controllers\CompanyMasterController as CompanyMasterController;
use Modules\MasterAdmin\Http\Controllers\MasterSettingsController as MasterSettingsController;
use Modules\MasterAdmin\Http\Controllers\CategoryManagementController as CategoryManagementController;
use Modules\MasterAdmin\Http\Controllers\MasterPriceGroupController as MasterPriceGroupController;
use Modules\MasterAdmin\Http\Controllers\MasterProductController as MasterProductController;

$PRIFIX = 'web/v1/masteradmin';
 
Route::group(['middleware' =>  ['web','localization','jwt.auth','cors'], 'prefix' => $PRIFIX, 'namespace' => 'Modules\MasterAdmin\Http\Controllers'], function() {

    Route::get('/', 'MasterAdminController@index');

	Route::group(['prefix' =>'user'], function() {
		Route::post('list',[UserManagementController::class,'list'])->name('super-user-list');
		Route::post('create',[UserManagementController::class,'create'])->name('super-user-add');
		Route::post('getById',[UserManagementController::class,'edit'])->name('super-user-detail');
		Route::post('update',[UserManagementController::class,'update'])->name('super-user-update');
		Route::post('status',[UserManagementController::class,'changeStatus'])->name('super-user-change-status');
	});

	Route::group(['prefix' =>'company'], function() {
		Route::post('list',[CompanyMasterController::class,'list'])->name('master-company-list');
		Route::post('create',[CompanyMasterController::class,'create'])->name('master-company-add');
		Route::post('getById',[CompanyMasterController::class,'getById'])->name('master-company-detail');
		Route::post('update',[CompanyMasterController::class,'update'])->name('master-company-update');
		Route::post('apiDataLogger',[CompanyMasterController::class,'ApiDataLogger'])->name('ApiDataLogger');
	});
	
	Route::group(['prefix' =>'parameter'], function() {
		Route::post('type',[MasterSettingsController::class,'index'])->name('parameter-type-index');
		Route::post('list',[MasterSettingsController::class,'list'])->name('parameter-list');
		Route::post('parameterType',[MasterSettingsController::class,'getParameterType'])->name('parameter-type-list');
		Route::post('create',[MasterSettingsController::class,'create'])->name('parameter-add');
		Route::post('getById',[MasterSettingsController::class,'getByIdParameter'])->name('parameter-detail');
		Route::post('update',[MasterSettingsController::class,'update'])->name('parameter-update');
		Route::post('type/create',[MasterSettingsController::class,'addParameterType'])->name('parameter-type-add');
		Route::post('type/update',[MasterSettingsController::class,'updateParameterType'])->name('parameter-type-update');
		Route::post('status',[MasterSettingsController::class,'changeStatus'])->name('change-status-parameter');
	});
	
	Route::group(['prefix' =>'category'], function() {
		Route::post('changeStatus',[CategoryManagementController::class,'changeStatus'])->name('master-changeStatus');
		Route::post('list',[CategoryManagementController::class,'list'])->name('master-category-list');
		Route::post('create',[CategoryManagementController::class,'create'])->name('master-category-add');
		Route::post('getById',[CategoryManagementController::class,'getById'])->name('master-category-detail');
		Route::post('update',[CategoryManagementController::class,'update'])->name('master-category-update');
		Route::post('changeOrder',[CategoryManagementController::class,'changeOrder'])->name('master-category-change-order');
		Route::post('dropdown',[CategoryManagementController::class,'dropdown'])->name('master-category-dropdown');
	});

	Route::group(['prefix' =>'pricegroup'], function() {
		Route::post('list',[MasterPriceGroupController::class,'list'])->name('master-pricegroup-list');
		Route::post('create',[MasterPriceGroupController::class,'create'])->name('master-pricegroup-add');
		Route::post('getById',[MasterPriceGroupController::class,'getById'])->name('master-pricegroup-detail');
		Route::post('update',[MasterPriceGroupController::class,'update'])->name('master-pricegroup-update');
		Route::post('changeStatus',[MasterPriceGroupController::class,'changeStatus'])->name('master-changeStatus');
	});

	Route::group(['prefix' =>'product'], function() {
		Route::post('status',[MasterProductController::class,'status'])->name('master-status');
		Route::post('productUnit',[MasterProductController::class,'productUnit'])->name('master-product-unit');
		Route::post('ProductGroup',[MasterProductController::class,'productGroup'])->name('master-product-group');
		Route::post('list',[MasterProductController::class,'list'])->name('master-product-list');
		Route::post('create',[MasterProductController::class,'create'])->name('master-product-add');
		Route::post('getById',[MasterProductController::class,'getById'])->name('master-product-detail');
		Route::post('update',[MasterProductController::class,'update'])->name('master-pricegroup-update');
		Route::post('productPriceDetail',[MasterProductController::class,'listProductPriceDetail'])->name('master-product-detail');
		Route::post('productPriceGroup/create',[MasterProductController::class,'addProductPriceGroup'])->name('add-product-price-group');
		Route::post('productVeriableDetail',[MasterProductController::class,'productVeriableDetail'])->name('master-veriable-detail');
		Route::post('changeStatus',[MasterProductController::class,'changeStatus'])->name('super-user-change-status');
	});

	Route::group(['prefix' =>'location'], function() {
		Route::post('list',[MasterSettingsController::class,'changeStatus'])->name('ListLocationMaster');
		Route::post('addOrUpdate',[MasterSettingsController::class,'AddOrUpdateLocation'])->name('AddOrUpdateLocation');
		Route::post('getById',[MasterSettingsController::class,'getById'])->name('GetById');
		Route::post('status',[MasterSettingsController::class,'changeStatus'])->name('super-user-change-status');
		Route::post('state', function () {
			$data = App\Models\StateMaster::all();
			return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $data]);
		})->name('get-master-state');  
		Route::post('country', function () {
			$data = App\Models\CountryMaster::all();
			return response()->json(["code" => SUCCESS , "msg" =>trans('message.RECORD_FOUND'),"data" => $data]);
		})->name('get-master-state');  
	});
});
