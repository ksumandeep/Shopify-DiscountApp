<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*Route::get('/', function () {
    return view('welcome');
});  */


/*Route::get('/', function () {
    return view('welcome');
})->middleware(['auth.shopify'])->name('home');*/

Route::get('/', 'DashboardController@dashboard')->middleware(['auth.shopify'])->name('home');

Route::get('/dashboard', 'DashboardController@dashboard')->middleware(['auth.shopify'])->name('dashboard');
Route::get('/shopifyProducts', 'DashboardController@shopifyProducts')->name('shopifyProducts');
Route::get('/assignProduct', 'DashboardController@assignProduct')->name('assignProduct');
Route::get('/disableAllProduct', 'DashboardController@disableAllProduct')->name('disableAllProduct');
Route::get('/disableSingleProduct', 'DashboardController@disableSingleProduct')->name('disableSingleProduct');

Route::get('/checkActiveDiscount', 'DashboardController@checkActiveDiscount')->name('checkActiveDiscount');
//Route::get('/api', 'APIController')->name('api');
Route::get('/getProducts', 'APIController@getShopifyProducts')->name('getShopifyProducts');

Route::get('/settings', 'DashboardController@settingsIndex')->name('settings');
Route::post('/updateClientInfo', 'DashboardController@updateClientInfo')->name('updateClientInfo');


Route::post('/checkWebhook', 'DashboardController@checkWebhook')->name('checkWebhook');

