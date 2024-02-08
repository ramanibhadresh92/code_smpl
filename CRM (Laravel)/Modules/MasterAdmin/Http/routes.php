<?php

$PRIFIX = 'web/v1/masteradmin';
Route::group(['middleware' =>  ['web','localization','jwt.auth','cors'], 'prefix' => $PRIFIX, 'namespace' => 'Modules\MasterAdmin\Http\Controllers'], function()
{
    Route::get('/', 'MasterAdminController@index');

    /**
	* Module 	: 	User Management
	* Use 		:	Access user management Api
	*/
	Route::group(['prefix' =>'user'], function()
	{
		Route::post('list','UserManagementController@list')->name('super-user-list');
		Route::post('create','UserManagementController@create')->name('super-user-add');
		Route::post('getById','UserManagementController@edit')->name('super-user-detail');
		Route::post('update','UserManagementController@update')->name('super-user-update');
		Route::post('status','UserManagementController@changeStatus')->name('super-user-change-status');
	});
	Route::group(['prefix' =>'company'], function()
	{
		Route::post('list','CompanyMasterController@list')->name('master-company-list');
		Route::post('create','CompanyMasterController@create')->name('master-company-add');
		Route::post('getById','CompanyMasterController@getById')->name('master-company-detail');
		Route::post('update','CompanyMasterController@update')->name('master-company-update');
		Route::post('apiDataLogger','CompanyMasterController@ApiDataLogger')->name('ApiDataLogger');

	});
	Route::group(['prefix' =>'parameter'], function() {
		Route::post('type','MasterSettingsController@index')->name('parameter-type-index');
		Route::post('list','MasterSettingsController@list')->name('parameter-list');
		Route::post('parameterType','MasterSettingsController@getParameterType')->name('parameter-type-list');
		Route::post('create','MasterSettingsController@create')->name('parameter-add');
		Route::post('getById','MasterSettingsController@getByIdParameter')->name('parameter-detail');
		Route::post('update','MasterSettingsController@update')->name('parameter-update');
		Route::post('type/create','MasterSettingsController@addParameterType')->name('parameter-type-add');
		Route::post('type/update','MasterSettingsController@updateParameterType')->name('parameter-type-update');
		Route::post('status','MasterSettingsController@changeStatus')->name('change-status-parameter');
	});
	Route::group(['prefix' =>'category'], function()
	
	{
		Route::post('changeStatus','CategoryManagementController@changeStatus')->name('master-changeStatus');
		Route::post('list','CategoryManagementController@list')->name('master-category-list');
		Route::post('create','CategoryManagementController@create')->name('master-category-add');
		Route::post('getById','CategoryManagementController@getById')->name('master-category-detail');
		Route::post('update','CategoryManagementController@update')->name('master-category-update');
		Route::post('changeOrder','CategoryManagementController@changeOrder')->name('master-category-change-order');
		Route::post('dropdown','CategoryManagementController@dropdown')->name('master-category-dropdown');
	});
	Route::group(['prefix' =>'pricegroup'], function()
	{
		Route::post('list','MasterPriceGroupController@list')->name('master-pricegroup-list');
		Route::post('create','MasterPriceGroupController@create')->name('master-pricegroup-add');
		Route::post('getById','MasterPriceGroupController@getById')->name('master-pricegroup-detail');
		Route::put('update','MasterPriceGroupController@update')->name('master-pricegroup-update');
		Route::post('changeStatus','MasterPriceGroupController@changeStatus')->name('master-changeStatus');
	});
	Route::group(['prefix' =>'product'], function()
	{
		Route::get('status','MasterProductController@status')->name('master-status');
		Route::get('productUnit','MasterProductController@productUnit')->name('master-product-unit');
		Route::get('ProductGroup','MasterProductController@productGroup')->name('master-product-group');
		Route::post('list','MasterProductController@list')->name('master-product-list');
		Route::post('create','MasterProductController@create')->name('master-product-add');
		Route::post('getById','MasterProductController@getById')->name('master-product-detail');
		Route::post('update','MasterProductController@update')->name('master-pricegroup-update');
		Route::post('productPriceDetail','MasterProductController@listProductPriceDetail')->name('master-product-detail');
		Route::post('productPriceGroup/create','MasterProductController@addProductPriceGroup')->name('add-product-price-group');
		Route::post('productVeriableDetail','MasterProductController@productVeriableDetail')->name('master-veriable-detail');
		Route::post('changeStatus','MasterProductController@changeStatus')->name('super-user-change-status');
	});

	Route::group(['prefix' =>'location'], function()
	{
		Route::post('list','MasterSettingsController@ListLocationMaster')->name('ListLocationMaster');
		Route::post('addOrUpdate','MasterSettingsController@AddOrUpdateLocation')->name('AddOrUpdateLocation');
		Route::post('getById','MasterSettingsController@getById')->name('GetById');
		Route::post('status','MasterSettingsController@changeStatus')->name('super-user-change-status');
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
