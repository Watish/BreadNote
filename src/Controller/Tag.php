<?php

namespace App\Controller;

use Mix\Vega\Context;
use App\Container\DB;

class Tag extends Base
{
    public function addTag(Context $ctx){
        parent::firstload($ctx);
        if(!parent::get_query('tag')){
            parent::error('标签参数为空');
        }
        $tag = parent::get_query('tag');
        if(!parent::strsafe($tag)){
            parent::error('标签名非法');
        }
        if($this->is_tag_exist($tag)){
            parent::error('标签已存在');
        }
        $new_data = [
            'name' => addslashes($tag),
            'isdeleted' => false,
            'createdtime' => date("Y-m-d H:i:s"),
            'updatetime' => date("Y-m-d H:i:s"),
            'uuid' => parent::new_token()
            ];
        
        $token = $this->token;
        $userid = parent::get_userid($token);
        $tb_name = 'User_'.$userid.'_Tags';    
        
        $do1 = DB::instance()->insert($tb_name,$new_data);
        if(!$do1){
            parent::error('添加异常');
        }
        $array = DB::instance()->table($tb_name)->where('name = ?',addslashes($tag))->first();
        $json = array(
            'TagId' => $array->uuid,
            'UserId' => $userid,
            'Tag' => $array->name,
            'CreatedTime' => $array->createdtime,
            'UpdatedTime' => $array->updatetime,
            'Usn' => rand(1,10)
            );
        $ctx->JSONP(200,$json);
    }
    
    public function getSyncTags(Context $ctx){
        $this->getTags($ctx);
    }
    
    public function deleteTag(Context $ctx){
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
        
        $do1 = DB::instance()->table($tb_name)->where('name = ?',addslashes($tag))->delete();
        
        if(!$do1){
            parent::error('删除失败');
        }
        
        $ctx->JSONP(200,array(
            'Ok' => true,
            'Msg' => '删除成功',
            'Usn' => rand(1,10)
            ));
        
    }
    
    private function getTags(Context $ctx){
        parent::firstload($ctx);
        $token = $this->token;
        $userid = parent::get_userid($token);
        $tb_name = 'User_'.$userid.'_Tags';  
        
        $array = DB::instance()->table($tb_name)->get();
        $i = 0;
        $json = array();
        foreach ($array as $a){
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
    
    private function is_tag_exist($str){
        $token = $this->token;
        $userid = parent::get_userid($token);
        $tb_name = 'User_'.$userid.'_Tags';
        
        $array = DB::instance()->table($tb_name)->where('name = ?',addslashes($str))->first();
        if(!isset($array->name)){
            return false;
        }
        return true;
    }
}