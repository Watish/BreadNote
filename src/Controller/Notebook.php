<?php

namespace App\Controller;

use Mix\Vega\Context;
use App\Container\DB;

class Notebook extends Base
{
    public $ctx;
    
    public function getNotebooks(Context $ctx){
        $this->ctx = $ctx;
        
        if(!parent::check_logined(parent::get_query('token'))){
            parent::error('token错误');
        }
        $token = parent::get_query('token');
        $this->token = $token;
        
        $array = DB::instance()->table('User_Pool')->where('token = ?',$token)->first();
        if(!isset($array->id)){
            parent::erorr('用户异常');
        }
        $id = $array->id;
        $this->id = $id;
        
        unset($array);
        
        $tb_name = 'User_'.$id.'_Notebooks';
        $sql = 'SELECT * FROM '.addslashes($tb_name);
        
        $array = DB::instance()->raw($sql)->get();
        
        if(!$array){
            //parent::error('用户数据异常');
            $json = [];
        }else{
        $i = 0;
        foreach ($array as $a){
            $json[$i]['NotebookId'] = $a->uuid;
            $json[$i]['UserId'] = $id ;
            $json[$i]['ParentNotebookId'] = $a->etc;
            $json[$i]['Seq'] = (int)$a->seq;
            $json[$i]['Title'] = $a->title;
            $json[$i]['IsBlog'] = (bool)$a->isblog;
            $json[$i]['IsDeleted'] = (bool)$a->isdeleted;
            $json[$i]['CreatedTime'] = $a->createdtime;
            $json[$i]['UpdatedTime'] = $a->updatetime;
            //$json[$i]['Usn'] = $a->id;
            $json[$i]['Usn'] = rand(1,10) ;
            $i++;
        }
        }
        unset($array);
        $ctx -> JSONP(200,$json);
        
    }
    public function getSyncNotebooks(Context $ctx){
        $this->ctx = $ctx;
        
        if(!parent::check_logined(parent::get_query('token'))){
            parent::error('token错误');
        }
        $token = parent::get_query('token');
        $this->token = $token;
        
        if(!parent::get_query('afterUsn')){
            parent::error('同步参数为空');
        }
        $usn = parent::get_query('afterUsn');
        if(!parent::isusn($usn)){
            parent::error('同步参数非法');
        }
        
        if(!parent::get_query('maxEntry')){
            parent::error('最大同步数参数为空');
        }
        $limit = parent::get_query('maxEntry');
        if(!is_numeric($limit) or $limit<=0){
            parent::error('最大同步数参数非法');
        }
        
        $array = DB::instance()->table('User_Pool')->where('token = ?',$token)->first();
        if(!isset($array->id)){
            parent::erorr('用户异常');
        }
        $id = $array->id;
        $this->id = $id;
        
        unset($array);
        
        $tb_name = 'User_'.$id.'_Notebooks';
        $sql = 'SELECT * FROM '.addslashes($tb_name);
        
        $fromid = parent::usn2noteid($usn);
        
        $array = DB::instance()->table($tb_name)->where('id > ?',$fromid)->limit($limit)->get();
        
        if(!$array){
            $json = [];
        }else{
        $i = 0;
        foreach ($array as $a){
            $json[$i]['NotebookId'] = $a->uuid;
            $json[$i]['UserId'] = $id ;
            $json[$i]['ParentNotebookId'] = $a->etc;
            $json[$i]['Seq'] = (int)$a->seq;
            $json[$i]['Title'] = $a->title;
            $json[$i]['IsBlog'] = (bool)$a->isblog;
            $json[$i]['IsDeleted'] = (bool)$a->isdeleted;
            $json[$i]['CreatedTime'] = $a->createdtime;
            $json[$i]['UpdatedTime'] = $a->updatetime;
            //$json[$i]['Usn'] = $a->id;
            $json[$i]['Usn'] = rand(1,10);
            $i++;
            $maxid = $a->id;
        }
        }
        unset($array);
        //parent::notebook_usn_update((int)$maxid,$usn,$token);
        $ctx -> JSONP(200,$json);
    }
    public function addNotebook(Context $ctx){
        $this->ctx = $ctx;
        if(!parent::check_logined(parent::get_query('token'))){
            parent::error('token错误');
        }
            $this->token = parent::get_query('token');
            $token = $this->token;
        if(!parent::get_query('title')){
            parent::error('标题为空');
        }
        
        $parentNotebookId = null;
        
        if(parent::get_query('parentNotebookId')){
            $parentNotebookId = parent::get_query('parentNotebookId');
            if(!parent::istoken($parentNotebookId)){
                $parentNotebookId = null;
            }
        }
        
        
        /***
        if(!parent::get_query('seq')){
            parent::error('排列为空');
        }
        if(!is_numeric($seq) or $seq<0){
            $seq = 0;
        }
        ***/
        
        $title = parent::get_query('title');
        $seq = parent::get_query('seq');

        if(parent::check_notebook_exist($title,$token)){
            parent::error('笔记本已存在');
        }
        /***
            $json[$i]['NotebookId'] = $a->id;
            $json[$i]['UserId'] = $id ;
            $json[$i]['ParentNotebookId'] = $a->etc;
            $json[$i]['Seq'] = $a->seq;
            $json[$i]['Title'] = $a->title;
            $json[$i]['IsBlog'] = $a->isblog;
            $json[$i]['IsDeleted'] = $a->isdeleted;
            $json[$i]['CreatedTime'] = $a->createdtime;
            $json[$i]['UpdatedTime'] = $a->updatetime;
            $json[$i]['Usn'] = $a->id;
        ***/
        $new_data = [
            'title' => addslashes($title),
            'seq' => $seq,
            'etc' => $parentNotebookId,
            'isblog' => false,
            'isdeleted' => false,
            'createdTime' => date("Y-m-d H:i:s"),
            'updatetime' => date("Y-m-d H:i:s"),
            'uuid' => parent::new_token()
            ];
        $userid = parent::get_userid($token);
        $tb_name = 'User_'.$userid.'_Notebooks';
        $do1 = DB::instance()->insert($tb_name,$new_data);
        if(!$do1){
            parent::error('创建笔记本失败');
        }
        $notebookid = $new_data['uuid'];
        $a = DB::instance()->table($tb_name)->where('uuid = ?',$notebookid)->first();
        
        $json['NotebookId'] = $a->uuid;
        $json['UserId'] = $userid;
        $json['ParentNotebookId'] = $a->etc;
        $json['Seq'] = (int)$a->seq;
        $json['Title'] = $a->title;
        $json['IsBlog'] = (bool)$a->isblog;
        $json['IsDeleted'] = (bool)$a->isdeleted;
        $json['CreatedTime'] = $a->createdtime;
        $json['UpdatedTime'] = $a->updatetime;
        //$json['Usn'] = $a->id;
        $json['Usn'] = rand(1,10);
        
        $ctx -> JSONP(200,$json);
        //do something
    }
    public function updateNotebook(Context $ctx){
        $this->ctx = $ctx;
        if(!parent::check_logined(parent::get_query('token'))){
            parent::error('token错误');
        }
        $this->token = parent::get_query('token');
        $token = $this->token;
        if(!parent::get_query('notebookId')){
            parent::error('笔记本参数为空');
        }
        $notebookid = parent::get_query('notebookId');
        if(!parent::get_query('title')){
            parent::error('标题参数为空');
        }
        $title = parent::get_query('title');
        $parentNotebookId = null;
        if(parent::get_query('parentNotebookId')){
            if(!parent::istoken(parent::get_query('parentNotebookId'))){
                parent::error('父笔记本非法');
            }else{
                $parentNotebookId = parent::get_query('parentNotebookId');
            }
            //parent::error('父笔记本参数为空');
        }
        
        /***
        if(!parent::get_query('usn')){
            parent::error('同步参数为空');
        }
        $usn = parent::get_query('usn');
        if(!is_numeric($usn)){
            parent::error('同步参数非法');
        }
        //usn同步暂时不用
        ***/
        
        /***
        if(!parent::get_query('seq')){
            parent::error('排序参数为空');
        }
        $seq = parent::get_query('seq');
        if(!is_numeric($seq) or $seq < 0){
            parent::error('排序参数非法');
        }
        ***/
        
        
        if(!parent::check_notebookid_exist($notebookid,$token)){
            parent::error('笔记本不存在');
        }
        
        $new_data = [
            'title' => addslashes($title),
            'etc' => $parentNotebookId,
            'seq' => 0,
        ];
        
        $userid = parent::get_userid($token);
        $tb_name = 'User_'.$userid.'_Notebooks';
        
        $do1 = DB::instance()->table($tb_name)->where('uuid = ?',$notebookid)->updates($new_data);
        if(!$do1){
            parent::error('修改失败');
        }
        
        $array = DB::instance()->table($tb_name)->where('uuid = ?',$notebookid)->first();
        
        $a = $array;
        
        
        $json['NotebookId'] = $array->uuid;
        $json['UserId'] = $userid ;
        $json['ParentNotebookId'] = $array->etc;
        $json['Seq'] = (int)$array->seq;
        $json['Title'] = $array->title;
        $json['IsBlog'] = (bool)$array->isblog;
        $json['IsDeleted'] = (bool)$array->isdeleted;
        $json['CreatedTime'] = $array->createdtime;
        $json['UpdatedTime'] = $array->updatetime;
        //$json['Usn'] = $array->id;
        $json['Usn'] = rand(1,10);
        
        $ctx->JSONP(200,$json);
    }
    
    public function deleteNotebook(Context $ctx){
        $this->ctx = $ctx;
        if(!parent::check_logined(parent::get_query('token'))){
            parent::error('token错误');
        }
        $token = parent::get_query('token');
        $this->token = $token;
        if(!parent::get_query('notebookId')){
            parent::error('笔记本参数为空');
        }
        $notebookid = parent::get_query('notebookId');
        if(!parent::check_notebookid_exist($notebookid,$token)){
            parent::error('笔记本不存在');
        }
        
        $userid = parent::get_userid($token);
        $tb_name = 'User_'.$userid.'_Notebooks';
        $do1 = DB::instance()->table($tb_name)->where('uuid = ?',$notebookid)->delete();
        $tb_name = 'User_'.$userid.'_Notes';
        $do2 = DB::instance()->table($tb_name)->where('notebook = ?',$notebookid)->delete();
        
        if(!$do1 or !$do2){
            parent::error('删除失败');
        }
        if(parent::check_notebookid_exist($notebookid,$token)){
            parent::error('删除失败');
        }
        $ctx->JSONP(200,array(
            'Ok' => true
            ));
        
        
    }

    
}