<?php 
include("entrance.php");
include("../key.php");
$sdk=new ghinkPassport($global_id,$global_key);
$ts=time();
if($_COOKIE["token"]==""){
    switch(@$_GET["type"]){
        case "callBack":
            if(@$_GET["tid"]!=""){
                $token=$sdk->checkLogin($_GET["tid"]);
                $uid=$sdk->getUid($token);
                $nicknameRead=$sdk->getName($uid);
                $authInfo=json_decode($sdk->readAuth($uid));
        		//数据库连接
                $conn=new mysqli(database::addr,database::user,database::pass,database::name);
                $sql=$conn->prepare("SELECT `ban`,`type` FROM `users` WHERE `uid`=?");
                $sql->bind_param('i',$uid);
                $sql->execute();
                $sql->bind_result($ban,$type);
                while($sql->fetch()){
                    $rBan=$ban;
                    $rType=$type;
                }
                if($rType==""){
                    $name=$authInfo->idcard_confirm->name;
                    $number=$authInfo->idcard_confirm->idcard_number;
                    $authList=array();
                    for($i=2019;$i<=2021;$i++){
                        $authList=array_merge($authList,json_decode(file_get_contents("../song/information/".strval($i).".json"),true));
                    }
                    $authStatus=null;
                    foreach ($authList as $value){
                        if(md5($number)==$value && $number!=null){
                            $authStatus="passed";
                        }
                    }
                    $sql=$conn->prepare("SELECT `nickname` FROM `users` WHERE `name`=?");
                    $sql->bind_param("s",$name);
                    $sql->execute();
                    $sql->bind_result($nickname);
                    while($sql->fetch()){
                        $rNickname=$nickname;
                    }
                    if($rNickname==""){
                        $type="user";
                        $sql=$conn->prepare("INSERT INTO `users`(`uid`, `type`, `nickname`, `name`, `status`) VALUES (?,?,?,?,?)");
                        $sql->bind_param('issss',$uid,$type,$nicknameRead,$name,$authStatus);
                        $sql->execute();
                        setcookie("token",$token,$ts+604800,"/");
                        setcookie("uid",$uid,$ts+604800,"/");
                        setcookie("name",$nicknameRead,$ts+604800,"/");
                    }else{
                        exit("<script>alert('对不起！您已经存在一个账号！请使用昵称为".$rNickname."的账号登录！');</script>");
                    }
                }else if($rBan=="true"){
                    exit("<script>alert('对不起！您已被封禁！请联系广播站技术部负责人，谢谢！');</script>");
                }else{
                    $name=$authInfo->idcard_confirm->name;
                    $number=$authInfo->idcard_confirm->idcard_number;
                    $authList=array();
                    for($i=2019;$i<=2021;$i++){
                        $authList=array_merge($authList,json_decode(file_get_contents("../song/information/".strval($i).".json"),true));
                    }
                    $authStatus=null;
                    foreach ($authList as $value){
                        if(md5($number)==$value && $number!=null){
                            $authStatus="passed";
                        }
                    }
                    $sql=$conn->prepare("UPDATE `users` SET `name`=?,`nickname`=?,`status`=? WHERE `uid`=?");
                    $sql->bind_param('sssi',$name,$nicknameRead,$authStatus,$uid);
                    $sql->execute();
                    setcookie("token",$token,$ts+604800,"/");
                    setcookie("uid",$uid,$ts+604800,"/");
                    setcookie("name",$nicknameRead,$ts+604800,"/");
                }
                $conn->close();
                header("location:".$_COOKIE["refer"]);
            }else{
                echo "登录失败！";
            }
            exit();
        default:
            setcookie("refer",$_GET["location"],$ts+604800,"/");
            $tid=$sdk->applyLogin();
            $location=urlencode("https://radio.fmhs.club/center/login.php?type=callBack&tid=".$tid);
            header("location:".$sdk->loginAddr($tid,$location));
    }
}else{
    header("location:".$_GET["location"]);
}