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
        $result = $this->insert($data);
        if ($result) {
            return $result;
        } else {
            $this->error = '创建失败！';
            return false;
        }
    }
}
