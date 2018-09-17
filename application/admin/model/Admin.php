<?php

namespace app\admin\model;

use think\Model;

class Admin extends Model
{
    public function getLogin($data){
    	$validate = Validate($this->name);
        if (!$validate->scene('login')->check($data)) {
            $this->error = $validate->getError();
            return false;
        }

        $where['username'] = $data['username'];
        $where['password'] = md5('imtoken'.md5($data['password']));
        $rs = $this->where($where)->find();
        if($rs){
        	return $rs;
        }else{
        	$this->error = '帐号或密码有误！';
            return false;
        }
    }
}
