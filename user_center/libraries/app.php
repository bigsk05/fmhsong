<?php 
/*
Copyright GHINK Network Studio
Author: Bigsk(https://www.xiaxinzhe.cn)
*/
if(!defined('IN_SYS')){//Defined entreance security.
    header('HTTP/1.1 403 Forbidden');
    exit(file_get_contents(setting::root."template/403.html"));
}
//全局应用与开发者管理工具
class developer{
    //检查开发者权限
    function checkLevel($did){
        //记录时间戳
        $ts=time();
        //创建数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        $sql=$conn->prepare("SELECT level FROM `developers` WHERE `did`=?");
        $sql->bind_param('i',$did);
        $sql->execute();
        $sql->bind_result($level);
        while($sql->fetch()){
            $rLevel=$level;
        }
        loger($conn,"developer-checkLevel","success",$ts,"info",json_encode(array("did"=>$did)));
        $conn->close();
        return $rLevel;
    }
    //新增开发者
    function create($uid,$level="personal"){
        //记录时间戳
        $ts=time();
        //创建数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        if(!$this->checkExist($uid)){
            $user=new user;
            $id=$user->getIdUid($uid);
            $area=$user->getAreaId($uid);
            $sql=$conn->prepare("INSERT INTO developers (
                did,uid,area,level) VALUES(
                NULL,?,?,?)");
            $sql->bind_param('iss',$uid,$area,$level);
            $sql->execute();
            //取回开发者id
            $did=mysqli_insert_id($conn);
            //更新全局开发者数
            $sql=$conn->prepare("SELECT deveNum FROM `system` WHERE 1");
            $sql->execute();
            $sql->bind_result($deveNum);
            while($sql->fetch()){
                $rDdeveNum=$deveNum;
            }
            $sql=$conn->prepare("UPDATE `system` SET `deveNum`=? WHERE 1");
            $rDdeveNum=$rDdeveNum+1;
            $sql->bind_param('i',$rDdeveNum);
            $sql->execute();
            loger($conn,"developer-create","success",$ts,"info",json_encode(array("uid"=>$uid)));
            $conn->close();
            return $did;
        }else{
            loger($conn,"developer-create","failed",$ts,"info",json_encode(array("uid"=>$uid)));
            $conn->close();
            return false;
        }
    }
    function quit($uid){
        //记录时间戳
        $ts=time();
        //创建数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        if($this->checkExist($uid)){
            //移除开发者所有APP
            $app=new app;
            $app->removeForDeve($did);
            loger($conn,"developer-quit","success",$ts,"info",json_encode(array("uid"=>$uid)));
            $conn->close();
            return $did;
        }else{
            loger($conn,"developer-quit","failed",$ts,"info",json_encode(array("uid"=>$uid)));
            $conn->close();
            return false;
        }
    }
    function checkExist($uid){
        //记录时间戳
        $ts=time();
        //创建数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        $sql=$conn->prepare("SELECT uid FROM `developers` WHERE `uid`=?");
        $sql->bind_param('i',$uid);
        $sql->execute();
        $sql->bind_result($uid);
        while($sql->fetch()){
            $rUid=$uid;
        }
        loger($conn,"developer-checkExist","success",$ts,"info",json_encode(array("uid"=>$uid)));
        if($rUid==""){
            $conn->close();
            return false;
        }else{
            $conn->close();
            return true;
        }
    }
}
class app{
    //新增APP
    function create($did,$name){
        //记录时间戳
        $ts=time();
        //创建数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        if(!$this->checkExist($name)){
            //获取已使用的全局id数
            $sql=$conn->prepare("SELECT idUsed FROM `system` WHERE 1");
            $sql->execute();
            $sql->bind_result($idUsed);
            while($sql->fetch()){
                $rIdUsed=$idUsed;
            }
            $id=$rIdUsed+1;
            //更新全局id数
            $sql=$conn->prepare("UPDATE `system` SET `idUsed`=? WHERE 1");
            $sql->bind_param('i',$id);
            $sql->execute();
            //新增APP
            $sql=$conn->prepare("INSERT INTO apps (
                id,aid,name,sid,skey,ban,lastIP,addTime,lastTime,ownerId,users) VALUES(
                ?,NULL,?,?,?,?,?,?,?,?,?)");
            $users=json_encode(array());
            $ban="false";
            $lastIP="0.0.0.0";
            //创建sId与sKey
            $hash=new hash;
            $sIdKey=$hash->generateSIdKey($name);
            $sql->bind_param('isssssiiis',$id,$name,$sIdKey[0],$sIdKey[1],$ban,$lastIP,$ts,$ts,$did,$users);
            $sql->execute();
            //取回APPiD
            $aid=mysqli_insert_id($conn);
            loger($conn,"app-create","success",$ts,"info",json_encode(array("did"=>$did)));
            $conn->close();
            return array($aid,$sIdKey[0],$sIdKey[1]);
        }else{
            loger($conn,"app-create","failed",$ts,"info",json_encode(array("did"=>$did)));
            $conn->close();
            return false;
        }
    }
    //移除APP
    function remove($aid){
        //记录时间戳
        $ts=time();
        //创建数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        //判断是否存在
        if($this->checkExistAid($aid)){
            //删除APP
            $sql=$conn->prepare("DELETE FROM `apps` WHERE `aid`=?");
            $sql->bind_param('i',$aid);
            $sql->execute();
            loger($conn,"app-remove","success",$ts,"info",json_encode(array("did"=>$rDid)));
            $conn->close();
            return true;
        }else{
            loger($conn,"app-remove","failed",$ts,"info",json_encode(array("did"=>$rDid)));
            $conn->close();
            return false;
        }
    }
    function giveToken($sId,$sKey){
        //记录时间戳
        $ts=time();
        //创建数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        if($this->checkRight($sId,$sKey)){
            //获取APP的全局ID
            $id=$this->getIdSId($sId);
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
                $token=$hash->generateToken($sId+$sKey);
                $sql=$conn->prepare("INSERT INTO `tokens`(`tid`, `type`, `createTime`, `renewal`, `id`, `value`) 
                                    VALUES (NULL,'app',?,NULL,?,?)");
                $sql->bind_param('iis',$ts,$id,$token);
                $sql->execute();
                loger($conn,"app-giveToken","success",$ts,"info",json_encode(array("sid"=>$sId)));
                $conn->close();
                return $token;
            }else{
                loger($conn,"app-giveToken","failed",$ts,"info",json_encode(array("sid"=>$sId)));
                $conn->close();
                return $rValue;
            }
        }else{
            loger($conn,"app-giveToken","failed",$ts,"info",json_encode(array("email"=>$email)));
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
            loger($conn,"app-destroyToken","success",$ts,"info",json_encode(array("token"=>$token)));
            $conn->close();
            return true;
        }else{
            loger($conn,"app-destroyToken","failed",$ts,"info",json_encode(array("token"=>$token)));
            $conn->close();
            return false;
        }
    }
    //检测Token是否有效
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
        if((($ts-$rCreateTime)>604800 && $rRenewal=="") || (($ts-$rCreateTime)>1209600 && $rRenewal=="true") || ($rDestroy=="true")){
            loger($conn,"app-checkToken","success",$ts,"info",json_encode(array("token"=>$token)));
            $conn->close();
            return false;
        }else{
            loger($conn,"app-checkToken","success",$ts,"info",json_encode(array("token"=>$token)));
            $conn->close();
            return true;
        }
    }
    //移除开发者所有APP
    function removeForDeve($did){
        //记录时间戳
        $ts=time();
        //创建数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        //删除APP
        $sql=$conn->prepare("DELETE FROM `apps` WHERE `did`=?");
        $sql->bind_param('i',$did);
        $sql->execute();
        loger($conn,"app-removeForDeve","success",$ts,"info",json_encode(array("did"=>$did)));
        $conn->close();
        return true;
    }
    //使用Token获取Aid
    function getAidToken($token){
        //记录时间戳
        $ts=time();
        //创建数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        //检测Token是否已经存在
        if($this->checkToken($token)){
            $sql=$conn->prepare("SELECT `id` FROM `tokens` WHERE `value`=?");
            $sql->bind_param('s',$token);
            $sql->execute();
            $sql->bind_result($id);
            while($sql->fetch()){
                $rId=$id;
            }
            $aid=$this->getAidId($rId);
            return $aid;
        }else{
            return false;
        }
    }
    //使用全局ID获取APP的Aid
    function getAidId($id){
        //记录时间戳
        $ts=time();
        //创建数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        $sql=$conn->prepare("SELECT aid FROM `apps` WHERE `id`=?");
        $sql->bind_param('i',$id);
        $sql->execute();
        $sql->bind_result($aid);
        while($sql->fetch()){
            $rAid=$aid;
        }
        loger($conn,"app-getIdAid","success",$ts,"info",json_encode(array("id"=>$id)));
        $conn->close();
        return $rAid;
    }
    //使用sId获取APP的全局ID
    function getIdSId($sId){
        //记录时间戳
        $ts=time();
        //创建数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        $sql=$conn->prepare("SELECT id FROM `apps` WHERE `sid`=?");
        $sql->bind_param('i',$sId);
        $sql->execute();
        $sql->bind_result($id);
        while($sql->fetch()){
            $rId=$id;
        }
        loger($conn,"app-getIdSId","success",$ts,"info",json_encode(array("sid"=>$sId)));
        $conn->close();
        return $rId;
    }
    //使用APP ID检查APP是否存在
    function checkExistAid($aid){
        //记录时间戳
        $ts=time();
        //创建数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        $sql=$conn->prepare("SELECT name FROM `apps` WHERE `aid`=?");
        $sql->bind_param('i',$aid);
        $sql->execute();
        $sql->bind_result($name);
        while($sql->fetch()){
            $rName=$name;
        }
        loger($conn,"app-checkExistAid","success",$ts,"info",json_encode(array("aid"=>$aid)));
        if($rName==""){
            $conn->close();
            return false;
        }else{
            $conn->close();
            return true;
        }
    }
    //检查APP是否已经存在或者重名
    function checkExist($name){
        //记录时间戳
        $ts=time();
        //创建数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        $sql=$conn->prepare("SELECT aid FROM `apps` WHERE `name`=?");
        $sql->bind_param('s',$name);
        $sql->execute();
        $sql->bind_result($aid);
        while($sql->fetch()){
            $rAid=$aid;
        }
        loger($conn,"app-checkExist","success",$ts,"info",json_encode(array("name"=>$name)));
        if($rAid==""){
            $conn->close();
            return false;
        }else{
            $conn->close();
            return true;
        }
    }
    //检查APP信息是否正确
    function checkRight($sId,$sKey){
        //记录时间戳
        $ts=time();
        //创建数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        $sql=$conn->prepare("SELECT skey FROM `apps` WHERE `sid`=?");
        $sql->bind_param('s',$sId);
        $sql->execute();
        $sql->bind_result($sKey);
        while($sql->fetch()){
            $rSKey=$sKey;
        }
        loger($conn,"app-checkRight","success",$ts,"info",json_encode(array("sid"=>$sId)));
        if($sKey==$rSKey){
            $conn->close();
            return true;
        }else{
            $conn->close();
            return false;
        }
    }
    //检查应用权限级别
    function checkLevel($aid){
        //记录时间戳
        $ts=time();
        //创建数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        $sql=$conn->prepare("SELECT ownerId FROM `apps` WHERE `aid`=?");
        $sql->bind_param('i',$aid);
        $sql->execute();
        $sql->bind_result($ownerId);
        while($sql->fetch()){
            $rOwnerId=$ownerId;
        }
        $deve=new developer;
        loger($conn,"app-checkLevel","success",$ts,"info",json_encode(array("aid"=>$aid)));
        $conn->close();
        return $deve->checkLevel($rOwnerId);
    }
}