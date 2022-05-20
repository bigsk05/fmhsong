<?php 
/*
Copyright GHINK Network Studio
Author: Bigsk(https://www.xiaxinzhe.cn)
*/
if(!defined('IN_SYS')){//Defined entreance security.
    header('HTTP/1.1 403 Forbidden');
    exit(file_get_contents(setting::root."template/403.html"));
}
//百度大脑认证接口
class baiduBrain{
    function __construct(){
        $this->getToken();
    }
    //请求TOKEN
    function getToken(){
        $jsonData=array("grant_type"=>"client_credentials",
                        "client_id"=>setting::baiduCloud[0],
                        "client_secret"=>setting::baiduCloud[1]);
        $this->token=json_decode(sendPost("https://aip.baidubce.com/oauth/2.0/token",$jsonData),true)["access_token"];
    }
    //生成验证TOKEN
    function getVeriToken(){
        $options=array(
          'http' => array(
            'method' => 'POST',
            'header' => 'Content-type:application/json',
            'content' => '{"plan_id":"'.setting::baiduAuthPlanId.'"}',
            'timeout' => 15*60
          )
        );
        $context=stream_context_create($options);
        $result=file_get_contents("https://aip.baidubce.com/rpc/2.0/brain/solution/faceprint/verifyToken/generate?access_token=".$this->token,false,$context);
        $result=json_decode($result,true);
        if($result["success"]){
            return $result["result"]["verify_token"];
        }else{
            return false;
        }
    }
    //取回验证结果
    function getBack($veriToken){
        $options=array(
          'http' => array(
            'method' => 'POST',
            'header' => 'Content-type:application/json',
            'content' => '{"verify_token":"'.$veriToken.'"}',
            'timeout' => 15*60
          )
        );
        $context=stream_context_create($options);
        $result=file_get_contents("https://aip.baidubce.com/rpc/2.0/brain/solution/faceprint/result/detail?access_token=".$this->token,false,$context);
        $result=json_decode($result,true);
        if($result["success"]){
            return $result["result"];
        }else{
            return false;
        }
    }
}
//实名认证接口
class realNameAuth{
    //创建实名认证任务
    function create($uid){
        $brain=new baiduBrain;
        $task=new task;
        $veriToken=$brain->getVeriToken();
        if($veriToken==false){
            return false;
        }else{
            $tid=$task->createAuth($veriToken,$uid);
            if($tid==false){
                return false;
            }else{
                $result=$this->makeUrl($veriToken,$tid);
                return $result;
            }
        }
    }
    //构造实名认证URL
    function makeUrl($veriToken,$tid){
        $url="https://brain.baidu.com/face/print/?token=".urlencode($veriToken)."&successUrl=".urlencode("https://center.ghink.net/v1/interface/oauth.php?type=authBack&tid=".$tid)."&failedUrl=https://center.ghink.net/v1/interface/oauth.php?type=authFailed";
        return $url;
    }
    //取回实名认证结果
    function getResult($tid){
		//记录时间戳
        $ts=time();
        $task=new task;
        $brain=new baiduBrain;
        $result=$task->getBackAuth($tid);
        if($result==false){
            return false;
        }else{
            $veriToken=$result[1];
            $uid=$result[0];
            $result=$brain->getBack($veriToken);
            $result=json_encode($result);
            //记录数据库
            $conn=new mysqli(database::addr,database::user,database::pass,database::name);
            loger($conn,"realNameAuth-getResult","success",$ts,"info",json_encode(array("tid"=>$tid)));
            //录入数据库
            $sql=$conn->prepare("UPDATE `userInfoCN` SET `idCradInfo`=? WHERE `uid`=?");
            $sql->bind_param('si',$result,$uid);
            $sql->execute();
            $json=json_decode($result);
            $name=$json->idcard_confirm->name;
            $brithday=date('Ymd',strtotime($json->idcard_ocr_result->birthday));
            $sex=$json->idcard_ocr_result->gender;
            $sql=$conn->prepare("UPDATE `userInfoCN` SET `name`=?,`brithday`=?,`sex`=? WHERE `uid`=?");
            $sql->bind_param('sssi',$name,$brithday,$sex,$uid);
            $sql->execute();
            $conn->close();
            return true;
        }
    }
    //检查某个用户是否已经实名
    function check($uid){
		//记录时间戳
        $ts=time();
        //数据库链接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        $sql=$conn->prepare("SELECT idCradInfo FROM `userInfoCN` WHERE `uid`=?");
        $sql->bind_param('i',$uid);
        $sql->execute();
        $sql->bind_result($idCardInfo);
        while($sql->fetch()){
            $rIdCradInfo=$idCardInfo;
        }
        if($rIdCradInfo!=""){
            $json=json_decode($rIdCradInfo,true);
            $expire=strtotime($json["idcard_ocr_result"]["expire_time"]);
            $issue=strtotime($json["idcard_ocr_result"]["issue_time"]);
            if($ts<$expire && $ts>$issue){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    //读取实名认证数据
    function readData($uid){
		//记录时间戳
        $ts=time();
        //数据库链接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        $sql=$conn->prepare("SELECT idCradInfo FROM `userInfoCN` WHERE `uid`=?");
        $sql->bind_param('i',$uid);
        $sql->execute();
        $sql->bind_result($idCardInfo);
        while($sql->fetch()){
            $rIdCradInfo=$idCardInfo;
        }
        if($rIdCradInfo!=""){
            return $rIdCradInfo;
        }else{
            return false;
        }
    }
}
//邮件验证
class mail{
  //登陆地区警告函数
  function sendWarn($email,$area,$lastArea){
    $user=new user;
    $name=$user->getName($email);
    $uArea=$user->getArea($email);
    if($uArea=="china"){
      $title="极科通行证异地登录告警";
      $body=str_replace("{{lastArea}}",$lastArea,file_get_contents(setting::root."template/logWarn/zh_CN.html"));
      $body=str_replace("{{area}}",$area,$body);
      $body=str_replace("{{name}}",$name,$body);
    }else{
      $title="Ghink Passport Different places Log Warn";
      $body=str_replace("{{lastArea}}",$lastArea,file_get_contents(setting::root."template/logWarn/en_US.html"));
      $body=str_replace("{{area}}",$area,$body);
      $body=str_replace("{{name}}",$name,$body);
    }
    $this->send($email,$title,$body);
  }
  //注册邮件核验函数
  function checkReg($email,$tid,$code){
    //获取时间戳
    $ts=time();
    //数据库链接
    $conn=new mysqli(database::addr,database::user,database::pass,database::name);
    //请求核验信息
    $sql=$conn->prepare("SELECT content FROM `tasks` WHERE `tid`=?");
    $sql->bind_param('s',$tid);
    $sql->execute();
    $sql->bind_result($content);
    while($sql->fetch()){
        $rContent=$content;
    }
    //判断是否存在
    if($rContent==""){
      loger($conn,"check-reg","success",$ts,"info",json_encode(array("email"=>$email)));
      $conn->close();
      return false;
    }
    //json解码
    $content=json_decode($rContent);
    //合法性判断
    if($content[0]==$code && $content[1]==$email){
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
      //核销处理记录
      $sql=$conn->prepare("UPDATE `tasks` SET `content`=\"done\" WHERE `tid`=?");
      $sql->bind_param('i',$tid);
      $sql->execute();
      //记录用户信息
      $sql=$conn->prepare(
        "INSERT INTO users(id,uid,name,pass,email,ban,area,regTime,logTime,regIP,logIP) 
         VALUES(?,NULL,?,?,?,'false',?,?,?,?,?)");
      $sql->bind_param('issssiiss',$id,$content[2],$content[3],$email,$content[4],$ts,$ts,$content[5],$content[5]);
      $sql->execute();
	  $uid=mysqli_insert_id($conn);
      if($content[4]=="china"){
        $sql=$conn->prepare(
          "INSERT INTO `userInfoCN`(`uid`) 
          VALUES (?)");
        $sql->bind_param('i',$uid);
        $sql->execute();
      }else{
        $sql=$conn->prepare(
          "INSERT INTO `userInfoGL`(`uid`) 
          VALUES (?)");
        $sql->bind_param('i',$uid);
        $sql->execute();
      }
      loger($conn,"check-reg","success",$ts,"info",json_encode(array("tid"=>$tid)));
      $conn->close();
      return true;
    }
    loger($conn,"check-reg","failed",$ts,"info",json_encode(array("tid"=>$tid)));
    $conn->close();
    return false;
  }
  //注册邮件发送函数
  function sendReg($email,$name,$pass,$area,$ip){
    //获取时间戳
    $ts=time();
    //数据库连接
    $conn=new mysqli(database::addr,database::user,database::pass,database::name);
    //检查是否已经存在
    $sql=$conn->prepare("SELECT content FROM `tasks` WHERE `content` REGEXP ?");
    $sql->bind_param('s',$email);
    $sql->execute();
    $sql->bind_result($content);
    while($sql->fetch()){
      $rContent=$content;
    }
    loger($conn,"mail-sendReg","success",$ts,"info",json_encode(array("email"=>$email)));
    if($rContent==""){
      //实例化对象
      $tsk=new task;
      $hash=new hash;
      $pass=$hash->generatePass($pass);
      $result=$tsk->create("reg",$email,$name,$pass,$area,$ip);
      //URL拼接
      $url=setting::url."/v1/interface/oauth.php?type=regMailCheck&tid=".$result[0]."&email=".$email."&code=".$result[1];
      //区域判断与邮件发送
      if($area=="china"){
        $title="极科通行证注册验证邮件";
        $body=str_replace("{{link}}",$url,file_get_contents(setting::root."template/regMail/zh_CN.html"));
      }else{
        $title="Reg Ghink Passport";
        $body=str_replace("{{link}}",$url,file_get_contents(setting::root."template/regMail/zh_CN.html"));
      }
      $this->send($email,$title,$body);
      $conn->close();
      return true;
    }else{
      $conn->close();
      return false;
    }
  }
  //邮件发送便利化函数
  function send($mailto, $mailsub, $mailbd){
    $conn=new mysqli(database::addr,database::user,database::pass,database::name);
    $smtpserver="smtp.exmail.qq.com";
    $smtpserverport=25;
    $smtpusermail="system@ghink.net";
    $smtpemailto=$mailto;
    $smtpuser="system@ghink.net";
    $smtppass="GkNet2014";
    $mailsubject=$mailsub;
    $mailsubject="=?UTF-8?B?".base64_encode($mailsubject)."?=";
    $mailbody=$mailbd;
    //$mailbody="=?UTF-8?B?".base64_encode($mailbody)."?=";
    $mailtype="HTML";
    $smtp=new smtp($smtpserver, $smtpserverport, true, $smtpuser, $smtppass);
    $smtp->debug=false;
    $smtp->sendmail($smtpemailto, $smtpusermail, $mailsubject, $mailbody, $mailtype);
    if($smtp->log_file==""){
      loger($conn,"mail-send","success",$ts,"info",json_encode(array("mailto"=>$mailto)));
    }else{
      loger($conn,"mail-send","failed",$ts,"error",json_encode(array("mailto"=>$mailto,"smtpLog"=>$smtp->log_file)));
    }
    $conn->close();
  }
}