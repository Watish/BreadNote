<?php

namespace App\Controller;


use Mix\Vega\Context;
use App\Container\DB;

class Auth
{
    public $ctx;
    /**
    * @param Context $ctx
    */
    public function index(Context $ctx) {
        $this->error('Hello World');
    }
    public function login(Context $ctx) {
        $this->ctx = $ctx;
        $this->init($ctx);
        if ($this->get_query('email') and $this->get_query('pwd')) {
            $user = $this->get_query('email');
            $pwd = md5($this->get_query('pwd'));
            //$db = new Mix\Database('mysql:host=127.0.0.1;port=3306;charset=utf8;dbname=leanote', 'root', 'wts20020912');
            $user_info = DB::instance()->table('User_Pool')-> where('user = ?', addslashes($user))->first();
            //$this->error($user_info['id']);
            if (isset($user_info->user)) {
                //$this->error($user_info);
                //$this->error($pwd);
                if (isset($user_info->pwd) and $user_info->pwd == $pwd) {
                    $ctx->JSONP(200, array(
                        'Ok' => true,
                        'Token' => $user_info->token,
                        'UserId' => (int)$user_info->id,
                        'Email' => $user_info->email,
                        'Username' => $user_info->user,
                    ));
                } else {
                    //$this->error($user_info);
                    $this->error('密码错误');
                }
            } else {
                $this->error('用户不存在');
            }
        } else {
            $this->error('参数为空');
        }
    }
    public function register(Context $ctx) {
        $this->ctx = $ctx;
        $this->init($ctx);
        if ($this->get_query('email') and $this->get_query('pwd')) {
            $user = $this->get_query('email');
            $pwd = $this->get_query('pwd');
            $array = DB::instance()->table('User_Pool')-> where('user = ?', addslashes($user))->first();
            if (isset($array->user) or isset($array->id)) {
                $this->error('用户已存在');
            } else {
                //用户不存在
                $data = [
                    'user' => addslashes($user),
                    'pwd' => md5($pwd),
                    'date' => date("Y-m-d H:i:s"),
                    'token' => $this->new_token(),
                ];
                $do1 = DB::instance()->insert('User_Pool', $data);
                if ($do1 and $this->init_user_data($user)) {
                    $ctx->JSONP(200, array(
                        'Ok' => true,
                        'Msg' => '注册成功',
                    ));
                } else {
                    $this->error('注册失败');
                }
            }
        } else {
            $this->error('参数为空');
        }
    }
    public function logout(Context $ctx) {
        $this->ctx = $ctx;
        if (!$this->get_query('token')) {
            $this->error('参数为空');
        }
        $token = $this->get_query('token');
        if (!$this->check_logined($token)) {
            $this->error('token错误');
        }
        $do1 = DB::instance()->table('User_Pool')->where('token = ?', $token)->update('token', $this->new_token());
        if ($do1) {
            $ctx->JSONP(200, array(
                'Ok' => true,
                'Msg' => '注销成功',
            ));
        } else {
            $this->error('注销失败');
        }
    }
    
    private function init(Context $ctx){
        $this->ctx = $ctx;
        $tb_name = 'User_Pool';
        $sql = "CREATE TABLE IF NOT EXISTS $tb_name( ".
        "id INT NOT NULL AUTO_INCREMENT, ".
        "user VARCHAR(20) NOT NULL, ".
        "email VARCHAR(20), ".
        "verified VARCHAR(5), ".
        "logo TEXT, ".
        "token VARCHAR(32), ".
        "pwd VARCHAR(32), ".
        "date DATE, ".
        "etc TEXT, ".
        "PRIMARY KEY ( id ))ENGINE=InnoDB DEFAULT CHARSET=utf8; ";
        
        $do1 = DB::instance()->raw($sql);
        if(!$do1){
           return false;
        }
        $data = [
                    'user' => 'admin',
                    'pwd' => md5('123456'),
                    'date' => date("Y-m-d H:i:s"),
                    'token' => $this->new_token(),
                ];
        $admin = 'admin';
        $do2 = DB::instance()->table('User_Pool')->where("user = ?",$admin)->first();
        if(!isset($do2->user) or empty($do2->user)){
            $do3 = DB::instance()->insert('User_Pool',$data)->lastInsertId();
            $this->init_user_data('admin');
            return true;
        }
        return true;
        
    }
    private function init_user_data($user) {
        $array = DB::instance()->table('User_Pool')-> where('user = ?', addslashes($user))->first();
        if (!isset($array->user) or !isset($array->id)) {
            return false;
        }
        $id = $array->id;

        $tbname = 'User_'.$id.'_Notes';
        $sql = "CREATE TABLE IF NOT EXISTS ".$tbname."( ". //notes
        "id INT NOT NULL AUTO_INCREMENT, ".
        "title VARCHAR(16) NOT NULL, ".
        "content MEDIUMTEXT NOT NULL, ".
        "tags TEXT, ".
        "notebook VARCHAR(32),".
        "istrash VARCHAR(5),".
        "isdeleted VARCHAR(5),".
        "ismarkdown VARCHAR(5),".
        "abstract TEXT,".
        "isblog VARCHAR(5),".
        "files TEXT,".
        "createdtime DATETIME,".
        "updatetime DATETIME,".
        "uuid VARCHAR(32),".
        "etc TEXT, ".
        "PRIMARY KEY ( id ))ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; ";
        //echo $sql;
        $do1 = DB::instance()->raw($sql);

        $tbname = 'User_'.$id.'_Notebooks'; //notebooks
        $sql = "CREATE TABLE IF NOT EXISTS ".$tbname."( ".
        "id INT NOT NULL AUTO_INCREMENT, ".
        "parentnotebookid VARCHAR(25), ".
        "seq INT, ".
        "title VARCHAR(16) NOT NULL, ".
        "isblog VARCHAR(5),".
        "isdeleted VARCHAR(5),".
        "createdtime DATETIME,".
        "updatetime DATETIME,".
        "uuid VARCHAR(32),".
        "etc TEXT, ".
        "PRIMARY KEY ( id ))ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; ";
        $do2 = DB::instance()->raw($sql);

        $tbname = 'User_'.$id.'_Tags';
        $sql = "CREATE TABLE IF NOT EXISTS ".$tbname."( ". //tags
        "id INT NOT NULL AUTO_INCREMENT, ".
        "name VARCHAR(16) NOT NULL, ".
        "include TEXT, ".
        "isdeleted VARCHAR(5),".
        "createdtime DATETIME,".
        "updatetime DATETIME,".
        "uuid VARCHAR(32),".
        "etc TEXT, ".
        "PRIMARY KEY ( id ))ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; ";
        $do3 = DB::instance()->raw($sql);

        $tbname = 'User_'.$id.'_Files';
        $sql = "CREATE TABLE IF NOT EXISTS ".$tbname."( ". //files
        "id INT NOT NULL AUTO_INCREMENT, ".
        "type VARCHAR(12),".
        "title VARCHAR(16) NOT NULL, ".
        "src text NOT NULL,".
        "uuid VARCHAR(32),".
        "etc TEXT, ".
        "PRIMARY KEY ( id ))ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; ";
        $do4 = DB::instance()->raw($sql);
        
        $notebookid = $this->new_token();
        
        $noteid = $this->new_token();

        $new_data = [
            'title' => 'Breadnote',
            'seq' =>1,
            'uuid' => $notebookid,
            'isblog' => false,
            'isdeleted' => false,
            'createdtime' => date("Y-m-d H:i:s"),
            'updatetime' => date("Y-m-d H:i:s")
            ];
        $do5 = DB::instance()->insert('User_'.$id.'_Notebooks',$new_data);
        unset($new_data);
        
        $new_data = [
            'title' => '第一个笔记',
            'content' => '欢迎使用Bread Note',
            'notebook' => $notebookid,
            'istrash' => false,
            'isdeleted' => false,
            'ismarkdown' => false,
            'isblog' => false,
            'createdtime' => date("Y-m-d H:i:s"),
            'updatetime' => date("Y-m-d H:i:s"),
            'uuid' => $noteid
            ];
        $do6 = DB::instance()->insert('User_'.$id.'_Notes',$new_data);
        unset($new_data);
        
        if (!$do1 or !$do2 or !$do3 or !$do4 or !$do5 or !$do6) {
            return false;
        }
        return true;
    }
    private function new_token() {
        $array = 'q.w.e.r.t.y.u.i.o.p.a.s.d.f.g.h.j.k.l.z.x.c.v.b.n.m.0.1.2.3.4.5.6.7.8.9';
        $array = explode('.', $array);
        $num = count($array);
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
    private function get_query($string) {
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
    private function error($str = "失败") {
        $array['Ok'] = false;
        $array['Msg'] = $str;
        $this->ctx->JSONP(200, $array);
        $this->ctx->abort();
    }
    
/***
 *     public function check_logined($token) {
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
    //100000 2000 3000  
    //笔记id 笔记本id 标签id
    //lastsysnctime = time()

    public function isusn($usn) {
        if (!isset($usn) or empty($usn)) {
            return false;
        }
        if (!is_numeric($usn)) {
            return false;
        }
        if ($usn >= 10000020003000 and $usn <= 19999929993999) {
            return true;
        }
    }
    public function usn2noteid($usn) {
        if (!$this->isusn($usn)) {
            return false;
        }
        $str = strval($usn);
        $str = substr($str, 0, 6);
        $id = (int)$str-100000;
        return $id;
    }
    public function usn2notebookid($usn) {
        if (!$this->isusn($usn)) {
            return false;
        }
        $str = strval($usn);
        $str = substr($str, 6, 4);
        $id = (int)$str - 2000;
        return $id;
    }
    public function usn2tagid($usn) {
        if (!$this->isusn($usn)) {
            return false;
        }
        $str = strval($usn);
        $str = substr($str, 9, 5);
        $id = (int)$str - 3000;
        return $id;
    }
    public function getusn($token){
        $array = DB::instance()->table('User_Pool')->where('token = ?',$token)->first();
        if(is_null($array->etc)){
            $this->updateusn(10000020003000,$token); //初始化usn
            $usn = 10000020003000;
        }else{
            $usn = (int)$array->etc;
        }
        return $usn;
        
    }
    public function updateusn($usn,$token){
        if(!$this->isusn($usn)){
            return false;
        }
        $new_data=[
            'etc' => $usn
            ];
        $do1 = DB::instance()->table('User_Pool')->where('token = ?',$token)->updates($new_data);
        return true;
    }
    public function note_usn_update($id,$usn,$token){
        $note_usn = strval(100000 + $id);
        
        $notebook_usn = strval(2000 + $this->usn2notebookid($usn));
        $tag_usn = strval(3000 + $this->usn2tagid($usn));
        
        $new_usn = (int)($note_usn.$notebook_usn.$tag_usn);
        return $this->updateusn($new_usn,$token);
    }
    public function notebook_usn_update($id,$usn,$token){
        $note_usn = strval(100000 + $this->usn2noteid($usn));
        
        $notebook_usn = strval(2000 + $id);
        
        $tag_usn = strval(3000 + $this->usn2tagid($usn));
        
        $new_usn = (int)($note_usn.$notebook_usn.$tag_usn);
        //return $note_usn.$notebook_usn.$tag_usn;
        return $this->updateusn($new_usn,$token);
    }
    public function tag_usn_update($id,$usn,$token){
        $note_usn = strval(100000 + $this->usn2noteid($usn));
        $notebook_usn = strval(2000 + $this->usn2notebookid($usn));
        
        $tag_usn = strval(3000 + $id);
        
        $new_usn = (int)($note_usn.$notebook_usn.$tag_usn);
        return $this->updateusn($new_usn,$token);
    }
  ***/
    
}