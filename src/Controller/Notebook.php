<?php

namespace App\Controller;

use Mix\Vega\Context;
use App\Container\DB;

class Notebook extends Base
{
    public $ctx;
    
    public function getNotebooks(Context $ctx){//获取所有笔记本
        $this->ctx = $ctx;
        parent::firstload($ctx);
        $token = $this->token;
        
        $id = parent::get_userid($token);//获取用户id
        $this->id = $id;//传递局部变量
        
        $tb_name = 'User_'.$id.'_Notebooks';
        $sql = 'SELECT * FROM '.addslashes($tb_name);//查询用户笔记本数据
        
        $array = DB::instance()->raw($sql)->get();//获取数据
        
        if(!$array){//遍历赋值
            $json = [];
        }else{
            $i = 0;
            foreach ($array as $a){
                $json[$i]['NotebookId'] = $a->uuid;//笔记本id
                $json[$i]['UserId'] = $id ;//用户id
                $json[$i]['ParentNotebookId'] = $a->etc;//父笔记本id
                $json[$i]['Seq'] = (int)$a->seq;//排序，暂未开发
                $json[$i]['Title'] = $a->title;//笔记本标题
                $json[$i]['IsBlog'] = (bool)$a->isblog;//是否是博客，暂未开发
                $json[$i]['IsDeleted'] = (bool)$a->isdeleted;//是否已移至回收箱里
                $json[$i]['CreatedTime'] = $a->createdtime;//创建日期
                $json[$i]['UpdatedTime'] = $a->updatetime;//更新日期
                //$json[$i]['Usn'] = $a->id;
                $json[$i]['Usn'] = rand(1,10) ;//usn同步暂未开发
                $i++;
            }
            }
            unset($array);//释放数组
            $ctx -> JSONP(200,$json);//返回数据
        
    }
    public function getSyncNotebooks(Context $ctx){//获取需同步的笔记本
        $this->ctx = $ctx;
        parent::firstload($ctx);
        $token = $this->token;
        
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
        
        unset($array);//释放数组
        
        $tb_name = 'User_'.$id.'_Notebooks';
        $sql = 'SELECT * FROM '.addslashes($tb_name);//查询用户笔记本数据
        
        $fromid = parent::usn2noteid($usn);//usn同步暂未开发，默认返回0
        
        $array = DB::instance()->table($tb_name)->where('id > ?',$fromid)->limit($limit)->get();//查询数据
        
        if(!$array){
            $json = [];
        }else{
            $i = 0;
            foreach ($array as $a){//遍历赋值
                $json[$i]['NotebookId'] = $a->uuid;//笔记本id
                $json[$i]['UserId'] = $id ;//用户id
                $json[$i]['ParentNotebookId'] = $a->etc;//父笔记本id
                $json[$i]['Seq'] = (int)$a->seq;//排序参数
                $json[$i]['Title'] = $a->title;//笔记本标题
                $json[$i]['IsBlog'] = (bool)$a->isblog;//是否为博客
                $json[$i]['IsDeleted'] = (bool)$a->isdeleted;//是否移至回收箱
                $json[$i]['CreatedTime'] = $a->createdtime;//创建日期
                $json[$i]['UpdatedTime'] = $a->updatetime;//更新日期
                //$json[$i]['Usn'] = $a->id;
                $json[$i]['Usn'] = rand(1,10);
                $i++;
                $maxid = $a->id;
            }
        }
        unset($array);//释放数组
        //parent::notebook_usn_update((int)$maxid,$usn,$token);
        $ctx -> JSONP(200,$json);
    }
    public function addNotebook(Context $ctx){//添加笔记本
        $this->ctx = $ctx;
        parent::firstload($ctx);
        $token = $this->token;
        
        if(!parent::get_query('title')){//笔记本标题必传
            parent::error('标题为空');
        }
        
        $parentNotebookId = null;//父笔记本默认为null
        
        if(parent::get_query('parentNotebookId')){//传了父笔记本就赋值
            $parentNotebookId = parent::get_query('parentNotebookId');
            if(!parent::istoken($parentNotebookId)){//判断id是否非法
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
        
        $title = parent::get_query('title');//获取url参数中笔记本的标题
        $seq = parent::get_query('seq');//获取url参数中笔记本排序

        if(parent::check_notebook_exist($title,$token)){//判断笔记本是否存在
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
    public function updateNotebook(Context $ctx){//更新笔记本
        $this->ctx = $ctx;
        parent::firstload($ctx);
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
        parent::firstload($ctx);
        $token = $this->token;
        
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