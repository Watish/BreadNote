<?php

namespace App\Controller;

use Mix\Vega\Context;
use App\Container\DB;

class Note extends Base{
    public function getNotes(Context $ctx){
        $this->ctx = $ctx;
        
        if(!parent::check_logined(parent::get_query('token'))){
            parent::error('token错误');
        }
        $token = parent::get_query('token');
        $this->token = $token;
        
        if(!parent::get_query('notebookId')){
            parent::error('笔记本参数为空');
        }
        $notebookId = parent::get_query('notebookId');
        if(!parent::istoken($notebookId)){
            parent::error('笔记本非法');
        }
        if(!parent::check_notebookid_exist($notebookId,$token)){
            parent::error('笔记本不存在');
        }
        
        $userid = parent::get_userid($token);
        $tb_name = 'User_'.$userid.'_Notes';
        
        $array = DB::instance()->table($tb_name)->where('notebook = ?',$notebookId)->get();
        
        if(!$array){
            
            $json = [];
            
        }else{
        
        $i = 0;
        foreach ($array as $a){
            
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
                'Usn' => rand(1,10),
                );
                
            unset($tags);
            unset($files);
            $i++;
        }
        }
        $ctx->JSONP(200,$json);
    }
    public function getSyncNotes(Context $ctx){
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
        if(!parent::get_query('maxEntry')){
            parent::error('最大同步数为空');
        }
        $limit = parent::get_query('maxEntry');
        if(!parent::isusn($usn)){
            parent::error('同步参数非法');
        }
        if(!is_numeric($limit) or $limit<=0){
            parent::error('最大同步数非法');
        }
        
        $afterid = parent::usn2noteid($usn);
        
        $userid = parent::get_userid($token);
        $tb_name = 'User_'.$userid.'_Notes';
        
        $array = DB::instance()->table($tb_name)->where('id > ?',$afterid)->limit($limit)->get();
        
        if(!$array){
            
            $json = [];
            
        }else{
        
        $i = 0;
        foreach ($array as $a){
            
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
        //parent::note_usn_update($maxid,parent::getusn($token),$token);
        $ctx->JSONP(200,$json);
        
        
    }
    
    public function getNoteAndContent(Context $ctx){
        $this->ctx = $ctx;
        
        if(!parent::check_logined(parent::get_query('token'))){
            parent::error('token错误');
        }
        $token = parent::get_query('token');
        $this->token = $token;
        
        if(!parent::get_query('noteId')){
            parent::error('笔记参数为空');
        }
        $noteid = parent::get_query('noteId');
        if(!parent::istoken($noteid)){
            parent::error('笔记参数非法');
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
                'NotebookId' => $array->notebook,
                'UserId' => $userid,
                'Title' => $array->title,
                'Tags' => $tags,
                'Content' => $array->content,
                'Abstract' => $array->abstract,
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
    public function getNoteContent(Context $ctx){
        parent::firstload($ctx);
        //$this->token
        $token = $this->token;
        if(!parent::get_query('noteId')){
            parent::error('笔记参数为空');
        }
        $noteid = parent::get_query('noteId');
        if(!parent::istoken($noteid)){
            parent::error('笔记参数非法');  
        }
        $userid = parent::get_userid($token);
        $tb_name = 'User_'.$userid.'_Notes';
        
        $array = DB::instance()->table($tb_name)->where('uuid = ?',$noteid)->first();
        if(!isset($array->uuid)){
            parent::error('笔记不存在');
        }
        
        $json = array(
            'NoteId' => $array->uuid,
            'UserId' => $userid,
            'Content' => $array->content,
        );
        
        $ctx->JSONP(200,$json);
        
    }
    public function deleteTrash(Context $ctx){
        parent::firstload($ctx);
        if(!parent::get_query('noteId')){
            parent::error('笔记参数为空');
        }
        $noteid = parent::get_query('noteId');
        if(!parent::istoken($noteid)){
            parent::error('笔记参数非法');
        }
        $token = $this->token;
        $userid = parent::get_userid($token);
        $tb_name = 'User_'.$userid.'_Notes';
        $array = DB::instance()->table($tb_name)->where('uuid = ?',$noteid)->first();
        if(!isset($array->uuid)){
            parent::error('笔记不存在');
        }
        $id = $array->id;
        $do1 = DB::instance()->table($tb_name)->where('uuid = ?',$noteid)->delete();
        if(!$do1){
            parent::error('删除异常');
        }
        $json = array(
            'OK' => true,
            'Msg' => '删除成功',
            //'Usn' => 10000020003000 + ($id-1)*100000000
            'Usn' => rand(1,10)
            );
        $ctx->JSONP(200,$json);
    }
    public function addNote(Context $ctx){
        parent::firstload($ctx);
        
        if(!parent::get_query('NotebookId')){
            parent::error('笔记本参数为空');
        }
        $notebookid = parent::get_query('NotebookId');
        if(!parent::istoken($notebookid)){
            parent::error('笔记本参数非法');
        }
        if(!parent::check_notebookid_exist($notebookid,$this->token)){
            parent::error('笔记本不存在');
        }
        if(!parent::get_query('Title')){
            parent::error('标题参数为空');
        }
        $title = addslashes(parent::get_query('Title'));
        
        /***
        if(!parent::get_query('Content')){
            parent::error('笔记内容为空');
        }
        ***/
        
        
        $content = addslashes(parent::get_query('Content'));
        
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
            $new_data['tags'] = json_encode(parent::get_query('Tags'),true);
        }
        
       
        $ismarkdown = parent::get_query('IsMarkdown');
        
        if($ismarkdown == 'false' or !$ismarkdown){
            $ismarkdown = false;
        }else{
            $ismarkdown = true;
        }
        
        $new_data['ismarkdown'] = $ismarkdown;
       
        $new_data['abstract'] = addslashes(parent::get_query('Abstract'));
            
        
        
        if(parent::get_query('CreatedTime')){
            $created_time = parent::get_query('CreatedTime');
            if(!parent::isdate($created_time)){
                //parent::error('创建日期格式错误');
                $created_time = date("Y-m-d H:i:s");
            }
            $new_data['createdtime'] = $created_time;
        }
        
        if(parent::get_query('UpdatedTime')){
            $updated_time = parent::get_query('UpdatedTime');
            if(!parent::isdate($updated_time)){
                //parent::error('更新日期格式错误');
                $updated_time = date("Y-m-d H:i:s");
            }
            $new_data['updatetime'] = $updated_time;
        }
        
        if(parent::get_query('Files')){
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
        
        //$array = $a;
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
    
    public function updateNote(Context $ctx){
        parent::firstload($ctx);
        
        if(!parent::get_query('NoteId')){
            parent::error('笔记参数为空');
        }
        $noteid = parent::get_query('NoteId');
        if(!parent::istoken($noteid)){
            parent::error('笔记参数非法');
        }
        if(!parent::check_noteid_exist($noteid,$this->token)){
            parent::error('笔记不存在');
        }
        
        if(parent::get_query('NotebookId')){
            $notebookid = parent::get_query('NotebookId');
            if(!parent::check_notebookid_exist($notebookid,$this->token)){
                parent::error('笔记本不存在');
            }
            $new_data['notebook'] = $notebookid;
        }
        
        if(parent::get_query('Title')){
            $new_data['title'] = addslashes(parent::get_query('Title'));
        }
        
        if(parent::get_query('Tags')){
            $new_data['tags'] = json_encode(parent::get_query('tags'));
        }
        
        if(parent::get_query('Content')){
            $new_data['content'] = addslashes(parent::get_query('Content'));
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