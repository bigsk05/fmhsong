<?php 
/*
Copyright GHINK Network Studio
Author: Bigsk(https://www.xiaxinzhe.cn)
*/
if(!defined('IN_SYS')){//Defined entreance security.
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class ghinkPassport{
    //初始化
    function __construct($sId="",$sKey=""){
        $this->id=$sId;
        $this->key=$sKey;
        $token=new token;
        $this->token=$token->getTokenApp($this->id,$this->key);
        if($this->token==false){
            die("Connect to Ghink Center failed!");
        }
    }
    //检查APP Token是否有效
    function checkTokenApp($token){
        $app=new app;
        return $app->checkToken($this->token,$token);
    }
    //查询APP开发者等级
    function checkLevelApp($token){
        $app=new app;
        return $app->checkLevel($this->token,$token);
    }
    //拼接oauth地址
    function loginAddr($tid,$location){
        return "https://center.ghink.net/v1/interface/oauth.php?type=oauth&tid=".$tid."&location=".$location;
    }
    //检查用户Token
    function checkToken($token){
        $user=new user;
        return $user->checkToken($token,$this->token);
    }
    //申请登录任务
    function applyLogin(){
        $task=new task;
        return $task->applyOauth($this->token);
    }
    //检查登录是否成功
    function checkLogin($tid){
        $token=new token;
        return $token->getTokenUsr($this->token,$tid);
    }
    //读取用户uid
    function getUid($token){
        $user=new user;
        return $user->getUid($token,$this->token);
    }
    //读取用户名
    function getName($uid){
        $user=new user;
        return $user->getName($uid,$this->token);
    }
    //检查用户是否实名
    function checkAuth($uid){
        $auth=new auth;
        return $auth->check($this->token,$uid);
    }
    //读取用户实名信息
    function readAuth($uid){
        $auth=new auth;
        return $auth->getData($this->token,$uid);
    }
}