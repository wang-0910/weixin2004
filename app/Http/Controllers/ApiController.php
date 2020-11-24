<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Xcx_user;
use App\GoodsModel;
class ApiController extends Controller
{
    public function test(){
        $goods_info = [
            'goods_id' => '1111',
            'goods_name' => 'cpb',
            'price' => '840'
        ];
        // echo json_encode($goods_info);
    }
    public function login(){
        $code=Request()->code;
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=".env('WX_XCX_APPID')."&secret=".env('WX_XCX_SECRET')."&js_code=".$code."&grant_type=authorization_code";
        $data = file_get_contents($url);
        $res = json_decode($data,true); 
        

        if(isset($res['errcode'])){
        
            $response = [
                'errno' => 50001,
                'msg' => '登录失败',
            ];
        }else{
            if(empty(xcx_user::where('openid',$res['openid'])->first())){
                xcx_user::insert(['openid'=>$res['openid']]);
            }
            $token = sha1($res['openid'].$res['session_key'].mt_rand(0,999999));
            $redis_key = 'XCX_token'.$token;
            Redis::set($redis_key,time());
            Redis::expire($redis_key,7200);
            $response = [
                'errno' => 0,
                'msg' => 'ok',
                'data' => [
                    'token' => $token
                ]
            ];
        }
        return $response;

    }

 
    public function goods(){
        $goods = GoodsModel::select('goods_id','goods_name','shop_price','goods_img','goods_number')->limit(10)->get()->toArray();
        // return json_encode($goods,256);
        $response = [
            'errno'=>0,
            'msg'=>'ok',
            'data' =>[
                'list'=>$goods
            ]
        ];
        // echo json_encode($response);
        return $response;
    }

    public function goodsList(Request $request){
        $page_size = $request->get('ps');
        $goods = GoodsModel::select('goods_id','goods_name','shop_price','goods_img')->paginate($page_size); 
        // echo $goods;
        $response = [
            'errno' => 0,
            'msg' => 'ok',
            'data' =>[
               'list' => $goods->items()
            ]
        ];
        // echo json_encode($response);
        return $response;
    }


    //商品详情页

    public function goodsDetail(Request $request){
        $goods_id = request()->get('goods_id');
        // dd($goods_id);
        $goods = GoodsModel::where('goods_id',$goods_id)->first()->toArray();
        // dd($goods);
        $response = [
            'errno' => 0,
            'msg' => 'ok',
            'data' =>[
               'list' => $goods
            ]
        ];
        return $response;
    }

    public function xcxlogin(){
        
    }
}
