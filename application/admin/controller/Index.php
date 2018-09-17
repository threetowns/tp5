<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;

class Index extends Controller
{
    public function index()
    {
        return msg(0, null, '访问无效');
    }

    /*
     * 登录
     */
    public function login(Request $request){
    	header('Access-Control-Allow-Origin: *');
        if($request->isPost()){
        	$this->param = $request->param();
        	$Admin = model('Admin');
            $ret  = $Admin->getLogin($this->param);
            if($ret){
            	// if success, we will return user token
            	unset($ret['password']);
	        	$token = md5(microtime());
	        	cache($token, json_encode($ret), 60 * 60 * 2); // 两小时过期
	            $data = [
	              'token' => $token,
	              'username'  => $ret['username']
	            ];
            	return msg(1, $data, '登录成功');
            }else{
            	return msg(0, null, $Admin->getError());
            }
        }else{
        	return msg(0, null, '非法请求');
        }
    }
}
