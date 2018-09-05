<?php

namespace app\api\controller;

use think\Controller;
use think\Request;

class Wallet extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        if($request->isPost()){
            // 1. 获取传入参数
            $this->param = $request->param();
            // 2. user_token, TODO: 优化建议 headers('user_token')
            $this->token = isset($this->param['user_token']) ? $this->param['user_token'] : null;
            // 3. check 用户token
            $this->checkToken();

            $this->model = model('Wallet');
            // 业务逻辑 - 获取当前用户的 钱包信息
            $query['uid'] = $this->user['uid'];
            $query['status'] = input('status') ? input('status') : 1;
            $query['type'] = $this->param['wallet_type'] ? $this->param['wallet_type'] : 1;
            $ret  = $this->model->getWallet($query);
            if($ret){
                return msg(1, $ret, '操作成功');
            }else{
                return msg(0, null, $this->model->getError());
            }
        }else{
            return msg(0, null, '非法请求');
        }
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //        
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        if($request->isPost()){
            // 1. 获取传入参数
            $this->param = $request->param();
            // 2. 处理查询条件
            $where = [];
            // 2.1 keyword 查询 symbol或address
            $queryKeyword = isset($this->param['keyword']) ? $this->param['keyword'] : null;
            if($queryKeyword){
                $where[] = ['symbol|address', 'like', '%'.$queryKeyword. '%'];
            }
            // 2.2 状态 0，默认不显示；1，显示
            $queryStatus = isset($this->param['status']) ? $this->param['status'] : null;
            if (!!$queryStatus) {
                $where[] = ['status', '=', $queryStatus];
            }

            $res = db('wallet_type')
                    ->field('address, symbol, logo_icon')
                    ->where($where)
                    ->select();
            if($res){
                return msg(1, $res, '操作成功');
            }else{
                return msg(0, [], '暂无数据');
            }
        }else{
            return msg(0, null, '非法请求');
        }
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }

    public function checkToken()
    {
        $this->user = json_decode(cache($this->token), true);
        if (!$this->user) {
            exit(json_encode(['code'=>101, 'error'=>'请重新登录']));
        }
        //每次访问自动续命
        cache($this->token, json_encode($this->user), 0);
    }
}
