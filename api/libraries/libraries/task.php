<?php 
/*
Copyright GHINK Network Studio
Author: Bigsk(https://www.xiaxinzhe.cn)
*/
if(!defined('IN_SYS')){//Defined entreance security.
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class task{
    //申请Oauth Task
    function applyOauth($token){
        $data=array("token"=>$token);
        $result=sendPost("https://center.ghink.net/v1/interface/api.php?type=authTask",$data);
        $result=json_decode($result,true);
        if($result["status"]=="success"){
            return $result["tid"];
        }else{
            return false;
        }
    }
}