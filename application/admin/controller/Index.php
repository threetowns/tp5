<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;

class Index extends Controller
{
    public function index()
    {
        return msg(0, null, '访问无效');
    }

    /*
     * 登录
     */
    public function login(Request $request){
    	header('Access-Control-Allow-Origin: *');
        if($request->isPost()){
        	$this->param = $request->param();
        	$Admin = model('Admin');
            $ret  = $Admin->getLogin($this->param);
            if($ret){
            	// if success, we will return user token
            	unset($ret['password']);
	        	$token = md5(microtime());
	        	cache($token, json_encode($ret), 60 * 60 * 2); // 两小时过期
	            $data = [
	              'token' => $token,
	              'username'  => $ret['username']
	            ];
            	return msg(1, $data, '登录成功');
            }else{
            	return msg(0, null, $Admin->getError());
            }
        }else{
        	return msg(0, null, '非法请求');
        }
    }

    /*
     * 订单列表
     */
    public function order(Request $request){
    	header('Access-Control-Allow-Origin: *');
        if($request->isPost()){
        	$Order = db('Order');
        	$data = $request->param();

            //时间搜索
            // if(isset($data['starttime']) && !empty($data['starttime'])){
            //     if(!isset($data['endtime']) || empty($data['endtime'])){
            //         $data['endtime'] = date('Y-m-d H:i:s',time());
            //     }
            //     $where['o.create_time'] = array('between time',array($data['starttime'],$data['endtime']));
            //     // $where[] = ['o.create_time', 'between time', [$data['starttime'],$data['endtime']];
            // }

        	// where
        	$rows = isset($data['rows']) && is_numeric($data['rows']) ? intval($data['rows']) : 10;
    		$page = isset($data['page']) && is_numeric($data['page']) ? intval($data['page']) : 1;
            $sql = "select o.*,
                (SELECT username from im_user where uid = to_uid) to_username,
                (SELECT username from im_user where uid = from_uid) from_username,
                (SELECT symbol from im_wallet_type where wid = wtid) symbol from im_order o";

            $where=' where 1=1';
            // 用户
            if(isset($data['username']) && !empty($data['username'])){
                $where .= " and (
                    o.to_uid = (select u.uid from im_user u where u.username like '%". $data['username'] ."%')
                    or 
                    o.from_uid = (select u.uid from im_user u where u.username like '%". $data['username'] ."%'))";
            }
            // 订单类型
            if(isset($data['type']) && !empty($data['type'])){
                $where .= " and o.wtid = ". intval($data['type']);
            }
            // 付款/收款地址
            if(isset($data['address']) && !empty($data['address'])){
                $where .= " and (
                    o.from_address like '%" . $data['address']. "%'
                    or
                    o.to_address like '%" . $data['address']. "%')";
            }

            $startRow = ($page - 1) * $rows;
            $ret = Db::query($sql . $where . " limit " . $startRow ."," .$rows);

        	// count
        	$total = Db::query("select count(*) as count from im_order o" . $where);
        	$rs['data'] = $ret;
        	$rs['total'] = $total[0]['count'];
        	$rs['page'] = $page;
        	return msg(1, $rs, 'ok');
        }else{
        	return msg(0, null, '非法请求');
        }
    }

    /*
     * 钱包列表
     */
    public function wallet(Request $request){
    	header('Access-Control-Allow-Origin: *');
        if($request->isPost()){
        	$Wallet = db('Wallet');
        	$data = $request->param();

        	// $where
        	$where = [];
        	if(isset($data['address']) && !empty($data['address'])){
        		$where[] = ['w.address', 'like', '%'.$data['address']. '%'];
        	}
        	if(isset($data['type']) && !empty($data['type'])){
        		$where[] = ['w.wtid', '=', intval($data['type'])];
        	}
        	if(isset($data['username']) && !empty($data['username'])){
        		$where[] = ['u.username', 'like', '%'.$data['username']. '%'];
        	}

        	// page
        	$rows = isset($data['rows']) && is_numeric($data['rows']) ? intval($data['rows']) : 10;
    		$page = isset($data['page']) && is_numeric($data['page']) ? intval($data['page']) : 1;

    		$rs = $Wallet->alias('w')
    				->field('w.*, u.username, wt.symbol, wt.logo_icon, wt.fullname')
    				->join('user u','w.uid=u.uid')
    				->join('wallet_type wt','w.wtid=wt.wid')
    				->limit($rows)
    				->page($page)
    				->where($where)
    				->select();
    		$total = $Wallet->where($where)->count();
    		$res['total'] = $total;
    		$res['page'] = $page;
   			$res['data'] = $rs;

    		return msg(1, $res, 'ok');
        }else{
        	return msg(0, null, '非法请求');
        }
    }

    /*
     * 修改钱包
     */
    public function wallet_edit(Request $request){
    	header('Access-Control-Allow-Origin: *');
        if($request->isPost()){
        	$Wallet = db('Wallet');
        	$this->param = $request->param();

        	// checkToken
        	$this->token = isset($this->param['admin_token']) ? $this->param['admin_token'] : null;
        	$this->checkToken();

        	// ID
        	$id = isset($this->param['id']) && is_numeric($this->param['id']) ? intval($this->param['id']) : null;
        	if(!$id){
        		return msg(0, null, '参数有误');
        	}
        	// num
        	$num = isset($this->param['num']) && !empty($this->param['num']) ? floatval($this->param['num']): null;
        	if(!$num || $num<0){
        		return msg(0, null, '参数有误');
        	}
        	$where['wid'] = $id;
        	$rs = $Wallet->where($where)->find();
        	if(!$rs){
        		return msg(0, null, '参数有误');
        	}

        	$save['wid'] = $rs['wid'];
        	$save['num'] = $num;
        	$result = $Wallet->where('wid',$rs['wid'])->update($save);
        	if($result){
        		return msg(1, null, '更新成功！');
        	}else{
        		return msg(0, null, '操作失败！');
        	}

        }else{
        	return msg(0, null, '非法请求');
        }
    }

    /*
     * 币种
     */
    public function currency(Request $request){
    	header('Access-Control-Allow-Origin: *');
        if($request->isPost()){
        	$data = $request->param();
        	// page
        	$rows = isset($data['rows']) && is_numeric($data['rows']) ? intval($data['rows']) : 10;
    		$page = isset($data['page']) && is_numeric($data['page']) ? intval($data['page']) : 1;
    		// where
    		$where = [];
    		if(isset($data['type']) && !empty($data['type'])){
        		$where[] = ['wid', '=', intval($data['type'])];
        	}
        	if(isset($data['status']) && !empty($data['status'] || $data['status'] == 0)){
        		$where[] = ['status', '=', intval($data['status'])];
        	}

        	$WalletType = db('wallet_type');
    		$rs = $WalletType->alias('w')
    				->limit($rows)
    				->page($page)
    				->where($where)
    				->select();
    		$total = $WalletType->where($where)->count();
    		$res['total'] = $total;
    		$res['page'] = $page;
   			$res['data'] = $rs;

    		return msg(1, $res, 'ok');
        }else{
        	return msg(0, null, '非法请求');
        }
    }

    /**
	 * 订单检索字段处理
	 */
	public function checkFilterData($data)
	{
		
		$where = [];
		$getdata = array();

		//订单hash
		if(isset($data['hash']) && !empty($data['hash'])){
			$where[] = ['o.hash', 'like', '%'.$data['address']. '%'];
		}
		// 订单类型
		if(isset($data['type']) && !empty($data['type'])){
			$where[] = ['o.wtid', '=', $data['type']];
		}
		// 付款/收款地址
		if(isset($data['address']) && !empty($data['address'])){
			$where[] = ['o.from_address|o.to_address', 'like','%'.$data['address'].'%'];
		}
		// 用户
		if(isset($data['username']) && !empty($data['username'])){
			$where[] = ['u.username', 'like', '%'.$data['username'].'%'];
		}
		//时间搜索
		if(isset($data['starttime']) && !empty($data['starttime'])){
			if(!isset($data['endtime']) || empty($data['endtime'])){
				$data['endtime'] = date('Y-m-d H:i:s',time());
			}
			$where['o.create_time'] = array('between time',array($data['starttime'],$data['endtime']));
			// $where[] = ['o.create_time', 'between time', [$data['starttime'],$data['endtime']];
		}

		$res['where'] = $where;
		return $res;
	}

	/*
	 * 管理员登录token
	 */
	public function checkToken()
    {
        $this->admin = json_decode(cache($this->token), true);
        if (!$this->admin) {
            exit(json_encode(['code'=>101, 'error'=>'请重新登录']));
        }
        //每次访问自动续命
        cache($this->token, json_encode($this->admin), 3600 *2);
    }
}
