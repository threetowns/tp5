<?php

namespace app\api\model;

use think\Model;

class Wallet extends Model
{
    public function getWallet($data = [])
    {

        $res = $this->where($data)->field('type,name,address,state,price')->select();
        if ($res) {
            $res = $res->toArray();
        }
        return $res;
    }
}
