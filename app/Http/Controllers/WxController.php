<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Log;
class WxController extends Controller
{
    /**
     * 接入  消息推送
     */
    public function wxEvent(){
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = env('WX_TOKEN');
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){   //验证消息

            //接收数据
            $xml_str = file_get_contents("php://input");
            //写入日志
            Log::info($xml_str);
            
        }else{
            echo '';
        }  

       
    }
    /**
     * 获取access_token
     */
    public function getAccressToken(){
        $key="1234";
        $response = Redis::get($key);
        if(empty($response)){
            echo "没有缓存";
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".env('WX_APPID')."&secret=".env('WX_APPSEC')."";
            // echo $url;
            $response = file_get_contents($url);
            
            $response = json_decode($response,true);
            $response = $response['access_token'];
            Redis::set($key,$response);
            Redis::expire($key,3600);


        }
            echo $response;
       
    }
    /**
     * 关注并回复
     */
    public function checkAttention(){
        
        //接收数据
        $file = file_get_contents("php://input");
        Log::info("----------".$file);
        
        
    }                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     
}
