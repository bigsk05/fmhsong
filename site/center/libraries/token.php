<?php 
/*
Copyright GHINK Network Studio
Author: Bigsk(https://www.xiaxinzhe.cn)
*/
if(!defined('IN_SYS')){//Defined entreance security.
    header('HTTP/1.1 403 Forbidden');
    exit(file_get_contents(setting::root."template/403.html"));
}

class token{
    //取回应用Token
    function getTokenApp($sId,$sKey){
        $data=array("sid"=>$sId,"skey"=>$sKey);
        $result=sendPost("https://center.ghink.net/v1/interface/api.php?type=appToken",$data);
        $result=json_decode($result,true);
        if($result["status"]=="success"){
            return $result["token"];
        }else{
            return false;
        }
    }
    //取回用户Token
    function getTokenUsr($token,$tid){
        $data=array("token"=>$token,"tid"=>$tid);
        $result=sendPost("https://center.ghink.net/v1/interface/api.php?type=authBack",$data);
        $result=json_decode($result,true);
        if($result["status"]=="success"){
            return $result["token"];
        }else{
            return false;
        }
    }
}