<?php

namespace app\api\model;

use think\Model;
use think\Db;

class Order extends Model
{
    public function checkIncome($data){
    	// dump($data);
    	$validate = Validate($this->name);
        if (!$validate->scene('pay')->check($data)) {
            $this->error = $validate->getError();
            return false;
        }
        // 查询收款地址(钱包地址)
        $where['wtid'] = $data['pay_id'];
        $where['address'] = $data['income_address'];
        $rs = model('Wallet')->where($where)->find();
        if(!$rs){
        	$this->error = '收款地址不存在';
        	return false;
        }else{
        	return $rs;
        }
    }

    public function checkPay($data){
    	// 查询付款方
    	$where['uid'] = $data['uid'];
    	$where['address'] = $data['pay_address'];
    	$where['wtid'] = $data['pay_id'];
    	$rs = model('Wallet')->where($where)->find();
    	if($rs){
    		return $rs;
    	}else{
    		$this->error = '付款方帐户异常';
    		return false;
    	}
    }

    public function setIncome($where = [], $num){
    	$this->wallet = model('Wallet');
        $rs = $this->wallet->where($where)->find();
        if ($rs) {
            return $rs;
        }else{
            // 没有则创建
            $where['status'] = 0;
            $where['num'] = $num;
            $result = $this->insert($where);
            if (!$result) {
            	$this->wallet->rollback();
                $this->error = '操作失败！';
                return false;
            }
        }
    }

    public function toPay($data, $payer, $incomeer){
    	// 开始交易
    	$this->wallet = model('Wallet');
    	$this->wallet->startTrans();
    	/* 业务逻辑说明：
    	 * 1. 矿工费： 从付款方 ETH里扣
    	 * 2. 付款与收款：
    	 *    2.1 当收款方没有该币种，则创建该币种且增加数量
    	 *    2.2 当收款方有该币种，则增加数量
    	 */
		// dump($data);
		  //   	dump($payer);
		  //   	dump($incomeer);

    	$num =  $data['number'];
    	$fee =  $data['fee'];

    	$wherePay['uid'] = $whereFee['uid'] = $payer['uid'];
    	$whereFee['wtid'] = $payer['wtid'];
    	$wherePay['wtid'] = $data['type'];
    	// A. 矿工费（前面判断帐户后）
    	$payFee = floatval($payer['num']) - $fee;
    	if($payFee < 0){
    		$this->error = '您的钱包余额不足';
			return false;
		}
		
		// B. 付款方:: 数量是否中足够支付
    	$payRs = $this->wallet->where($wherePay)->find(); // 返回当前结果集
    	if(!$payRs){
    		return msg(0, null, '帐户不存在');
    	}
    	$payNum =  $num;
    	if($payNum<0){
    		$this->error = '当前帐户余额不足';
    		return false;
    	}

    	if($payRs['wtid'] == $payer['wtid']){ // 如果钱包类型与付款类型一样，则一次性扣
    		if($payNum - $fee<0){
    			$this->error = '您的钱包余额不足';
    			return false;
    		}
    		$payNum = $payNum + $fee;
    		$order['block'] = mt_rand(100000,999999); // 区块
    	}else{
			// A.1 扣除矿工费
			$feeRs = $this->wallet->where($whereFee)->setDec('num', $fee);
			if(!$feeRs){
				$this->wallet->rollback();
				$this->error = '扣除矿工费失败';
				return false;
			}
    	}
    	// 付款方:: 扣款
    	$resultPay = $this->wallet->where($wherePay)->setDec('num', $payNum);
    	if(!$resultPay){
    		$this->wallet->rollback();
    		$this->error = '转帐失败1';
    		return false;
    	}
		// C. 收款方:: 查询当前类型帐户；有则增加交易数量；没有则减少交易数量
		$whereIncome['uid'] = $incomeer['uid'];
		$whereIncome['wtid'] = $payRs['wtid'];
 		$incomeRs = $this->wallet->where($whereIncome)->find();
 		if($incomeRs){
 			// 有则更新
 			$incomeNum = $num;
 			$resultIncome = $this->wallet->where($whereIncome)->setInc('num', $incomeNum);
 			if(!$resultIncome){
 				$this->wallet->rollback();
 				$this->error = '转帐失败2';
    			return false;
 			}
 		}else{
 			// 没有资产则创建
 			$whereIncome['status'] = 0;
 			$whereIncome['num'] = $num;
 			$resultIncome = $this->wallet->insert($whereIncome);
 			if (!$resultIncome) {
 				$this->wallet->rollback();
 				$this->error = '转帐失败3';
    			return false;
 			}
 		}
    	// D. 生成订单
		$order['wtid'] = $payRs['wtid'];
		$order['number'] = $num;
		$order['fee'] = $fee;
		$order['to_uid'] = $incomeer['uid'];
		$order['to_address'] = $incomeer['address'];
		$order['from_uid'] = $payer['uid'];
		$order['from_address'] = $payer['address'];
		$order['tips'] = trim($data['tips']);
		$order['state'] = 1;
		$order['create_time'] = date("Y-m-d H:i:s" ,time());

		$this->startTrans();
		$resultOrder = $this->insert($order);
		if(!$resultOrder){
    		$this->wallet->rollback();
    		$this->error = '转帐失败4';
    		return false;
    	}
    	$this->wallet->commit();
    	$this->commit();

    	return true;
    }

    public function getCount($data){
    	$validate = Validate($this->name);
        if (!$validate->scene('count')->check($data)) {
            $this->error = $validate->getError();
            return false;
        }
    	$days = 7;
    	$sql = "select dayTb.cday 'datetime' 
		    	,IFNULL(tNumTb2.num,0)-IFNULL(tNumTb.num,0) 'num' FROM (
					SELECT @cdate := DATE_ADD(@cdate, INTERVAL +1 DAY) cday
		 			FROM( SELECT @cdate := DATE_SUB(CURDATE(), INTERVAL ". $days ." DAY) FROM im_order limit ". $days .") t0
		  			WHERE date(@cdate) <= DATE_ADD(CURDATE(), INTERVAL -1 DAY)
				) dayTb
				LEFT JOIN(
					select LEFT(io.create_time,10) as cday,sum(io.number) as num from im_order io
					where io.wtid = " . $data['wtid'] ." and io.from_uid = ". $data['uid'] ."
					GROUP BY cday
				) tNumTb ON tNumTb.cday = dayTb.cday
				LEFT JOIN(
					select LEFT(io.create_time,10) as cday,sum(io.number) as num from im_order io
					where io.wtid = " . $data['wtid'] ." and io.to_uid = ". $data['uid'] ." 
					GROUP BY cday
				) tNumTb2 ON tNumTb2.cday = dayTb.cday";
    	$rs = Db::query($sql);
    	if($rs){
    		return $rs;
    	}
    }

    public function getOrder($data){
    	$validate = Validate($this->name);
        if (!$validate->scene('count')->check($data)) {
            $this->error = $validate->getError();
            return false;
        }

        $where['to_uid|from_uid'] = $data['uid'];
        $where['wtid'] = $data['wtid'];
        $rs = $this->where($where)->order('create_time', 'desc')->select();
    	return $rs;
    }
}
