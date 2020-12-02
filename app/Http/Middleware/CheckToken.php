<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;
use App\Xcx_user;

class CheckToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {   
        $token = $request->get('token');
        $redis_key = 'XCX_token' . $token;
        $login_info = Redis::get($redis_key);
        if($login_info)
        {
            $uid = Xcx_user::where('openid',$login_info)->value('id');
            // dd($uid);
            $_SERVER['uid'] = $uid;
        }else{
            $response = [
                'errno' => 400003,
                'msg'   => "未授权"
            ];
            die(json_encode($response));
        }
        return $next($request);
    }
}
