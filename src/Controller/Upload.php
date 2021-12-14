<?php

namespace App\Controller;

use Mix\Vega\Context;
use App\Container\DB;

class Upload extends Base{
    public $ctx;
    public function uploadAttach(Context $ctx){
        $this->ctx = $ctx;
        //parent::error($this->imgPath());
        parent::firstload($ctx);
        $token = $this->token;
        
        /**
        if(!isset($_FILES[img][name])){
            parent::error("文件上传失败");
        }
        */
        
        $file = $ctx->formFile('img');
        //parent::error($_FILES[img][name]);
        
        
        $userid = parent::get_userid($token);
        

        $name = $file->getClientFilename();
        $type = explode(".",$name);
        $type = $type[count($type)-1];
        
        $do = false;
        switch($type){
            case $type=="jpg":
                $do = true;
                break;
            case $type=="JPG":
                $do = true;
                break;
            case $type=="png":
                $do = true;
                break;
            case $type=="PNG":
                $do = true;
                break;
            case $type=="gif":
                $do = true;
                break;
            case $type=="GIF":
                $do = true;
                break;
            case empty($type):
                break;
            default:
                break;
                //parent::error('上传文件非法');
        }
        if(!$do){
            parent::error('上传文件非法');
        }
        $uuid = parent::new_token();
        $new_name = $uuid.'.'.$type;
        $targetPath = $this->imgPath().$new_name;
        
        //parent::error($targetPath);
        $file->moveTo($targetPath);
        
        //$targetPath = $this->imgPath.$file->getClientFilename();
        
        $tb_name = 'User_'.$userid.'_Files';
        
        $new_data = [
            'type' => $type,
            'title' => $type,
            'src' => $targetPath,
            'uuid' => $uuid,
            ];
        
        $do = DB::instance()->insert($tb_name,$new_data);
        
        $json = array(
            'type' => $type,
            'id' => $uuid,
            'src' => '/uploads/'.$this->token.'/'.$new_name,
            );
        
        
        $ctx->JSONP(200,$json);
    }
    private function imgPath(){
        $path = dirname(__FILE__);
        $path = str_replace('/src/Controller','/storage/uploads',$path).'/'.$this->token;
        if(!is_dir($path)){
            mkdir($path);
        }
        return $path.'/';
    }
}