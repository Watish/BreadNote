<?php

namespace App\Controller;

use Mix\Vega\Context;
use App\Container\DB;

class Note extends Base{//继承基础功能集合
    public function getNotes(Context $ctx){//获取笔记本下所有笔记
        $this->ctx = $ctx;
        parent::firstload($ctx);//初始化
        $token = $this->token;//获取token
        
        if(!parent::get_query('notebookId')){//判断url参数笔记本id是否为空
            parent::error('笔记本参数为空');
        }
        $notebookId = parent::get_query('notebookId');
        if(!parent::istoken($notebookId)){//判断id是否非法
            parent::error('笔记本非法');
        }
        if(!parent::check_notebookid_exist($notebookId,$token)){//检查笔记本是否存在
            parent::error('笔记本不存在');
        }
        
        $userid = parent::get_userid($token);//获取用户id
        $tb_name = 'User_'.$userid.'_Notes';
        
        $array = DB::instance()->table($tb_name)->where('notebook = ?',$notebookId)->get();//查询用户笔记数据
        if(!$array){//数据为空
            $json = [];
        }else{//有数据
            $i = 0;
            foreach ($array as $a){//遍历赋值
                if(empty($a->tags) or !isset($a->tags) or !$a->tags or $a->tags=='false'){//标签
                    $tags = array();
                }else{
                    $tags = json_decode($a->tags);
                }
                
                if(empty($a->files) or !isset($a->files) or !$a->files or $a->files=='false'){//文件
                    $files = array();
                }else{
                    $files = json_decode($a->files);
                }
                
                $json[$i] = array(
                    'NoteId' => $a->uuid,//笔记id
                    'NotebookId' => $a->notebook,//笔记本id
                    'UserId' => $userid,//用户id
                    'Title' => $a->title,//标题
                    'Tags' => $tags,//标签
                    //'Content' => $a->content,
                    //'Abstract' => $a->abstract,
                    'IsMarkdown' => (bool)$a->ismarkdown,//是否是markdown笔记
                    'IsBlog' => (bool)$a->isblog,//是否是博客，暂未开发
                    'IsTrash' => (bool)$a->istrash,//是否是在垃圾桶中
                    'Files' => $files,//所含附件，暂未开发
                    'CreatedTime' =>$a->createdtime,//创建日期
                    'UpdatedTime' => $a->updatetime,//更新日期
                    'PublicTime' => $a->createdtime,//公开日期，和创建日期一致
                    //'Usn' => (int)10000020003000 + $a->id*100000000
                    'Usn' => rand(1,10),//usn同步功能暂未开发
                    );
                
            unset($tags);//释放tags
            unset($files);//释放files
            $i++;
        }
        }
        $ctx->JSONP(200,$json);//返回json
    }
    public function getSyncNotes(Context $ctx){//获取全部笔记
        $this->ctx = $ctx;
        parent::firstload($ctx);
        $token = $this->token;
        
        /***
         * usn同步功能暂未开发
         ***/
        
        if(!parent::get_query('afterUsn')){//暂未开发
            parent::error('同步参数为空');
        }
        $usn = parent::get_query('afterUsn');//暂未开发
        
        if(!parent::get_query('maxEntry')){
            parent::error('最大同步数为空');//查询笔记个数
        }
        $limit = parent::get_query('maxEntry');
        
        if(!parent::isusn($usn)){//暂未开发
            parent::error('同步参数非法');
        }
        if(!is_numeric($limit) or $limit<=0){
            parent::error('最大同步数非法');
        }
        
        $afterid = parent::usn2noteid($usn);//暂未开发
        
        $userid = parent::get_userid($token);
        $tb_name = 'User_'.$userid.'_Notes';
        
        $array = DB::instance()->table($tb_name)->where('id > ?',$afterid)->limit($limit)->get();//查询用户笔记数据
        
        if(!$array){//数据为空
            $json = [];
        }else{
        $i = 0;
        foreach ($array as $a){//遍历赋值
            if(empty($a->tags) or !isset($a->tags) or !$a->tags or $a->tags=='false'){
                $tags = array();
            }else{
                $tags = json_decode($a->tags);
            }
            if(empty($a->files) or !isset($a->files) or !$a->files or $a->files=='false'){
                $files = array();
            }else{
                $files = json_decode($a->files);
            }
            $json[$i] = array(
                'NoteId' => $a->uuid,
                'NotebookId' => $a->notebook,
                'UserId' => $userid,
                'Title' => $a->title,
                'Tags' => $tags,
                //'Content' => $a->content,
                //'Abstract' => $a->abstract,
                'IsMarkdown' => (bool)$a->ismarkdown,
                'IsBlog' => (bool)$a->isblog,
                'IsTrash' => (bool)$a->istrash,
                'Files' => $files,
                'CreatedTime' =>$a->createdtime,
                'UpdatedTime' => $a->updatetime,
                'PublicTime' => $a->createdtime,
                //'Usn' => (int)10000020003000 + $a->id*100000000
                'Usn' => rand(1,10)
                );
            unset($files);
            unset($tags);
            $maxid = $a->id;
            $i++;
        }
        }
        $ctx->JSONP(200,$json);//返回json
    }
    
    public function getNoteAndContent(Context $ctx){
        $this->ctx = $ctx;
        parent::firstload($ctx);
        $token = $this->token;
        
        if(!parent::get_query('noteId')){
            parent::error('笔记参数为空');
        }
        $noteid = parent::get_query('noteId');
        if(!parent::istoken($noteid)){
            parent::error('笔记参数非法');
        }
        
        $mycache = new Cache($noteid.'getNoteAndContent');
        if($mycache->exists and !$mycache->expired){
            $json = $mycache->value;
            $ctx->JSONP(200,$json);
            $ctx->abort();
        }
        
        $userid = parent::get_userid($token);
        $tb_name = 'User_'.$userid.'_Notes';
        
        $array = DB::instance()->table($tb_name)->where('uuid = ?',$noteid)->first();
        if(!isset($array->uuid)){
            parent::error('笔记本不存在');
        }
        
        $array = $a;
        if(empty($a->tags) or !isset($a->tags) or !$a->tags or $a->tags=='false'){
                $tags = array();
        }else{
                $tags = json_decode($a->tags);
        }
        if(empty($a->files) or !isset($a->files) or !$a->files or $a->files=='false'){
                $files = array();
        }else{
                $files = json_decode($a->files);
        }
        $json = array(
                'NoteId' => $array->uuid,
                'NotebookId' => $array->notebook,//所在笔记本id
                'UserId' => $userid,//用户id
                'Title' => $array->title,//标题
                'Tags' => $tags,//标签
                'Content' => $array->content,//内容
                'Abstract' => $array->abstract,//abstract内容
                'IsMarkdown' => (bool)$array->ismarkdown,//是否是markdown笔记
                'IsBlog' => (bool)$array->isblog,//是否是博客，暂未开发
                'IsTrash' => (bool)$array->istrash,//是否是在垃圾桶中
                'Files' => $files,//所含附件，暂未开发
                'CreatedTime' =>$array->createdtime,//创建日期
                'UpdatedTime' => $array->updatetime,//更新日期
                'PublicTime' => $array->createdtime,//公开日期，与创建日期一致
                //'Usn' => (int)10000020003000 + $array->id*100000000
                'Usn' => rand(1,10)
        );
        $mycache->Set($json);
        $ctx->JSONP(200,$json);
    }
    public function getNoteContent(Context $ctx){
        $this->ctx = $ctx;
        parent::firstload($ctx);//初始化
        $token = $this->token;//获取token
        
        if(!parent::get_query('noteId')){//判断url参数笔记id是否为空
            parent::error('笔记参数为空');
        }
        $noteid = parent::get_query('noteId');
        if(!parent::istoken($noteid)){//判断id是否非法
            parent::error('笔记参数非法');  
        }
        
        $mycache = new Cache($noteid.'getNoteContent');
        if($mycache->exists and !$mycache->expired){
            $json = $mycache->value;
            $ctx->JSONP(200,$json);
            $ctx->abort();
        }
        
        $userid = parent::get_userid($token);//获取用户id
        $tb_name = 'User_'.$userid.'_Notes';
        
        $array = DB::instance()->table($tb_name)->where('uuid = ?',$noteid)->first();//通过笔记id查询用户笔记数据
        if(!isset($array->uuid)){
            parent::error('笔记不存在');
        }
        $json = array(
            'NoteId' => $array->uuid,//笔记id
            'UserId' => $userid,//用户id
            'Content' => $array->content,//笔记内容
        );
        $mycache->Set($json);
        $ctx->JSONP(200,$json);//返回数据
    }
    public function deleteTrash(Context $ctx){
        $this->ctx = $ctx;
        parent::firstload($ctx);
        $token = $this->token;
        
        if(!parent::get_query('noteId')){
            parent::error('笔记参数为空');
        }
        $noteid = parent::get_query('noteId');
        if(!parent::istoken($noteid)){
            parent::error('笔记参数非法');
        }
        
        $this->flushAllCache($noteid);
        $token = $this->token;
        $userid = parent::get_userid($token);
        $tb_name = 'User_'.$userid.'_Notes';
        $array = DB::instance()->table($tb_name)->where('uuid = ?',$noteid)->first();//通过笔记id查询用户笔记数据
        if(!isset($array->uuid)){
            parent::error('笔记不存在');
        }
        $id = $array->id;
        $do1 = DB::instance()->table($tb_name)->where('uuid = ?',$noteid)->delete();//删除用户笔记数据中指定id的笔记
        if(!$do1){
            parent::error('删除异常');
        }
        $json = array(
            'OK' => true,
            'Msg' => '删除成功',
            //'Usn' => 10000020003000 + ($id-1)*100000000
            'Usn' => rand(1,10)//usn同步功能暂未开发
            );
        $ctx->JSONP(200,$json);
    }
    public function addNote(Context $ctx){//添加笔记
        $this->ctx = $ctx;
        parent::firstload($ctx);
        $token = $this->token;
        
        if(!parent::get_query('NotebookId')){//判断url参数笔记本id是否为空
            parent::error('笔记本参数为空');
        }
        $notebookid = parent::get_query('NotebookId');
        if(!parent::istoken($notebookid)){
            parent::error('笔记本参数非法');
        }
        if(!parent::check_notebookid_exist($notebookid,$this->token)){//判断笔记本是否存在
            parent::error('笔记本不存在');
        }
        if(!parent::get_query('Title')){
            parent::error('标题参数为空');
        }
        $title = addslashes(parent::get_query('Title'));//标题转义
        $content = parent::get_query('Content');//内容转义
        $uuid = parent::new_token();
        $new_data = [
            'notebook' => $notebookid,
            'title' => $title,
            'content' => $content,
            'isdeleted' => false,
            'isblog' => false,
            'istrash' => false,
            'uuid' => $uuid
            ];
        
        if(parent::get_query('Tags')){
            $new_data['tags'] = json_encode(parent::get_query('Tags'),true);//传来的tags参数是一个数组
        }
        $ismarkdown = parent::get_query('IsMarkdown');//获取url参数ismarkdown
        if($ismarkdown == 'false' or !$ismarkdown){
            $ismarkdown = false;
        }else{
            $ismarkdown = true;
        }
        $new_data['ismarkdown'] = $ismarkdown;
        $new_data['abstract'] = addslashes(parent::get_query('Abstract'));
        if(parent::get_query('CreatedTime')){
            $created_time = parent::get_query('CreatedTime');
            if(!parent::isdate($created_time)){//日期格式错误则创建一个新的日期
                //parent::error('创建日期格式错误');
                $created_time = date("Y-m-d H:i:s");
            }
            $new_data['createdtime'] = $created_time;
        }
        
        if(parent::get_query('UpdatedTime')){//同上
            $updated_time = parent::get_query('UpdatedTime');
            if(!parent::isdate($updated_time)){
                //parent::error('更新日期格式错误');
                $updated_time = date("Y-m-d H:i:s");
            }
            $new_data['updatetime'] = $updated_time;
        }
        
        if(parent::get_query('Files')){//和tags参数一样，传来的是一个数组
            $new_data['files'] = json_encode(parent::get_query('Tags'));
        }
        
        $userid = parent::get_userid($this->token);
        $tb_name = 'User_'.$userid.'_Notes';
        
        $do1 = DB::instance()->insert($tb_name,$new_data);
        
        if(!$do1){
            parent::error('添加失败');
        }
        
        $array = DB::instance()->table($tb_name)->where('uuid = ?',$uuid)->first();
        
        if(!isset($array->uuid)){
            parent::error('笔记异常');
        }
        
        $a = $array;
        if(empty($a->tags) or !isset($a->tags) or !$a->tags or $a->tags=='false'){
                $tags = array();
            }else{
                $tags = json_decode($a->tags);
        }
        if(empty($a->files) or !isset($a->files) or !$a->files or $a->files=='false'){
                $files = array();
            }else{
                $files = json_decode($a->files);
        }
        $json = array(
                'NoteId' => $array->uuid,
                'NotebookId' => $array->notebook,
                'UserId' => $userid,
                'Title' => $array->title,
                'Tags' => $tags,
                'IsMarkdown' => (bool)$array->ismarkdown,
                'IsBlog' => (bool)$array->isblog,
                'IsTrash' => (bool)$array->istrash,
                'Files' => $files,
                'CreatedTime' =>$array->createdtime,
                'UpdatedTime' => $array->updatetime,
                'PublicTime' => $array->createdtime,
                //'Usn' => (int)10000020003000 + $array->id*100000000
                'Usn' => rand(1,10)
            );
        $ctx->JSONP(200,$json);
    }
    
    public function updateNote(Context $ctx){//更新笔记
        $this->ctx = $ctx;
        parent::firstload($ctx);
        $token = $this->token;
        
        
        if(!parent::get_query('NoteId')){//笔记id必传
            parent::error('笔记参数为空');
        }
        $noteid = parent::get_query('NoteId');
        if(!parent::istoken($noteid)){
            parent::error('笔记参数非法');
        }
        
        $this->flushAllCache($noteid);
        
        
        if(!parent::check_noteid_exist($noteid,$this->token)){
            parent::error('笔记不存在');
        }
        
        if(parent::get_query('NotebookId')){//笔记本id也必传
            $notebookid = parent::get_query('NotebookId');
            if(!parent::check_notebookid_exist($notebookid,$this->token)){
                parent::error('笔记本不存在');
            }
            $new_data['notebook'] = $notebookid;
        }
        
        if(parent::get_query('Title')){//标题可选，有则加入更新数组中
            $new_data['title'] = addslashes(parent::get_query('Title'));
        }
        
        if(parent::get_query('Tags')){//标签可选，有则加入更新数组中
            $new_data['tags'] = json_encode(parent::get_query('tags'));
        }
        
        if(parent::get_query('Content')){//内容可选，有则加入更新数组中
            $new_data['content'] = parent::get_query('Content');
        }
        /***
        $ismarkdown = parent::get_query('IsMarkdown');
        if(parent::get_query('IsMarkdown')){
            
            if(empty($ismarkdown) or !isset($ismarkdown) or is_null($ismarkdown) or !$ismarkdown){
                $ismarkdown = false;
            }
            
            if($ismarkdown and !empty($ismarkdown) and $ismarkdown!=='false'){
                $ismarkdown = true;
            }
            
            if($ismarkdown and !empty($ismarkdown) and $ismarkdown!=='true'){
                $ismarkdown = false;
            }
        }
        
        $new_data['ismarkdown'] = $ismarkdown;
        
        ***/
        $new_data['abstract'] = addslashes(parent::get_query('Abstract'));
        if(parent::get_query('IsTrash')){
            $new_data['istrash'] = true;
        }else{
            $new_data['istrash'] = false;        
        }
        if(parent::get_query('UpdatedTime')){
            $updated_time = parent::get_query('UpdatedTime');
            if(!parent::isdate($updated_time)){
                parent::error('更新日期格式错误');
            }
            $new_data['updatetime'] = $updated_time;
        }
        
        if(parent::get_query('Files')){
            $new_data['files'] = json_encode(parent::get_query('Tags'));
        }
        $userid = parent::get_userid($this->token);
        $tb_name = 'User_'.$userid.'_Notes';
        $do1 = DB::instance()->table($tb_name)->where('uuid = ?',$noteid)->updates($new_data);
        if(!$do1){
            parent::error('更新失败');
        }        
        $array = DB::instance()->table($tb_name)->where('uuid = ?',$noteid)->first();
        $a = $array;
        if(empty($a->tags) or !isset($a->tags) or !$a->tags or $a->tags=='false'){
                $tags = array();
            }else{
                $tags = json_decode($a->tags);
            }
            
            if(empty($a->files) or !isset($a->files) or !$a->files or $a->files=='false'){
                $files = array();
            }else{
                $files = json_decode($a->files);
        }
        $json = array(
                'NoteId' => $array->uuid,
                'NotebookId' => $array->notebook,
                'UserId' => $userid,
                'Title' => $array->title,
                'Tags' => $tags,
                'IsMarkdown' => (bool)$array->ismarkdown,
                'IsBlog' => (bool)$array->isblog,
                'IsTrash' => (bool)$array->istrash,
                'Files' => $files,
                'CreatedTime' =>$array->createdtime,
                'UpdatedTime' => $array->updatetime,
                'PublicTime' => $array->createdtime,
                //'Usn' => (int)10000020003000 + $array->id*100000000
                'Usn' => rand(1,10)
            );
        $ctx->JSONP(200,$json);
        
        
    }
    
    private function flushAllCache($noteid){
        $a = new Cache($noteid.'getNoteAndContent');
        $b = new Cache($noteid.'getNoteContent');
        if($a->exists){
            $a->Delete();
        }
        if($b->exists){
            $b->Delete();
        }
    }
    private function flushCache($name){
        $a = new Cache($name);
        if($a->exists){
            $a->Delete();
        }
    }
}

/***
    $json[$i] = array(
                'NoteId' => $a->uuid,
                'NotebookId' => $a->notebook,
                'UserId' => $userid,
                'Title' => $a->title,
                'Tags' => (array)json_decode($a->tags),
                'Content' => $a->content,
                'IsMarkdown' => (bool)$a->ismarkdown,
                'IsBlog' => (bool)$a->isblog,
                'IsTrash' => (bool)$a->istrash,
                'Files' => (array)json_decode($a->files),
                'CreatedTime' =>$a->createdtime,
                'UpdatedTime' => $a->updatetime,
                'PublicTime' => $a->createdtime,
                'Usn' => (int)10000020003000 + $a->id*100000000
                );
            $i++;
***/