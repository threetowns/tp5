<?php

namespace app\api\controller;

use think\Controller;
use think\Request;

class Common extends Controller
{
    public function _initialize()
    {
        parent::_initialize();
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, sessionId, Authorization");
        $this->request = Request::instance();
        $this->param = $this->request->param();
        $this->token = isset($this->request->header()['Authorization']) ? $this->request->header()['Authorization'] : null;
        $this->checkToken();
    }

    public function checkToken()
    {
        $this->user = json_decode(cache($this->token), true);
        dump($this->user);
        if (!$this->user) {
            exit(json_encode(['code'=>101, 'error'=>'请重新登录']));
        }
        //每次访问自动续命
        cache($this->token, json_encode($this->user), 0); // convention.php expire为0永久有效
        return $this->user;
    }
}
