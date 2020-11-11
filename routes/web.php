<?php

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

Route::get('/', function () {
    return view('welcome');
});

//Route::get('/Token','test\TestController@token');//测试接入
Route::post('/index','WxController@index');//接入

Route::post('/wxEvent','WxController@wxEvent');//消息推送
Route::any('/token',"WxController@getAccressToken");//获取access_token
Route::get('/create_menu','WxController@create_menu');//添加菜单

 