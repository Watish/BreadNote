<?php

namespace App\Controller;

use Mix\Vega\Context;
use App\Container\DB;

class Index
{
    public function index(Context $ctx){
        if(!file_exists(BASE_DIR.'conf/config.json')){
            $ctx->JSONP(500,array(
                'Ok' => false,
                'Msg' => '请先配置好数据库(conf/config.json)'
                ));
            $ctx->abort();
        }
        $code = file_get_contents(__DIR__.'/../../storage/index.html');
        $ctx->string(200,$code);
    }
    
    public function hello(Context $ctx){
        //$ctx->string(200,BASE_DIR);
        
    }

}