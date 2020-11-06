<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Log;
class WxController extends Controller
{
    /**
     * 接入  
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
            //把xml转化为对象或者数组
            $data = simplexml_load_string($xml_str,"SimpleXMLElement", LIBXML_NOCDATA);
            
           $xml = '<xml>
                        <ToUserName><![CDATA[toUser]]></ToUserName>
                        <FromUserName><![CDATA[fromUser]]></FromUserName>
                        <CreateTime>'.time().'</CreateTime>
                        <MsgType><![CDATA[text]]></MsgType>
                        <Content><![CDATA[你好]]></Content>
                </xml>';
                    echo $xml;
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
     * 推送事件
     */
    
}
