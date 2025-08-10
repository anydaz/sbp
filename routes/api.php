<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/login', 'App\Http\Controllers\AuthController@login');

Route::middleware(['auth:sanctum'])->group(function () {
	Route::get('/auth', 'App\Http\Controllers\UserController@getAuthInfo');
	Route::resource('users', 'App\Http\Controllers\UserController');
	Route::post('change_password', 'App\Http\Controllers\UserController@change_password');

	Route::resource('customers', 'App\Http\Controllers\CustomerController');
	Route::resource('payment_types', 'App\Http\Controllers\PaymentTypeController');
	Route::resource('payment_categories', 'App\Http\Controllers\PaymentCategoryController');

	Route::resource('products', 'App\Http\Controllers\ProductController');
	Route::resource('product_categories', 'App\Http\Controllers\ProductCategoryController');
	Route::resource('draft_sales_orders', 'App\Http\Controllers\DraftSalesOrderController');
	Route::get('draft_sales_orders/{id}/print', 'App\Http\Controllers\DraftSalesOrderController@print');

	Route::resource('sales_orders', 'App\Http\Controllers\SalesOrderController');
	Route::get('sales_orders/{id}/print', 'App\Http\Controllers\SalesOrderController@print');

	Route::resource('sales_returns', 'App\Http\Controllers\SalesReturnController');

	Route::resource('purchase_orders', 'App\Http\Controllers\PurchaseOrderController');
	Route::resource('delivery_notes', 'App\Http\Controllers\DeliveryNoteController');
	Route::resource('purchase_returns', 'App\Http\Controllers\PurchaseReturnController');

	Route::resource('journal_batches', 'App\Http\Controllers\JournalBatchController');

	Route::post('products/new/import', 'App\Http\Controllers\ProductController@import_new');
	Route::post('products/import', 'App\Http\Controllers\ProductController@import');
	Route::post('products/bulk_update_price', 'App\Http\Controllers\ProductController@bulk_update_price');
	Route::post('products/export', 'App\Http\Controllers\ProductController@export');
	// Route::get('products/barcode/{code}', 'App\Http\Controllers\ProductController@find_by_barcode');
	// Route::get('products/efficiency_code/{code}', 'App\Http\Controllers\ProductController@find_by_efficiency_code');
	Route::get('products/{id}/print', 'App\Http\Controllers\ProductController@print');

	Route::post('product_categories/import', 'App\Http\Controllers\ProductCategoryController@import');

	Route::post('purchase_order/import', 'App\Http\Controllers\PurchaseOrderController@import');

	Route::get('report/sales', 'App\Http\Controllers\ReportController@report_sales');
	Route::post('report/sales/export', 'App\Http\Controllers\ReportController@export_sales');
	Route::get('report/purchase', 'App\Http\Controllers\ReportController@report_purchase');

	Route::post('/print', 'App\Http\Controllers\PrintController@print');
});