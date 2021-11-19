<?php

namespace App\Controller;

use Mix\Vega\Context;
use App\Container\DB;

class User extends Base
{
    public $ctx;
    
    public function info(Context $ctx){//获取用户信息
        $this->ctx = $ctx;
        parent::firstload($ctx);
        $token = $this->token;
        
        if(!parent::get_query('userId')){//获取url参数中的用户id
            parent::error('参数为空');
        }
        $user_id = parent::get_query('userId');
        $this->user_id = $user_id;
        $user_info = DB::instance()->table('User_Pool')-> where('id = ?',addslashes($user_id))->first();//获取用户数据
        if(!isset($user_info->user)){//用户不存在
            parent::error('用户不存在');
        }
        $ctx -> JSONP(200,array(
            'UserId' => "$user_id",
            'Username' => $user_info->user,
            'Email' => $user_info->email,
            'Verified' => $user_info->verified,
            'Logo' => $user_info->logo
        ));
    }
    
    public function updateUsername(Context $ctx){//更改用户名
        $this->ctx = $ctx;
        parent::firstload($ctx);
        $this->token = $token;
        
        if(!parent::get_query('username')){//获取url参数中的用户名称
            parent::error('用户名为空');
        }
        $user = parent::get_query('username');
        $do1 = DB::instance()->table('User_Pool')->where('token = ?',$token)->update('user',addslashes($user));//更新用户数据
        if($do1){
            $ctx->JSONP(200,array(
                'Ok' => true,
                'Msg' => '修改成功'
                ));
        }else{
            parent::error('修改失败');
        }
    }
    
    public function updatePwd(Context $ctx){//更改用户密码
        $this->ctx = $ctx;
        parent::firstload($ctx);
        $token = $this->token;
        
        if(!parent::get_query('oldPwd') or !parent::get_query('pwd')){//判断url参数中的旧密码和新密码是否存在
            parent::error('新旧密码为空');
        }
        $old_pwd = parent::get_query('oldPwd');
        $new_pwd = parent::get_query('pwd');
        $do1 = DB::instance()->table('User_Pool')->where('token = ? AND pwd = ?',$token,md5($old_pwd))->update('pwd',md5($new_pwd));//通过旧密码和token获取用户数据
        if(!$do1){
            parent::error('修改失败');
        }
        $ctx->JSONP(200,array(
            'Ok' => true,
            'Msg' => '修改成功'
            ));
    }
    
    public function updateLogo(Context $ctx){//暂未开发
        //do something
        $this->ctx = $ctx;
        $ctx->JSONP(200,array(
            'Ok' => true,
            'Msg' => '尚未开发此功能'
        ));
    }
    
    public function getSyncState(Context $ctx){//此功能尚未开发
        $this->ctx = $ctx;
        if(!parent::check_logined(parent::get_query('token'))){
            parent::error('token错误');
        }
        $token = parent::get_query('token');
        $this->token = $token;
        $usn = parent::getusn($token);
        $ctx->JSONP(200,array(
            'LastSyncUsn' => 200096+rand(1,10),
            'LastSyncTime' => time()
        ));
    }
    
}