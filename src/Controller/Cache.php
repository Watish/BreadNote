<?php

/***
 * 静态文件缓存
***/

namespace App\Controller;
use Mix\Vega\Context;

class Cache{
    
    public $key;
    public $value;
    public $CreatedTime;
    public $LastUpdateTime;
    public $ExpiredTime;
    
    private $path;
    private $lock;
    
    
    
    
    public $expired;
    public $exists = false;
    
    public function __construct($key){
        if(empty($key)){
            $this->lock = true;
            return false;
        }
        $key = md5($key);
        $path = $this->cachePath().$key.'.cache';
        $this->path = $path;
        
        if(file_exists($path)){
            //文件存在
            $this->exists = true;
            $array = json_decode(file_get_contents($path),true);
            $this->value = $array['Value'];
            
            $ExpiredTime = $array['ExpiredTime'];
            $LastUpdateTime = $array['LastUpdateTime'];
            
            if($ExpiredTime<0){
                $this->expired = false;
            }else{
                if(time() - $LastUpdateTime >= $ExpiredTime){
                    $this->expired = true;
                }else{
                    $this->expired = false;
                }
            }
            
            
        }else{
            $this->exists = false;
            $this->expired = true;
            //文件不存在
            
            
            
        }
        
    }
    
    public function Set($value,$time=-1){
        if($this->lock){
            return false;
        }
        
        $new_time = time();
        
        $data = array(
            'Md5-Key' => $this->key,
            'CreatedTime' => $new_time,
            'LastUpdateTime' => $new_time,
            'ExpiredTime' => $time,
            'Value' => $value,
        );
        
        $this->CreatedTime = $new_time;
        $this->LastUpdateTime = $new_time;
        $this->flushExpired();
        $this->value = $value;
        
        $json = json_encode($data);
        $this->write($json);
    }
    public function Read(){
        if($this->lock){
            return false;
        }
        return $this->value;
    }
    public function Status(){
        if($this->lock){
            return false;
        }
        $array = array(
            '$key' => $this->key,
            '$value' =>$this->value,
            '$path' => $this->path,
            '$lock' =>$this->lock,
            '$expired'=>$this->expired,
            '$exists'=>$this->exists,
            '$CreatedTime'=>$this->CreatedTime,
            '$LastUpdateTime'=>$this->LastUpdateTime,
            '$ExpiredTime'=>$this->ExpiredTime
        );
        return $array;
    }
    public function Delete(){
        if($this->lock){
            return false;
        }
        
        if(unlink($this->path)){
            $this->exists = false;
            unset($this->value);
            unset($this->ExpiredTime);
            unset($this->CreatedTime);
            unset($this->LastUpdateTime);
            unset($this->expired);
            return true;
        }else{
            return false;
        }
    }
    
    
    private function cachePath(){
        $path = dirname(__FILE__);
        $path = str_replace('/src/Controller','/cache',$path).'/';
        if(!is_dir($path)){
            mkdir($path);
        }
        return $path;
    }
    private function write($data){
        $myfile = fopen($this->path, "w");
        fwrite($myfile, $data);
        fclose($myfile);
        $this->exists = true;
    }
    private function flushExpired(){
        if($this->ExpiredTime<0){
                $this->expired = false;
            }else{
                if(time() - $this->LastUpdateTime >= $this->ExpiredTime){
                    $this->expired = true;
                }else{
                    $this->expired = false;
                }
            }
    }
}