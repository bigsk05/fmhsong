<?php 
/*
Copyright GHINK Network Studio
Author: Bigsk(https://www.xiaxinzhe.cn)
*/
error_reporting(0);
if(!defined('IN_SYS')){//Defined entreance security.
    header('HTTP/1.1 403 Forbidden');
    exit(file_get_contents(setting::root."template/403.html"));
}
//哈希库
class hash{
    //MD5计算，用于接口对接的预留
    function cryptMD5($disencrypted){
        return md5($disencrypted);
    }
    //密码生成算法，非时效性
    function generatePass($pass){
        for ($i=0;$i<=strlen($pass);$i++){
            $pass=$this->cryptMD5($pass);
        }
        return $pass;
    }
    //Token生成算法，时效性
    function generateToken($value){
        $time=time();
        for ($i=0;$i<=strlen($value);$i++){
            $result=$result+$this->cryptMd5($value)+$time;
        }
        return $this->cryptMd5($result);
    }
    //sId与sKey生成算法，唯一性
    function generateSIdKey($name){
        $time=time();
        for ($i=0;$i<=strlen($name);$i++){
            @$result=$result+$name+$this->cryptMd5($name)+$time;
        }
        $sId=$this->cryptMd5($result);
        for ($i=0;$i<=strlen($name);$i++){
            @$result=$result+$time+$this->cryptMd5($name)+$name;
        }
        $sKey=$this->cryptMd5($result);
        return array($sId,$sKey);
    }
}