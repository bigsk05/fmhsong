<?php 
/*
Copyright GHINK Network Studio
Author: Bigsk(https://www.xiaxinzhe.cn)
*/
if(!defined('IN_SYS')){//Defined entreance security.
    header('HTTP/1.1 403 Forbidden');
    exit(file_get_contents(setting::root."template/403.html"));
}
//全局用户管理
class user{
    //用户注册函数
    function reg($name,$pass,$email,$ip){
        //记录时间戳
        $ts=time();
        //检查是否重复注册
        if(!$this->checkExist($email)){
            //用户分区检查
            $area=json_decode(file_get_contents("https://api.ghink.net/ip/?ip=".$ip),true)["city"];
            if($area==""){
                $area="global";
                $mail=new mail;
                //发送验证邮件
                $result=$mail->sendReg($email,$name,$pass,$area,$ip);
                return $result;
            }else{
                $area="china";
                $mail=new mail;
                //发送验证邮件
                $result=$mail->sendReg($email,$name,$pass,$area,$ip);
                return $result;
            }
        }else{
            return false;
        }
    }
    function login($email,$pass,$ip,$aid=0){
        //记录时间戳
        $ts=time();
        //创建数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        if($this->checkRight($email,$pass)){
            $ipaddr=new ip;
            $id=$this->getId($email);
            $lastIP=$ipaddr->getLast($id);
            $ipInfo=json_decode(file_get_contents("https://api.ghink.net/ip/?ip=".$ip),true);
            $ipInfoLast=json_decode(file_get_contents("https://api.ghink.net/ip/?ip=".$lastIP),true);
            if($ipInfo["city"]!=$ipInfoLast["city"]){
                $mail=new mail;
                $mail->sendWarn($email,$ipInfo["city"],$ipInfoLast["city"]);
            }
            //更新最后登录时间
            $sql=$conn->prepare("UPDATE `users` SET `logTime`=?,`logIP`=? WHERE `email`=?");
            $sql->bind_param('iss',$ts,$ip,$email);
            $sql->execute();
            $token=$this->giveToken($email);
            //记录登录信息
            $sql=$conn->prepare("INSERT INTO logHistory (id, ip, time, token, aid, status) 
                                VALUES(?,?,?,?,?,?)");
            $status="success";
            $sql->bind_param('isisis',$id,$ip,$ts,$token,$aid,$status);
            $sql->execute();
            $conn->close();
            return $token;
        }else{
            //记录登陆信息
            $sql=$conn->prepare("INSERT INTO logHistory (id, ip, time, token, aid, status) 
                                VALUES(?,?,?,NULL,NULL,?)");
            $status="failed";
            $sql->bind_param('isis',$id,$ip,$ts,$status);
            $sql->execute();
            $conn->close();
            return false;
        }
    }
    //生成用户Token
    function giveToken($email){
        //记录时间戳
        $ts=time();
        //实例化对象与数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        $id=$this->getId($email);
        //检测Token是否已经存在
        $sql=$conn->prepare("SELECT `value`,`createTime`,`renewal` FROM `tokens` WHERE `id`=?");
        $sql->bind_param('i',$id);
        $sql->execute();
        $sql->bind_result($value,$createTime,$renewal);
        while($sql->fetch()){
          $rValue=$value;
          $rCreateTime=$createTime;
          $rRenewal=$renewal;
        }
        if($rValue=="" || (($ts-$rCreateTime)>604800 && $rRenewal=="") || (($ts-$rCreateTime)>1209600 && $rRenewal=="true")){
            $hash=new hash;
            //计算Token
            $token=$hash->generateToken($email);
            $sql=$conn->prepare("INSERT INTO `tokens`(`tid`, `type`, `createTime`, `renewal`, `id`, `value`) 
                                VALUES (NULL,'user',?,NULL,?,?)");
            $sql->bind_param('iis',$ts,$id,$token);
            $sql->execute();
            loger($conn,"user-giveToken","success",$ts,"info",json_encode(array("email"=>$email)));
            $conn->close();
            return $token;
        }else{
            loger($conn,"user-giveToken","failed",$ts,"info",json_encode(array("email"=>$email)));
            $conn->close();
            return $rValue;
        }
    }
    //从Token获取用户ID
    function getIdToken($token){
        //记录时间戳
        $ts=time();
        if($this->checkToken($token)){
            //创建数据库连接
            $conn=new mysqli(database::addr,database::user,database::pass,database::name);
            //获取Token
            $sql=$conn->prepare("SELECT id FROM `tokens` WHERE `value`=?");
            $sql->bind_param('s',$token);
            $sql->execute();
            $sql->bind_result($id);
            while($sql->fetch()){
            $rId=$id;
            }
            if($rId!=""){
                loger($conn,"user-getIdToken","success",$ts,"info",json_encode(array("token"=>$token)));
                $conn->close();
                return $rId;
            }else{
                loger($conn,"user-getIdToken","failed",$ts,"info",json_encode(array("token"=>$token)));
                $conn->close();
                return false;
            }
        }else{
            return false;
        }
    }
    //销毁Token
    function destroyToken($token){
        //记录时间戳
        $ts=time();
        //创建数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        if($this->checkToken($token)){
            $sql=$conn->prepare("UPDATE `tokens` SET `destroy`=\"true\" WHERE `value`=?");
            $sql->bind_param('s',$token);
            $sql->execute();
            loger($conn,"user-destroyToken","success",$ts,"info",json_encode(array("token"=>$token)));
            $conn->close();
            return true;
        }else{
            loger($conn,"user-destroyToken","failed",$ts,"info",json_encode(array("token"=>$token)));
            $conn->close();
            return false;
        }
    }
    //检查用户Token是否有效
    function checkToken($token){
        //记录时间戳
        $ts=time();
        //创建数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        //获取Token
        $sql=$conn->prepare("SELECT createTime,renewal,destroy FROM `tokens` WHERE `value`=?");
        $sql->bind_param('s',$token);
        $sql->execute();
        $sql->bind_result($createTime,$renewal,$destroy);
        while($sql->fetch()){
          $rCreateTime=$createTime;
          $rRenewal=$renewal;
          $rDestroy=$destroy;
        }
        //判断Token时效
        if((($ts-$rCreateTime)>604800 && $rRenewal=="") || (($ts-$rCreateTime)>1209600 && $rRenewal=="true") || $rDestroy=="true"){
            loger($conn,"user-checkToken","success",$ts,"info",json_encode(array("token"=>$token)));
            $conn->close();
            return false;
        }else{
            loger($conn,"user-checkToken","success",$ts,"info",json_encode(array("token"=>$token)));
            $conn->close();
            return true;
        }
    }
    //Token续期函数
    function renewalToken($uToken,$aToken){
        //Log Rec
        //Check token of application
        //Database operate
    }
    function ban(){
    }
    //获取用户所属区域
    function getArea($email){
        //记录时间戳
        $ts=time();
        //创建数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        //获取数据库返回值
        $sql=$conn->prepare("SELECT area FROM `users` WHERE `email`=?");
        $sql->bind_param('s',$email);
        $sql->execute();
        $sql->bind_result($area);
        while($sql->fetch()){
            $rArea=$area;
        }
        loger($conn,"user-getArea","success",$ts,"info",json_encode(array("email"=>$email)));
        $conn->close();
        return $rArea;
    }
    //使用UID获取用户所属区域
    function getAreaUid($uid){
        //记录时间戳
        $ts=time();
        //创建数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        //获取数据库返回值
        $sql=$conn->prepare("SELECT area FROM `users` WHERE `uid`=?");
        $sql->bind_param('i',$uid);
        $sql->execute();
        $sql->bind_result($area);
        while($sql->fetch()){
            $rArea=$area;
        }
        loger($conn,"user-getAreaUid","success",$ts,"info",json_encode(array("uid"=>$uid)));
        $conn->close();
        return $rArea;
    }
    //获取用户地区
    function getAreaId($id){
        //记录时间戳
        $ts=time();
        //创建数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        //获取数据库返回值
        $sql=$conn->prepare("SELECT area FROM `users` WHERE `id`=?");
        $sql->bind_param('i',$id);
        $sql->execute();
        $sql->bind_result($area);
        while($sql->fetch()){
            $rArea=$area;
        }
        loger($conn,"user-getAreaId","success",$ts,"info",json_encode(array("id"=>$id)));
        $conn->close();
        return $rArea;
    }
    //从id获取用户uid
    function getUidId($id){
        //记录时间戳
        $ts=time();
        //创建数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        //获取数据库返回值
        $sql=$conn->prepare("SELECT uid FROM `users` WHERE `id`=?");
        $sql->bind_param('i',$id);
        $sql->execute();
        $sql->bind_result($uid);
        while($sql->fetch()){
            $rUid=$uid;
        }
        loger($conn,"user-getUidId","success",$ts,"info",json_encode(array("id"=>$id)));
        $conn->close();
        return $rUid;
    }    
    //从uid获取用户id
    function getIdUid($uid){
        //记录时间戳
        $ts=time();
        //创建数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        //获取数据库返回值
        $sql=$conn->prepare("SELECT id FROM `users` WHERE `uid`=?");
        $sql->bind_param('i',$uid);
        $sql->execute();
        $sql->bind_result($id);
        while($sql->fetch()){
            $rId=$id;
        }
        loger($conn,"user-getIdUid","success",$ts,"info",json_encode(array("uid"=>$uid)));
        $conn->close();
        return $rId;
    }
    //从uid获取用户昵称
    function getNameUid($uid){
        //记录时间戳
        $ts=time();
        //创建数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        //获取数据库返回值
        $sql=$conn->prepare("SELECT name FROM `users` WHERE `uid`=?");
        $sql->bind_param('i',$uid);
        $sql->execute();
        $sql->bind_result($name);
        while($sql->fetch()){
            $rName=$name;
        }
        loger($conn,"user-getNameUid","success",$ts,"info",json_encode(array("uid"=>$uid)));
        $conn->close();
        return $rName;
    }
    //获取用户全局id
    function getId($email){
        //记录时间戳
        $ts=time();
        //创建数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        //获取数据库返回值
        $sql=$conn->prepare("SELECT id FROM `users` WHERE `email`=?");
        $sql->bind_param('s',$email);
        $sql->execute();
        $sql->bind_result($id);
        while($sql->fetch()){
            $rId=$id;
        }
        loger($conn,"user-getId","success",$ts,"info",json_encode(array("email"=>$email)));
        $conn->close();
        return $rId;
    }
    //获取用户昵称
    function getName($email){
        //记录时间戳
        $ts=time();
        //创建数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        //获取数据库返回值
        $sql=$conn->prepare("SELECT name FROM `users` WHERE `email`=?");
        $sql->bind_param('s',$email);
        $sql->execute();
        $sql->bind_result($name);
        while($sql->fetch()){
            $rName=$name;
        }
        loger($conn,"user-getName","success",$ts,"info",json_encode(array("email"=>$email)));
        $conn->close();
        return $rName;
    }
    //用户存在性检测
    function checkExist($email){
        //记录时间戳
        $ts=time();
        //创建数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        //获取数据库返回值
        $sql=$conn->prepare("SELECT pass FROM `users` WHERE `email`=?");
        $sql->bind_param('s',$email);
        $sql->execute();
        $sql->bind_result($pass);
        while($sql->fetch()){
            $rPass=$pass;
        }
        loger($conn,"user-checkRight","success",$ts,"info",json_encode(array("email"=>$email)));
        $conn->close();
        if($rPass!=""){
            return true;
        }else{
            return false;
        }
    }
    //用户信息是否正确检测
    function checkRight($email,$passwd){
        //记录时间戳
        $ts=time();
        //创建数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        //获取用户基础信息
        $sql=$conn->prepare("SELECT pass FROM `users` WHERE `email`=?");
        $sql->bind_param('s',$email);
        $sql->execute();
        $sql->bind_result($pass);
        while($sql->fetch()){
            $rPass=$pass;
        }
        loger($conn,"user-checkRight","success",$ts,"info",json_encode(array("email"=>$email)));
        $conn->close();
        $hash=new hash();
        $input=$hash->generatePass($passwd);
        //加密密码并比对
        if($rPass==$input){
            return true;
        }else{
            return false;
        }
    }
}