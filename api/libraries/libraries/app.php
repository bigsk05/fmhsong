<?php 
/*
Copyright GHINK Network Studio
Author: Bigsk(https://www.xiaxinzhe.cn)
*/
if(!defined('IN_SYS')){//Defined entreance security.
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class app{
    //查询APP Token是否有效
    function checkToken($token,$tokenCheck){
        $data=array("token"=>$token,"tokenCheck"=>$tokenCheck);
        $result=sendPost("https://center.ghink.net/v1/interface/api.php?type=checkAppToken",$data);
        $result=json_decode($result,true);
        if($result["status"]=="success"){
            return $result["result"];
        }else{
            return false;
        }
    }
    //获取APP等级
    function checkLevel($token,$tokenCheck){
        $data=array("token"=>$token,"tokenCheck"=>$tokenCheck);
        $result=sendPost("https://center.ghink.net/v1/interface/api.php?type=checkAppLevel",$data);
        $result=json_decode($result,true);
        if($result["status"]=="success"){
            return $result["result"];
        }else{
            return false;
        }
    }
}