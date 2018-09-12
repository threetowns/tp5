<?php

namespace app\api\validate;

use think\Validate;

class Order extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'income_address' =>  'require',
        'pay_address' =>  'require',
        'number' =>  'require|gt:0',
        'fee' =>  'require',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'income_address.require' =>  '收款方不能为空',
        'pay_address.require' =>  '付款方不能为空',
        'number.require' =>  '转帐金额不能为空',
        'number.gt' =>  '转帐金额大于0',
        'fee.require' =>  '矿工费不能为空',
    ];

    protected $scene = [
        'pay'  =>  ['income_address','pay_address','number','fee']
    ];
}
