<?php 
/*
Copyright GHINK Network Studio
Author: Bigsk(https://www.xiaxinzhe.cn)
*/
include("../libraries/entrance.php");
//捕获当前时间
$ts=time();
//真实IP获取
$ipadd=new ip;
$ip=$ipadd->getReal();
//解析信息
$stream_opts = [
    "ssl" => [
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ]
]; 
$ipInfo=json_decode(file_get_contents("https://api.ghink.net/ip/?ip=".$ip,false,stream_context_create($stream_opts)),true);
//获取IP信息分区
$area=$ipInfo["city"];
//获取来路信息
$domain=explode("/",preg_replace("/https:\/\/|http:\/\//","",$_SERVER["HTTP_REFERER"]))[0];
switch($_GET['type']){
    //申请APP Token
    case "appToken":
        if($_POST['sid']!="" && $_POST['skey']!=""){
            $app=new app;
            $result=$app->giveToken($_POST['sid'],$_POST['skey']);
            if($result==false){
                exit(json_encode(array("status"=>"failed")));
            }else{
                exit(json_encode(array("status"=>"success","token"=>$result)));
            }
        }else{
            exit(json_encode(array("status"=>"failed")));
        }
    //申请Auth任务
    case "authTask":
        if($_POST["token"]!=""){
            $app=new app;
            if($app->checkToken($_POST["token"])){
                $task=new task;
                $result=$task->create("oauthCreate","","","","","",$_POST["token"]);
                if($result==false){
                    exit(json_encode(array("status"=>"failed")));
                }else{
                    exit(json_encode(array("status"=>"success","tid"=>$result)));
                }
            }else{
                exit(json_encode(array("status"=>"failed")));
            }
        }else{
            exit(json_encode(array("status"=>"failed")));
        }
    //取回Auth任务结果（用户Token）
    case "authBack":
        if($_POST["token"]!="" && $_POST["tid"]!=""){
            $app=new app;
            if($app->checkToken($_POST["token"])){
                $task=new task;
                $result=$task->getbackOauth($_POST["tid"]);
                if($result==false){
                    exit(json_encode(array("status"=>"failed")));
                }else{
                    exit(json_encode(array("status"=>"success","token"=>$result)));
                }
            }else{
                exit(json_encode(array("status"=>"failed")));
            }
        }else{
            exit(json_encode(array("status"=>"failed")));
        }
        exit();
    //获取用户UID
    case "getUid":
        if($_POST["tokenUsr"]!="" && $_POST["tokenApp"]!=""){
            $app=new app;
            if($app->checkToken($_POST["tokenApp"])){
                $user=new user;
                if($user->checkToken($_POST["tokenUsr"])){
                    $result=$user->getIdToken($_POST["tokenUsr"]);
                    if($result!=false){
                        $result=$user->getUidId($result);
                        if($result!=false){
                            exit(json_encode(array("status"=>"success","uid"=>$result)));
                        }else{
                            exit(json_encode(array("status"=>"failed")));
                        }
                    }else{
                        exit(json_encode(array("status"=>"failed")));
                    }
                }else{
                    exit(json_encode(array("status"=>"failed")));
                }
            }else{
                exit(json_encode(array("status"=>"failed")));
            }
        }else{
            exit(json_encode(array("status"=>"failed")));
        }
        exit();
    //检查用户Token是否有效
    case "checkToken":
        if($_POST["tokenUsr"]!="" && $_POST["tokenApp"]!=""){
            $app=new app;
            if($app->checkToken($_POST["tokenApp"])){
                $user=new user;
                if($user->checkToken($_POST["tokenUsr"])){
                    exit(json_encode(array("status"=>"success")));
                }else{
                    exit(json_encode(array("status"=>"failed")));
                }
            }else{
                exit(json_encode(array("status"=>"failed")));
            }
        }else{
            exit(json_encode(array("status"=>"failed")));
        }
        exit();
    //取回用户昵称
    case "getName":
        if($_POST['token']!="" && $_POST["uid"]!=""){
            $app=new app;
            if($app->checkToken($_POST["token"])){
                $user=new user;
                $result=$user->getNameUid($_POST["uid"]);
                exit(json_encode(array("status"=>"success","name"=>$result)));
            }else{
                exit(json_encode(array("status"=>"failed")));
            }
        }else{
            exit(json_encode(array("status"=>"failed")));
        }
        exit();
    //检测是否已经实名
    case "checkAuth":
        if($_POST['token']!="" && $_POST["uid"]!=""){
            $app=new app;
            if($app->checkToken($_POST["token"])){
                $auth=new realNameAuth;
                if($auth->check($uid)){
                    exit(json_encode(array("status"=>"success","auth"=>true)));
                }else{
                    exit(json_encode(array("status"=>"success","auth"=>false)));
                }
            }else{
                exit(json_encode(array("status"=>"failed")));
            }
        }else{
            exit(json_encode(array("status"=>"failed")));
        }
        exit();
    //取回实名信息
    case "getAuthInfo":
        if($_POST['token']!="" && $_POST["uid"]!=""){
            $app=new app;
            if($app->checkToken($_POST["token"])){
                $aid=$app->getAidToken($_POST["token"]);
                $level=$app->checkLevel($aid);
                if($level=="official"){
                    $auth=new realNameAuth;
                    exit(json_encode(array("status"=>"success","result"=>$auth->readData($_POST["uid"]))));
                }else{
                    exit(json_encode(array("status"=>"failed")));
                }
            }else{
                exit(json_encode(array("status"=>"failed")));
            }
        }else{
            exit(json_encode(array("status"=>"failed")));
        }
        exit();
    case "checkAppLevel":
        if($_POST['token']!="" && $_POST["tokenCheck"]!=""){
            $app=new app;
            if($app->checkToken($_POST["token"])){
                $aid=$app->getAidToken($_POST["token"]);
                $level=$app->checkLevel($aid);
                if($level=="enterprise" || $level=="official"){
                    if($app->checkToken($_POST["tokenCheck"])){
                        $aidCheck=$app->getAidToken($_POST["tokenCheck"]);
                        $levelCheck=$app->checkLevel($aidCheck);
                        exit(json_encode(array("status"=>"success","result"=>$levelCheck)));
                    }else{
                        exit(json_encode(array("status"=>"failed")));
                    }
                }else{
                    exit(json_encode(array("status"=>"failed")));
                }
            }else{
                exit(json_encode(array("status"=>"failed")));
            }
        }else{
            exit(json_encode(array("status"=>"failed")));
        }
        exit();
    case "checkAppToken":
        if($_POST['token']!="" && $_POST["tokenCheck"]!=""){
            $app=new app;
            if($app->checkToken($_POST["token"])){
                $aid=$app->getAidToken($_POST["token"]);
                $level=$app->checkLevel($aid);
                if($level=="enterprise" || $level=="official"){
                    if($app->checkToken($_POST["tokenCheck"])){
                        exit(json_encode(array("status"=>"success","result"=>true)));
                    }else{
                        exit(json_encode(array("status"=>"success","result"=>false)));
                    }
                }else{
                    exit(json_encode(array("status"=>"failed")));
                }
            }else{
                exit(json_encode(array("status"=>"failed")));
            }
        }else{
            exit(json_encode(array("status"=>"failed")));
        }
        exit();
    default:
        exit(json_encode(array("status"=>"success")));
}