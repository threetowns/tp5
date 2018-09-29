<?php

namespace app\api\controller;

use think\Controller;
use think\Request;

class User extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //
        return msg(0, null, '访问无效');
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        if($request->isPost()){
            $data = [
              'username' => $request->param('username'),
              'password' => $request->param('password'),
              'password_tips' => $request->param('password_tips')
            ];
            // return json_encode($data);

            $User = model('User');
            $ret  = $User->getJoin($data);
            if($ret){
                return msg(1, $ret, '创建成功！');
            }else{
                return msg(0, null, $User->getError());
            }
        }else{
            return msg(0, null, '非法请求');
        }
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        if($request->isPost()){
            $data = [
              'memory_words' => $request->param('memory_words'),
              'unique_key' => $request->param('unique_key'),
              'username' => $request->param('username')
            ];
            $User = model('User');
            $ret  = $User->getWords($data);
            if($ret){
                return msg(1, null, '更新助记词成功！');
            }else{
                return msg(0, null, $User->getError());
            }
        }else{
            return msg(0, null, '非法请求');
        }
    }

}
