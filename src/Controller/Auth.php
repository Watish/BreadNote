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
        $this->error('Hello World'); //Hello World
    }
    public function login(Context $ctx) { //用户登录
        $this->ctx = $ctx;//传递局部变量
        $this->init($ctx);//调用init函数，主要是初始化用户数据表和admin用户
        if ($this->get_query('email') and $this->get_query('pwd')) {//获取url参数email，pwd
            $user = $this->get_query('email');
            $pwd = md5($this->get_query('pwd'));//对密码进行md5加密
            $user_info = DB::instance()->table('User_Pool')-> where('user = ?', addslashes($user))->first();//查询用户数据
            if (isset($user_info->user)) {//判断用户是否存在
                if (isset($user_info->pwd) and $user_info->pwd == $pwd) {//判断密码是否正确
                    $ctx->JSONP(200, array(//返回json
                        'Ok' => true,
                        'Token' => $user_info->token,
                        'UserId' => (int)$user_info->id,
                        'Email' => $user_info->email,
                        'Username' => $user_info->user,
                    ));
                } else { //密码错误，返回自定义错误
                    $this->error('密码错误');
                }
            } else {//用户不存在
                $this->error('用户不存在');
            }
        } else {//url参数为空
            $this->error('参数为空');
        }
    }
    public function register(Context $ctx) {//用户注册
        $this->ctx = $ctx;//传递局部变量
        $this->init($ctx);//调用init函数，主要是初始化用户数据表和admin用户
        if ($this->get_query('email') and $this->get_query('pwd')) {//获取url参数email，pwd
            $user = $this->get_query('email');
            $pwd = $this->get_query('pwd');
            $array = DB::instance()->table('User_Pool')-> where('user = ?', addslashes($user))->first();//查询用户数据
            if (isset($array->user) or isset($array->id)) {//用户存在，返回错误
                $this->error('用户已存在');
            } else {
                //用户不存在
                $data = [
                    'user' => addslashes($user),//对用户名进行转义
                    'pwd' => md5($pwd),//对密码进行md5加密
                    'date' => date("Y-m-d H:i:s"),//注册日期
                    'token' => $this->new_token(),//创建一个的token
                ];
                $do1 = DB::instance()->insert('User_Pool', $data);//插入用户数据
                if ($do1 and $this->init_user_data($user)) {//操作返回true并且初始化用户数据成功
                    $ctx->JSONP(200, array(
                        'Ok' => true,
                        'Msg' => '注册成功',
                    ));
                } else {//返回自定义错误
                    $this->error('注册失败');
                }
            }
        } else {
            $this->error('参数为空');//url参数为空
        }
    }
    public function logout(Context $ctx) {//用户注销
        $this->ctx = $ctx;//传递局部变量
        if (!$this->get_query('token')) {//判断url参数token
            $this->error('参数为空');
        }
        $token = $this->get_query('token');
        
        $token_cache = new Cache($token);
        if($token_cache->exists){
            $token_cache->Delete();//删除token缓存
        }
        
        if (!$this->check_logined($token)) {//判断token是否存在
            $this->error('token错误');
        }
        $do1 = DB::instance()->table('User_Pool')->where('token = ?', $token)->update('token', $this->new_token());//读取用户数据
        if ($do1) {
            $ctx->JSONP(200, array(
                'Ok' => true,
                'Msg' => '注销成功',
            ));
        } else {
            $this->error('注销失败');
        }
    }

    private function init(Context $ctx) {//数据表初始方法
        $this->ctx = $ctx;
        $tb_name = 'User_Pool';//创建用户数据表，有则不建，无则建
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

        $do1 = DB::instance()->raw($sql);//执行sql
        if (!$do1) {
            return false;
        }
        $data = [
            'user' => 'admin',
            'pwd' => md5('123456'),
            'date' => date("Y-m-d H:i:s"),
            'token' => $this->new_token(),
        ];//admin数据
        $admin = 'admin';
        $do2 = DB::instance()->table('User_Pool')->where("user = ?", $admin)->first();//获取admin数据
        if (!isset($do2->user) or empty($do2->user)) {//admin不存在就创建
            $do3 = DB::instance()->insert('User_Pool', $data)->lastInsertId();
            $this->init_user_data('admin');
            return true;
        }
        return true;

    }
    private function init_user_data($user) {//初始化用户数据
        $array = DB::instance()->table('User_Pool')-> where('user = ?', addslashes($user))->first();
        if (!isset($array->user) or !isset($array->id)) {
            return false;
        }
        $id = $array->id;//获取用户id

        $tbname = 'User_'.$id.'_Notes';//创建数据表 User_[id]_Notes
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

        $tbname = 'User_'.$id.'_Notebooks'; //创建数据表 User_[id]_Notebooks
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

        $tbname = 'User_'.$id.'_Tags';//创建数据表 User_[id]_Tags
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

        $tbname = 'User_'.$id.'_Files';//创建数据表 User_[id]_Files
        $sql = "CREATE TABLE IF NOT EXISTS ".$tbname."( ". //files
        "id INT NOT NULL AUTO_INCREMENT, ".
        "type VARCHAR(12),".
        "title VARCHAR(16) NOT NULL, ".
        "src text NOT NULL,".
        "uuid VARCHAR(32),".
        "etc TEXT, ".
        "PRIMARY KEY ( id ))ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; ";
        $do4 = DB::instance()->raw($sql);

        $notebookid = $this->new_token();//为第一个笔记本新建一个token

        $noteid = $this->new_token();//为第一个笔记新建一个token

        $new_data = [
            'title' => 'Breadnote',
            'seq' => 1,
            'uuid' => $notebookid,
            'isblog' => false,
            'isdeleted' => false,
            'createdtime' => date("Y-m-d H:i:s"),
            'updatetime' => date("Y-m-d H:i:s")
        ];//笔记本数据
        $do5 = DB::instance()->insert('User_'.$id.'_Notebooks', $new_data);//插入第一个笔记本数据
        unset($new_data);//释放new_data变量

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
        ];//笔记数据
        $do6 = DB::instance()->insert('User_'.$id.'_Notes', $new_data);//插入第一个笔记数据
        unset($new_data);//释放new_data变量

        if (!$do1 or !$do2 or !$do3 or !$do4 or !$do5 or !$do6) {//判断所有操作有无错误
            return false;
        }
        return true;
    }
    private function new_token() {//新建token方法
        $array = 'q.w.e.r.t.y.u.i.o.p.a.s.d.f.g.h.j.k.l.z.x.c.v.b.n.m.0.1.2.3.4.5.6.7.8.9';//数字和小写英文表
        $array = explode('.', $array);//转变成数组
        $num = count($array);//获取数组长度
        for ($i = 1; $i <= 13; $i++) {//创建一个长度为13位的随机数字英文组合
            $a = $array[rand(0, $num-1)];
            if (empty($a) and !isset($a)) {
                $i = $i-1;
            } else {
                $res = $res.$a;
            }
        }
        return $res.date("ymdhis");//在尾部加上时间，防止重复
    }
    private function get_query($string) {//获取GET或者POST的url参数
        $ctx = $this->ctx;//传递局部变量
        if (empty($string)) {//判断参数是否为空
            return false;
        }
        if ($ctx->getQuery($string) or $ctx->getPostForm($string)) {//判断GET参数或者POST参数是否存在
            if ($ctx->getQuery($string)) {//GET参数存在拿GET的
                return $ctx->getQuery($string);
            }
            if ($ctx->getPostForm($string)) {//POST参数存在的拿POST的
                return $ctx->getPostForm($string);
            }
            return false;
        }
        return false;
    }
    private function error($str = "失败") {//自定义异常方法
        $array['Ok'] = false;//Ok设为false
        $array['Msg'] = $str;//自定义异常信息
        $this->ctx->JSONP(200, $array);//返回json
        $this->ctx->abort();//终止代码运行
    }
    private function check_logined($token) {//判断登陆方法
        $array = DB::instance()->table('User_Pool')-> where('token = ?', addslashes($token))->first();//通过token查询用户数据
        if (isset($array->user)) {//用户存在
            return true;
        } else {//不存在
            return false;
        }
    }
}