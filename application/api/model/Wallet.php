<?php

namespace app\api\model;

use think\Model;

class Wallet extends Model
{
    public function getWallet($data = [])
    {
    	// DO: Integrity constraint violation: where clause is ambiguous
    	$where['w.uid'] = $data['uid'];
        $where['w.status'] = $data['status'];
    	$where['t.type'] = $data['type'];
        // $res = $this->where($data)->field('type,name,address,status,price')->select();
		$res = $this->alias('w')
                ->field('w.name, w.wtid type, w.status, w.num, w.address, t.pid, t.wid id, t.logo_icon, t.ticker_id, t.website_slug, t.symbol t_symbol')
                ->join('wallet_type t', 'w.wtid = t.wid', 'LEFT')
                ->order('t.wid ASC')
                ->where($where)->select();
        if ($res) {
            $res = $res->toArray();
        }
        return $res;
    }

    /**
     * uid
     * tid
     **/
    public function updateWalletStatusOne($data = []){
        $rs = $this->where($data)->find();
        if ($rs) {
            // 1. 如果有，更新状态：0->1；1->0
            // 2 如果无，新建一条记录，则状态为1
            $update['status'] = $rs['status'] === 0 ? 1 : 0;
            $result = $this->allowField(['status'])->update($update, ['uid' => $rs['uid'], 'wtid' => $rs['wtid']]);
            if($result){
                return true;
            }else{
                $this->error = '操作失败！';
                return false;
            }
        }else{
            // 没有则创建
            $where = $data;
            $where['status'] = 1;
            $where['num'] = 0;
            $result = $this->insert($where);
            if ($result) {
                return true;
            } else {
                $this->error = '操作失败！';
                return false;
            }
        }
    }
}
