<?php

namespace App\Controller;

use Mix\Vega\Context;
use App\Container\DB;

class Base{
    
    public $ctx;
    public $token;
    
    public function get_query($string) {
        $ctx = $this->ctx;
        if (empty($string)) {
            return false;
        }
        if ($ctx->getQuery($string) or $ctx->getPostForm($string)) {
            if ($ctx->getQuery($string)) {
                return $ctx->getQuery($string);
            }
            if ($ctx->getPostForm($string)) {
                return $ctx->getPostForm($string);
            }
            return false;
        }
        return false;
    }

    public function error($str = "失败") {
        $array['Ok'] = false;
        $array['Msg'] = $str;
        $this->ctx->JSONP(200, $array);
        $this->ctx->abort();
    }
    public function new_token() {
        $array = 'q.w.e.r.t.y.u.i.o.p.a.s.d.f.g.h.j.k.l.z.x.c.v.b.n.m.0.1.2.3.4.5.6.7.8.9';
        $array = explode('.', $array);
        $num = count($array);
        $res = '';
        for ($i = 1; $i <= 13; $i++) {
            $a = $array[rand(0, $num-1)];
            if (empty($a) and !isset($a)) {
                $i = $i-1;
            } else {
                $res = $res.$a;
            }
        }
        return $res.date("ymdhis");
    }
    public function check_logined($token) {
        $array = DB::instance()->table('User_Pool')-> where('token = ?', addslashes($token))->first();
        if (isset($array->user)) {
            return true;
        } else {
            return false;
        }
    }
    public function get_userid($token) {
        $array = DB::instance()->table('User_Pool')->where('token = ?', $token)->first();
        return $array->id;
    }
    public function strsafe($str) {
        $pattern = "#['!`~\/\\\%^&*()+=\$\#:;<>\]\[{}]#";
        if (preg_match($pattern, $str)) {
            return false;
        } else {
            return true;
        }
    }
    public function istoken($str) {
        $ereg = '/^[[:alnum:]]{25}$/';
        //$str='a!@#$%^';
        $num = preg_match($ereg, $str);
        //echo $num;
        if ($num == 0 or empty($str) or !isset($str)) {
            return false;
        } else {
            return true;
        }
    }
    public function isdate($str){
        $ereg = '/^[0-9]{4}(-[0-9]{2}){2}.[0-9]{2}(:[0-9]{2}){2}$/';
        //$str='a!@#$%^';
        $num = preg_match($ereg, $str);
        //echo $num;
        if ($num == 0 or empty($str) or !isset($str)) {
                return false;
        } else {
                return true;
        }
    }
    /***100000 2000 3000  
     * 笔记id 笔记本id 标签id
     * 
     * lastsysnctime = time()
    ***/
    public function isusn($usn) {
        /***
        if (!isset($usn) or empty($usn)) {
            return false;
        }
        if (!is_numeric($usn)) {
            return false;
        }
        if ($usn >= 10000020003000 and $usn <= 19999929993999) {
            return true;
        }
        ***/
        return true;
    }
    public function usn2noteid($usn) {
        /***
        if (!$this->isusn($usn)) {
            return false;
        }
        $str = strval($usn);
        $str = substr($str, 0, 6);
        $id = (int)$str-100000;
        return $id;
        ***/
        return 0;
    }
    public function usn2notebookid($usn) {
        /***
        if (!$this->isusn($usn)) {
            return false;
        }
        $str = strval($usn);
        $str = substr($str, 6, 4);
        $id = (int)$str - 2000;
        return $id;
        ***/
        return 0;
    }
    public function usn2tagid($usn) {
        /***
        if (!$this->isusn($usn)) {
            return false;
        }
        $str = strval($usn);
        $str = substr($str, 9, 5);
        $id = (int)$str - 3000;
        return $id;
        ***/
        return 0;
    }
    public function getusn($token){
        /***
        $array = DB::instance()->table('User_Pool')->where('token = ?',$token)->first();
        if(is_null($array->etc)){
            $this->updateusn(10000020003000,$token); //初始化usn
            $usn = 10000020003000;
        }else{
            $usn = (int)$array->etc;
        }
        return $usn;
        ***/
        return 0;
        
    }
    public function updateusn($usn,$token){
        /***
        if(!$this->isusn($usn)){
            return false;
        }
        $new_data=[
            'etc' => $usn
            ];
        $do1 = DB::instance()->table('User_Pool')->where('token = ?',$token)->updates($new_data);
        return true;
        
        ***/
        return 0;
    }
    public function note_usn_update($id,$usn,$token){
        /***
        $note_usn = strval(100000 + $id);
        
        $notebook_usn = strval(2000 + $this->usn2notebookid($usn));
        $tag_usn = strval(3000 + $this->usn2tagid($usn));
        
        $new_usn = (int)($note_usn.$notebook_usn.$tag_usn);
        return $this->updateusn($new_usn,$token);
        ***/
    }
    public function notebook_usn_update($id,$usn,$token){
        /***
        $note_usn = strval(100000 + $this->usn2noteid($usn));
        
        $notebook_usn = strval(2000 + $id);
        
        $tag_usn = strval(3000 + $this->usn2tagid($usn));
        
        $new_usn = (int)($note_usn.$notebook_usn.$tag_usn);
        //return $note_usn.$notebook_usn.$tag_usn;
        return $this->updateusn($new_usn,$token);
        ***/
    }
    public function tag_usn_update($id,$usn,$token){
        /***
        $note_usn = strval(100000 + $this->usn2noteid($usn));
        $notebook_usn = strval(2000 + $this->usn2notebookid($usn));
        
        $tag_usn = strval(3000 + $id);
        
        $new_usn = (int)($note_usn.$notebook_usn.$tag_usn);
        return $this->updateusn($new_usn,$token);
        ***/
    }
    public function check_notebook_exist($title,$token){
        
        $userid = $this->get_userid($token);
        $tb_name = 'User_'.$userid.'_Notebooks';
        $array = DB::instance()->table($tb_name)->where('title = ?',addslashes($title))->first();
        if(isset($array->title) and isset($array->uuid)){
            return true;
        }
        return false;
    }
    public function check_notebookid_exist($id,$token){
        
        if(!$this->istoken($id)){
            return false;
        }
        $userid = $this->get_userid($token);
        $tb_name = 'User_'.$userid.'_Notebooks';
        $array = DB::instance()->table($tb_name)->where('uuid = ?',$id)->first();
        if(isset($array->title) and isset($array->uuid)){
            return true;
        }
        return false;
    }
    public function check_noteid_exist($id,$token){
        
        if(!$this->istoken($id)){
            return false;
        }
        $userid = $this->get_userid($token);
        $tb_name = 'User_'.$userid.'_Notes';
        $array = DB::instance()->table($tb_name)->where('uuid = ?',$id)->first();
        if(isset($array->title) and isset($array->uuid)){
            return true;
        }
        return false;
    }
    public function firstload(Context $ctx){
        $this->ctx = $ctx;
        
        if(!$this->check_logined($this->get_query('token'))){
            $this->error('token错误');
        }
        $token = $this->get_query('token');
        $this->token = $token;
    }
}