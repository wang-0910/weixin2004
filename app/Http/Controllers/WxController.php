<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Log;
use GuzzleHttp\Client;
use App\UserModel;
use App\Media;
class WxController extends Controller
{
    /**
     * 接入
     */
    public function index(){
        $this->wxEvent();//接入
        $this->create_menu();//菜单
    }
    /**
     *  消息推送
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
            $access_token = $this->getAccessToken();
            //接收数据
            $xml_str = file_get_contents("php://input");
           // dd($xml_str);
            //写入日志
            Log::info($xml_str);

            $obj = simplexml_load_string($xml_str,"SimpleXMLElement", LIBXML_NOCDATA);
        //    dd($obj);die;
            //天气
             $data = [];
            if($obj->MsgType=="text"){
               // echo '111';die;
                if($obj->Content=="天气"){
                    $content = $this->weather();
                    $this->checkText($obj,$content);
                }
            } else if ($obj->MsgType=="image"){
               
                $data[] = [
                    "openid" =>$obj->ToUserName,
                    "add_time"=>$obj->CreateTime,
                    "media_url"=>$obj->PicUrl,
                     "msg_id" => $obj->MsgId,
                     "media_id"=>$obj->MediaId,
                     "media_type"=>$obj->MsgType
                ];
                    $this->material($obj->MediaId);
                    $media = Media::insert($data);
            }else if($obj->MsgType=='video'){
                $data[] = [
                    "openid" => $obj->ToUserName,
                    "add_time"=>$obj->CreateTime,
                    "media_type"=>$obj->MsgType,
                    "media_id"=>$obj->MediaId,
                    'thumb' => $obj->ThumbMediaId,
                    'msg_id'=>$obj->MsgId
                ];
                    $this->material($obj->MediaId);
                    $media = Media::insert($data);
            }else if($obj->MsgType=='voice'){
                $data[] = [
                    "openid" => $obj->ToUserName,
                    "add_time"=>$obj->CreateTime,
                    "media_type"=>$obj->MsgType,
                    "media_id"=>$obj->MediaId,
                    'msg_id'=>$obj->MsgId,
                    'format'=>$obj->Format,
                    'recognition' => $obj->Recognition

                ];
                    $this->material($obj->MediaId);
                    $media = Media::insert($data);
            }else if($obj->Event=="CLICK"){
                if($obj->EventKey=="V1001_TODAY_QQ"){
                    $key = '1233455';
                    $openid = $obj->ToUserName;
                    $slsmember = Redis::sismember($key,$openid);
                    if($slsmember=='1'){
                        $content = "已签到";
                        $this->checkText($obj,$content);
                    }else{
                        $content = "签到成功";
                        Redis::sAdd($key,$openid);
                        $this->checkText($obj,$content);
                    }
                }
            }else if($obj->MsgType='text'){
                //  dd($content);
                $key = '6bcb5167eff3c1f78fc5c97bdc67d265';
                $url = "http://api.tianapi.com/txapi/pinyin/index?key=".$key."&text=".$content;
                $data = file_get_contents($url);
                $res = json_decode($data,true);
                $content = "";
                if($res['code'] == 200){ //判断状态码
                $content = $res['newslist'][0]['pinyin'];
                // dd($content);
                $data = [
                    'Content' => $obj->Content,
                    'MsgId' => $obj->MsgId
                ];
                Redis::sAdd($key,$data);
                echo $this->checkText($obj,$content);
            }else{	
                echo "返回错误，状态消息：".$res['msg'];
                }
            }
        
    








            $openid = $obj->FromUserName;//获取发送方的 openid
            $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=" . $access_token . "&openid=" . $openid . "&lang=zh_CN";
            // Log::info($url);
           
            $user_model = new UserModel();
            if($obj->MsgType=='event'){
                if($obj->Event == "subscribe"){
                    $user = $user_model->where('openid','=',$openid)->first();
                   
                    if($user){
                        $content = "欢迎回来";
                        $info = $this->checkText($obj,$content);
                    }else{
                         $user = json_decode($this->http_get($url),true);
                        $content = "谢谢你的关注";
                        $info = $this->checkText($obj,$content);
                        $data = [
                            "subscribe" => $user['subscribe'],
                            "openid" => $user['openid'],
                            "nickname" => $user['nickname'],
                            "sex" => $user['sex'],
                            "city" => $user['city'],
                            "country" => $user['country'],
                            "province" => $user['province'],
                            "language" => $user['language'],
                            "headimgurl" => $user['headimgurl'],
                            "subscribe_time" => $user['subscribe_time'],
                            "subscribe_scene" => $user['subscribe_scene'],
                        ];
                        $user_model->insert($data);
                    }

                   
                }
                
            }
        }else{
            echo '';
        }  

       
    }

    /**
     * 新增素材
     */

     public function material($media_id){
        $access_token = $this->getAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$access_token.'&media_id='.$media_id;
        $client = new client();
        $response = $client->get($url); 
        // 得到头部信息
        // 从头部信息中取出文件名 将文件名处理为字符串
        $file_name = $response->getHeader('Content-disposition')[0];

        $file_type = 'static/'.$response->getHeader('Content-Type')[0];
//        Log::info("=====file_type=====".$file_type);
        // 判断有无 文件夹 没有 则创建多层文件夹
        $adddir=$file_type.date("/Ymd/",time());
        if(!is_dir($adddir)){
            mkdir($adddir, 0777,true);
            chmod($adddir, 0777);
        }
        $file_name = ltrim($file_name,"attachment; filename=\"");
        $file_name = rtrim($file_name,'"');
        $file_path = $adddir.$file_name;
        $client->get($url,['save_to'=>$file_path]);

     }
     

    /** 
     * 天气
     */
    public function weather(){
        $url = "http://api.k780.com:88/?app=weather.future&weaid=beijing&&appkey=10003&sign=b59bc3ef6191eb9f747dd4e83c99f2a4&format=json";     
        // $weather = file_get_contents($url);
        $weather = file_get_contents($url);
        $weather = json_decode($weather,true);  
       //dd($weather);

        if($weather['success']==1){
            $content = "";
            foreach($weather['result'] as $k=>$v){
                $content .= "\n"."地区:" . $v['citynm'] .","."日期:" . $v['days'] . $v['week'] .","."温度:" . $v['temperature'] .","."风速:" . $v['winp'] .","."天气:" . $v['weather'];
            }
            return $content;
        }

        
    }



    /**
     * 获取access_token
     */
    public function getAccessToken(){
        $key = 'wx:access_token';
        
        //检查是否有token
        $token = Redis::get($key);
        if($token){
        return  $token;
        }else{
        echo "无缓存";
        
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".env('WX_APPID')."&secret=".env('WX_APPSEC')."";
        $response = file_get_contents($url);
        //echo $response;
        $data = json_decode($response,true);
        $token=$data['access_token'];
        //保存redis
        
        Redis::set($key,$token);
        Redis::expire($key,3600);
        
        
        }
        return $token;
        
        
        
        
        }
 
    /**
     * 文本消息
     */
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
                    <Content><![CDATA[".$content."]]></Content>
                </xml>
        ";
        $info = sprintf($xml,$ToUserName,$FromUserName,$CreateTime,$MsgType,$content);
        log::info($info);
        echo $info; 
    }

/**
 * 自定义菜单
 */
   public function create_menu(){
       //获取token
       $access_token = $this->getAccessToken();

        $url =  "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;
        $menu = [
            "button"=>
            [
                [
                    "type"=>"click",
                    "name"=> "签到",
                    "key"=>"V1001_TODAY_QQ"
                ],

                [
                    "name" => "菜单",
                    "sub_button" => 
                [
                    [
                        "type" => "view",
                        "name" => "视频",
                        "url" =>  "http://v.qq.com"
                    ], 

                    [
                        "type" => "view",
                        "name" => "音乐",
                        "url" => "https://music.163.com"
                    ]
                ]
                ]
            ]
    ];
        // echo $menu;
        $client = new Client();
        $response = $client->request('POST',$url,[
            'verify' =>false,
            'body'=>json_encode($menu,JSON_UNESCAPED_UNICODE)
        ]);
        

   }


  

  /**
   * 过滤http协议
   */
   public function http_get($url){
    //        Log::info("--------------------123");
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);//向那个url地址上面发送
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);//设置发送http请求时需不需要证书
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设置发送成功后要不要输出1 不输出，0输出
            $output = curl_exec($ch);//执行
            curl_close($ch);    //关闭
            return $output;
     }
    

    
     

}
