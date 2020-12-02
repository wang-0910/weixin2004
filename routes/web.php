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
Route::get('/weather','WxController@weather');//天气
// Route::any('/pinyin','WxController@pinyin');//天气

//测试登录


Route::prefix('/api')->group(function(){
    Route::get('/test','ApiController@test')->middleware('token');
    Route::get('/login',"ApiController@login");
    Route::get('/goods',"ApiController@goods")->middleware('token');
    Route::get('/goodslist  ',"ApiController@goodsList")->middleware('token');
    Route::get('/goodsdetail',"ApiController@goodsDetail")->middleware('token');
    Route::get('/xcxlogin','ApiController@xcxlogin');
    Route::post('/cart','ApiController@cart')->middleware('token');//加入购物车

});

 