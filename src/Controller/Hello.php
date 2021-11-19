<?php

namespace App\Controller;

use Mix\Vega\Context;

class Hello
{

    /**
     * @param Context $ctx
     */
    public function index(Context $ctx)
    {
        //$ctx->string(200, 'hello, world!');
        $mycache = new Cache('Hello World');
        if($mycache->exists and !$mycache->expired){
            $data = $mycache->value;
            $ctx->JSONP(200,$data);
        }else{
            $mycache->Set('这是缓存里的数据',10);
            $ctx->JSONP(200,'这不是缓存里的数据');
        }
        
    }

}
