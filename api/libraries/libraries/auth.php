<?php 
/*
Copyright GHINK Network Studio
Author: Bigsk(https://www.xiaxinzhe.cn)
*/
if(!defined('IN_SYS')){//Defined entreance security.
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class auth{
    //查询用户是否实名
    function check($token,$uid){
        $data=array("token"=>$token,"uid"=>$uid);
        $result=sendPost("https://center.ghink.net/v1/interface/api.php?type=checkAuth",$data);
        $result=json_decode($result,true);
        if($result["status"]=="success"){
            return $result["auth"];
        }else{
            return false;
        }
    }
    //查询用户实名信息
    function getData($token,$uid){
        $data=array("token"=>$token,"uid"=>$uid);
        $result=sendPost("https://center.ghink.net/v1/interface/api.php?type=getAuthInfo",$data);
        $result=json_decode($result,true);
        if($result["status"]=="success"){
            return $result["result"];
        }else{
            return false;
        }
    }
}