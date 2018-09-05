<?php

namespace app\api\model;

use think\Model;

class Wallet extends Model
{
    public function getWallet($data = [])
    {
    	// DO: Integrity constraint violation: where clause is ambiguous
    	if($data['uid']){ $where['w.uid'] = $data['uid']; }
    	if($data['type']){ $where['t.type'] = $data['type']; }
    	if($data['status']){ $where['t.status'] = $data['status']; }
        // $res = $this->where($data)->field('type,name,address,status,price')->select();
		$res = $this->alias('w')
                ->field('w.name, w.wtid type, w.status, w.num, w.address, t.logo_icon, t.ticker_id, t.symbol t_symbol')
                ->join('wallet_type t', 'w.wtid = t.wid', 'LEFT')
                ->order('t.wid ASC')
                ->where($where)->select();
        if ($res) {
            $res = $res->toArray();
        }
        return $res;
    }
}
