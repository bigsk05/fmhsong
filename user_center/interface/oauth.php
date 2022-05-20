<?php 
/*
Copyright GHINK Network Studio
Author: Bigsk(https://www.xiaxinzhe.cn)
*/
include("../libraries/entrance.php");
//检测数据库
$conn=mysqli_connect(database::addr,database::user,database::pass,database::name) or die('Database Error! Please contact with administrator!');
mysqli_close($conn);
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
switch($_GET["type"]){
    //退出登陆模块
    case "exit":
        setcookie("ghinkToken","",-1,"/");
        header("location: oauth.php?type=log");
        exit();
    //实名认证模块
    case "auth":
        $user=new user;
        if(!$user->checkToken($_COOKIE["ghinkToken"])){
            $token="";
            $url="Need Login";
            echo str_replace("{{url}}",$url,str_replace("{{token}}",$token,file_get_contents(setting::root."template/authentication/authentication.html")));
        }else{
            $token=$_COOKIE["ghinkToken"];
            //检查是否为中国大陆用户
            $auth=new realNameAuth;
            $id=$user->getIdToken($token);
            $uid=$user->getUidId($id);
            $url=$auth->create($uid);
            if($user->getAreaUid($uid)=="global" || $auth->check($uid)){
                header("location: oauth.php?type=panel");
            }else{
                echo str_replace("{{url}}",$url,str_replace("{{token}}",$token,file_get_contents(setting::root."template/authentication/authentication.html")));
            }
        }
        exit();
    //实名认证回调模块
    case "authBack":
        if($_GET['tid']==""){
            exit(file_get_contents(setting::root."template/403.html"));
        }else{
            $realNameAuth=new realNameAuth;
            if($realNameAuth->getResult($_GET['tid'])){
                echo file_get_contents(setting::root."template/authentication/success.html");
            }else{
                echo file_get_contents(setting::root."template/authentication/failed.html");
            }
        }
        exit();
    //实名失败模块
    case "authFailed":
        echo file_get_contents(setting::root."template/authentication/failed.html");
        exit();
    //找回密码模块
    case "find":
        echo "暂未开放 Not Open";
        exit();
    //面板模块
    case "panel":
        $user=new user;
        if(!$user->checkToken($_COOKIE["ghinkToken"])){
            header("location: oauth.php?type=log");
        }else{
            echo "暂未开放 Not Open<br/>";
            echo '<a href="oauth.php?type=auth">点我进行实名认证</a><br>';
            echo '您的UID为：'.$user->getUidId($user->getIdToken($_COOKIE["ghinkToken"])).'<br>';
            echo '您的昵称为：'.$user->getNameUid($user->getUidId($user->getIdToken($_COOKIE["ghinkToken"]))).'<br>';
            echo '<a href="oauth.php?type=exit">退出登录</a><br>';
        }
        exit();
    //OAUTH模块核验
    case "oauthCheck":
        //判断来路信息
        if($domain==setting::domain){
            if($_POST['tid']!=""){
                $task=new task;
                if($task->checkOauth($_POST['tid'])){
                    if($_POST["email"]=="" || $_POST["pass"]==""){
                        //判断并输出信息
                        if($area==""){
                            echo json_encode(array("","Log in failed! "));
                        }else{
                            echo json_encode(array("","登陆失败！"));
                        }
                    }else{
                        $user=new user;
                        $task=new task;
                        $aid=$task->getAidTid($_POST['tid']);
                        $result=$user->login($_POST["email"],$_POST["pass"],$ip,$aid);
                        if($result==false){
                            //判断并输出信息
                            if($area==""){
                                echo json_encode(array("","Log in failed! "));
                            }else{
                                echo json_encode(array("","登陆失败！"));
                            }
                        }else{
                            //设置cookie
                            setcookie("ghinkToken",$result,$ts+604800,"/");
                            $task->recordOauth($_POST['tid'],$result);
                            //判断并输出信息
                            if($area==""){
                                echo json_encode(array($result,"Log in success! "));
                            }else{
                                echo json_encode(array($result,"登陆成功！"));
                            }
                        }
                    }
                }
            }else{
                //非法访问403
                exit(file_get_contents(setting::root."template/403.html"));
            }
        }else{
            //非法访问403
            exit(file_get_contents(setting::root."template/403.html"));
        }
        exit();
    //OAUTH模块
    case "oauth":
        if($_GET['tid']!="" && $_GET['location']!=""){
            //判断并输出模板
            if($area==""){
                echo str_replace("{{LOCATION}}",$_GET['location'],str_replace("{{TID}}",$_GET['tid'],file_get_contents(setting::root."template/oauth/en_US.html")));
            }else{
                echo str_replace("{{LOCATION}}",$_GET['location'],str_replace("{{TID}}",$_GET['tid'],file_get_contents(setting::root."template/oauth/zh_CN.html")));
            }
        }else{
            exit(file_get_contents(setting::root."template/403.html"));
        }
        exit();
    //登录模块
    case "log":
        $user=new user;
        if($_COOKIE['ghinkToken']!="" && $user->checkToken($_COOKIE['ghinkToken'])){
            header("location: oauth.php?type=panel");
            exit();
        }
        //判断并输出模板
        if($area==""){
            echo file_get_contents(setting::root."template/log/en_US.html");
        }else{
            echo file_get_contents(setting::root."template/log/zh_CN.html");
        }
        exit();
    //登录核验模块
    case "logCheck":
        if($domain==setting::domain){
            if($_POST["email"]=="" || $_POST["pass"]==""){
                //判断并输出信息
                if($area==""){
                    echo json_encode(array("","Log in failed! "));
                }else{
                    echo json_encode(array("","登陆失败！"));
                }
            }else{
                $user=new user;
                $result=$user->login($_POST["email"],$_POST["pass"],$ip);
                if($result==false){
                    //判断并输出信息
                    if($area==""){
                        echo json_encode(array("","Log in failed! "));
                    }else{
                        echo json_encode(array("","登陆失败！"));
                    }
                }else{
                    //设置cookie
                    setcookie("ghinkToken",$result,$ts+604800,"/");
                    //判断并输出信息
                    if($area==""){
                        echo json_encode(array($result,"Log in success! "));
                    }else{
                        echo json_encode(array($result,"登陆成功！"));
                    }
                }
            }
        }else{
            //非法访问403
            exit(file_get_contents(setting::root."template/403.html"));
        }
        exit();
    //注册模块
    case "reg":
        $user=new user;
        //判断并输出模板
        if($area==""){
            echo file_get_contents(setting::root."template/reg/en_US.html");
        }else{
            echo file_get_contents(setting::root."template/reg/zh_CN.html");
        }
        exit();
    //注册邮件发送模块
    case "regMail":
        if($domain==setting::domain){
            if($area==""){
                //非空判断
                if($_POST["email"]=="" || $_POST["pass"]=="" || $_POST["name"]==""){
                    exit("Auth mail send failed!");
                }else{
                    //处理邮件请求
                    $user=new user;
                    $result=$user->reg($_POST["name"],$_POST["pass"],$_POST["email"],$ip);
                    if($result==false){
                        exit("Auth mail send failed! Maybe the user has already exist!");
                    }else{
                        echo "Auth mail has sent to your email! Please Check.";
                    }
                }
            }else{
                //非空判断
                if($_POST["email"]=="" || $_POST["pass"]=="" || $_POST["name"]==""){
                    exit("验证邮件发送失败！");
                }else{
                    //处理邮件请求
                    $user=new user;
                    $result=$user->reg($_POST["name"],$_POST["pass"],$_POST["email"],$ip);
                    if($result==false){
                        exit("验证邮件发送失败！可能是用户已经存在！");
                    }else{
                        echo "验证邮件发送成功！请查收。";
                    }
                }
            }
        }else{
            //非法访问403
            exit(file_get_contents(setting::root."template/403.html"));
        }
        exit();
    //注册邮件验证模块
    case "regMailCheck":
        //非空判断
        if($_GET["email"]=="" || $_GET["tid"]=="" || $_GET["code"]==""){
            exit(file_get_contents(setting::root."template/403.html"));
        }else{
            //处理核验请求
            $mail=new mail;
            $result=$mail->checkReg($_GET["email"],$_GET["tid"],$_GET["code"]);
            if($result){
                //判断并输出模板
                if($area==""){
                    echo file_get_contents(setting::root."template/regMailCheck/success_en_US.html");
                }else{
                    echo file_get_contents(setting::root."template/regMailCheck/success_zh_CN.html");
                }
            }else{
                //判断并输出模板
                if($area==""){
                    echo file_get_contents(setting::root."template/regMailCheck/failed_en_US.html");
                }else{
                    echo file_get_contents(setting::root."template/regMailCheck/failed_zh_CN.html");
                }
            }
        }
        exit();
    //协议模块
    case "agreement":
        //输出协议页
        echo file_get_contents(setting::root."template/agreement.html");
        exit();
    //非法访问
    default:
        //非法访问403
        exit(file_get_contents(setting::root."template/403.html"));
}