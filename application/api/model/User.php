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
        return true;
    }
}
