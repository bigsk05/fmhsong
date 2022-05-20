<?php 
/*
Copyright GHINK Network Studio
Author: Bigsk(https://www.xiaxinzhe.cn)
*/
if(!defined('IN_SYS')){//Defined entreance security.
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class user{
    //取回UID
    function getUid($tokenUsr,$tokenApp){
        $data=array("tokenUsr"=>$tokenUsr,"tokenApp"=>$tokenApp);
        $result=sendPost("https://center.ghink.net/v1/interface/api.php?type=getUid",$data);
        $result=json_decode($result,true);
        if($result["status"]=="success"){
            return $result["uid"];
        }else{
            return false;
        }
    }
    //检查用户Token是否有效
    function checkToken($tokenUsr,$tokenApp){
        $data=array("tokenUsr"=>$tokenUsr,"tokenApp"=>$tokenApp);
        $result=sendPost("https://center.ghink.net/v1/interface/api.php?type=checkToken",$data);
        $result=json_decode($result,true);
        if($result["status"]=="success"){
            return true;
        }else{
            return false;
        }
    }
    //取回昵称
    function getName($uid,$token){
        $data=array("uid"=>$uid,"token"=>$token);
        $result=sendPost("https://center.ghink.net/v1/interface/api.php?type=getName",$data);
        $result=json_decode($result,true);
        if($result["status"]=="success"){
            return $result["name"];
        }else{
            return false;
        }
    }
}