<?php

namespace App\Controller;

use Mix\Vega\Context;
use App\Container\DB;

class Tag extends Base
{
    public function addTag(Context $ctx){
        $this->ctx = $ctx;
        parent::firstload($ctx);
        $token = $this->token;
        
        if(!parent::get_query('tag')){//获取url参数tag，tag为标签名
            parent::error('标签参数为空');
        }
        $tag = parent::get_query('tag');
        if(!parent::strsafe($tag)){//判断标签名是否非法
            parent::error('标签名非法');
        }
        if($this->is_tag_exist($tag)){//判断标签是否存在
            parent::error('标签已存在');
        }
        $new_data = [
            'name' => addslashes($tag),//转义的标签名
            'isdeleted' => false,//是否删除
            'createdtime' => date("Y-m-d H:i:s"),//创建日期
            'updatetime' => date("Y-m-d H:i:s"),//更新日期，创建时与创建日期一致
            'uuid' => parent::new_token()//新建token
            ];
        
        $token = $this->token;
        $userid = parent::get_userid($token);
        $tb_name = 'User_'.$userid.'_Tags';    
        
        $do1 = DB::instance()->insert($tb_name,$new_data);//插入tag数据
        if(!$do1){
            parent::error('添加异常');
        }
        $array = DB::instance()->table($tb_name)->where('name = ?',addslashes($tag))->first();//查询刚刚插入的tag数据
        $json = array(
            'TagId' => $array->uuid,//tag对应的id
            'UserId' => $userid,//用户id
            'Tag' => $array->name,//标签名
            'CreatedTime' => $array->createdtime,
            'UpdatedTime' => $array->updatetime,
            'Usn' => rand(1,10)//usn同步暂未开发
            );
        $ctx->JSONP(200,$json);
    }
    
    public function getSyncTags(Context $ctx){//获取需同步的标签，由于usn同步暂未开发，相当于获取所有标签
        $this->getTags($ctx);
    }
    
    public function deleteTag(Context $ctx){//删除标签
        $this->ctx = $ctx;
        parent::firstload($ctx);
        $token = $this->token;
        
        $userid = parent::get_userid($token);
        $tb_name = 'User_'.$userid.'_Tags'; 
        
        if(!parent::get_query('tag')){
            parent::error('标签参数为空');
        }
        
        $tag = parent::get_query('tag');
        if(!parent::strsafe($tag)){
            parent::error('标签名非法');
        }
        if(!$this->is_tag_exist($tag)){
            parent::error('标签不存在');
        }
        
        $do1 = DB::instance()->table($tb_name)->where('name = ?',addslashes($tag))->delete();//删除指定标签名的标签
        
        if(!$do1){
            parent::error('删除失败');
        }
        
        $ctx->JSONP(200,array(
            'Ok' => true,
            'Msg' => '删除成功',
            'Usn' => rand(1,10)
            ));
        
    }
    
    private function getTags(Context $ctx){//获取所有标签
        $this->ctx = $ctx;
        parent::firstload($ctx);
        $token = $this->token;
        
        $userid = parent::get_userid($token);//获取用户id
        $tb_name = 'User_'.$userid.'_Tags';  
        
        $array = DB::instance()->table($tb_name)->get();//查询用户标签数据
        $i = 0;
        $json = array();
        foreach ($array as $a){//遍历赋值
            $json[$i] = array(
                'TagId' => $a->uuid,
                'UserId' => $userid,
                'Tag' => $a->name,
                'CreatedTime' => $a->createdtime,
                'UpdatedTime' => $a->updatetime,
                'Usn' => rand(1,10)
            );
            $i++;
        }
        $ctx->JSONP(200,$json);
    }
    
    private function is_tag_exist($str){//判断标签是否存在
        $token = $this->token;
        $userid = parent::get_userid($token);
        $tb_name = 'User_'.$userid.'_Tags';
        
        $array = DB::instance()->table($tb_name)->where('name = ?',addslashes($str))->first();//查询指定名称的标签数据
        if(!isset($array->name)){//若数据为空
            return false;
        }
        return true;
    }
}