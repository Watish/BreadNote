<?php

namespace App\Controller;

use Mix\Vega\Context;
use App\Container\DB;

class User extends Base
{
    public $ctx;
    
    public function info(Context $ctx){
        $this->ctx = $ctx;
        if(!parent::get_query('userId')){
            parent::error('参数为空');
        }
        if(!parent::get_query('token')){
            parent::error('token为空');
        }
        if(!parent::check_logined(parent::get_query('token'))){
            parent::error('token错误');
        }
        $this->token = parent::get_query('token');
        $user_id = parent::get_query('userId');
        $this->user_id = $user_id;
        $user_info = DB::instance()->table('User_Pool')-> where('id = ?',addslashes($user_id))->first();
        if(!isset($user_info->user)){
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
    
    public function updateUsername(Context $ctx){
        $this->ctx = $ctx;
        if(!parent::get_query('username')){
            parent::error('用户名为空');
        }
        if(!parent::check_logined(parent::get_query('token'))){
            parent::error('token错误');
        }
        $token = parent::get_query('token');
        $user = parent::get_query('username');
        $do1 = DB::instance()->table('User_Pool')->where('token = ?',$token)->update('user',addslashes($user));
        if($do1){
            $ctx->JSONP(200,array(
                'Ok' => true,
                'Msg' => '修改成功'
                ));
        }else{
            parent::error('修改失败');
        }
    }
    
    public function updatePwd(Context $ctx){
        $this->ctx = $ctx;
        if(!parent::get_query('oldPwd') or !parent::get_query('pwd')){
            parent::error('新旧密码为空');
        }
        if(!parent::check_logined(parent::get_query('token'))){
            parent::error('token错误');
        }
        $old_pwd = parent::get_query('oldPwd');
        $new_pwd = parent::get_query('pwd');
        $do1 = DB::instance()->table('User_Pool')->where('token = ? AND pwd = ?',$token,md5($old_pwd))->update('pwd',md5($new_pwd));
        if(!$do1){
            parent::error('修改失败');
        }
        $ctx->JSONP(200,array(
            'Ok' => true,
            'Msg' => '修改成功'
            ));
    }
    
    public function updateLogo(Context $ctx){
        //do something
        $this->ctx = $ctx;
        $ctx->JSONP(200,array(
            'Ok' => true,
            'Msg' => '尚未开发此功能'
        ));
    }
    
    public function getSyncState(Context $ctx){
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