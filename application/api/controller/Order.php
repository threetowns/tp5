<?php

namespace app\api\controller;

use think\Controller;
use think\Request;

class Order extends Controller
{
    public function index()
    {
        return msg(0, null, '访问无效');
    }

    public function add(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        if($request->isPost()){
        	$this->param = $request->param();
            $this->token = isset($this->param['user_token']) ? $this->param['user_token'] : null;
            $this->checkToken();

            $data = [
              'income_address' => $this->param['income_address'],
              'pay_address' => $this->param['pay_address'],
              'number' => floatval($this->param['number']),
              'fee' => floatval($this->param['fee']),
              'tips' => $this->param['tips'],
              'user_token' =>  $this->param['user_token'],
              'pay_way' => $this->param['pay_way'],
              'pay_id' => $this->param['pay_id'],
              'type' => $this->param['type'],
              'uid' => $this->user['uid']
            ];

            $Order = model('Order');
            $incomeRs  = $Order->checkIncome($data);
            if(!$incomeRs){
            	return msg(0, null, $Order->getError());
            }
        	$payRs = $Order->checkPay($data);
        	if(!$payRs){
        		return msg(0, null, $Order->getError());
        	}
        	$orderRs = $Order->toPay($data, $payRs, $incomeRs);
    		if(!$orderRs){
    			return msg(0, null,  $Order->getError());
    		}else{
    			return msg(1, null, '转帐成功！');
    		}
        }else{
            return msg(0, null, '非法请求');
        }
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
