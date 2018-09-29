<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use think\Db;

class News extends Controller
{
    public function index()
    {
        return msg(0, null, '访问无效');
    }

    public function read(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        if($request->isPost()){
            $data = $request->param();
            
            $where = [];
			if(isset($data['isfocus']) && !empty($data['isfocus'])){
        		$where[] = ['isfocus', '=', $data['isfocus']];
        	}else{
        		$where[] = ['isfocus', '=', 0];
        	}

        	$rows = isset($data['rows']) && is_numeric($data['rows']) ? intval($data['rows']) : 10;
            $page = isset($data['page']) && is_numeric($data['page']) ? intval($data['page']) : 1;

            $rs = db('News')
            		->field('title, thumb, create_time, from_id id')
            		->where($where)
            		->limit($rows)
    				->page($page)
    				->order('create_time desc')
    				->select();
    		$res['data'] = $rs;
    		return msg(1, $res, '查询成功');
        }else{
            return msg(0, null, '访问无效');
        }
    }

    public function details(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        if($request->isPost()){
            $data = $request->param();

            $where = [
                'from_id' => isset($data['id']) ? $data['id'] : null,
            ];
            $ret = db('News')->where($where)->find();

            if($ret){
            	$content = htmlspecialchars_decode($ret['content']);
            	$ret['content'] = str_replace("\\r\\n",'', $content);
                return msg(1, $ret, '查询成功');
            }else{
                return msg(0, null, '查询失败');
            }
        }else{
            return msg(0, null, '访问无效');
        }
    }
}
