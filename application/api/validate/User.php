<?php

namespace app\api\validate;

use think\Validate;

class User extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     * 帮助文档： https://www.kancloud.cn/manual/thinkphp5_1/354102
     * @var array
     */ 
    protected $rule = [
        'username' => 'require',
        'password' => 'require|min:8|max:20',
        'memory_words' => 'require'
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */ 
    protected $message = [
        'username.require' => '身份名不能为空',
        'password.require' => '密码不能为空',
        'password.min' => '密码不少于8位字符',
        'password.max' => '密码不大于20位字符',
        'memory_words.require' => '助记词不能为空'
    ];

    /**
     * 定义验证场景
     * 格式：'字段名.规则名' =>  '错误信息'
     * 帮助文档： https://www.kancloud.cn/manual/thinkphp5_1/354104
     * @var array
     */ 
    protected $scene = [
        'join'  =>  ['username','password'],
        'memory_words'   =>  ['memory_words']
    ];
}
