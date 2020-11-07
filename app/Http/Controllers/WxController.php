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
            $obj = simplexml_load_string($xml,"SimpleXMLElement", LIBXML_NOCDATA);
            if($obj->MsgType=='event'){
                if($obj->Event == "subscribe"){
                   $content = "谢谢你的关注";
                   
                   $info = $this->checkText($obj,$content);

                }
           
            }
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
 
    public function checkText($obj,$content){
        $ToUserName = $obj->FromUserName;
        $FromUserName = $obj->ToUserName;
        $CreateTime = time();
        $MsgType = 'text';
        $xml = "
                <xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[你好]]></Content>
                </xml>
        ";
        $info = sprintf($xml,$ToUserName,$FromUserName,$CreateTime,$MsgType,$content);
        log::info($info);
        echo $info; 
    }
}
