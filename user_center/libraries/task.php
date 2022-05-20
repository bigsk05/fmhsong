<?php 
/*
Copyright GHINK Network Studio
Author: Bigsk(https://www.xiaxinzhe.cn)
*/
if(!defined('IN_SYS')){//Defined entreance security.
    header('HTTP/1.1 403 Forbidden');
    exit(file_get_contents(setting::root."template/403.html"));
}
//全局时效任务管理
class task{
	//创建任务
	function create($type,$email="",$name="",$pass="",$area="",$ip="",$token=""){
		//记录时间戳
        $ts=time();
		//数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
		$sql=$conn->prepare("INSERT INTO tasks 
		(tid,type,createTime,invalidTime,aid,status,content) VALUES
		(NULL,?,?,?,?,\"open\",?)");
		switch($type){
			//创造注册邮件验证任务
			case "reg":
				$validTime=$ts+10800;//三个小时
				$aid=0;
				//生成验证码
				$code=rand(100000,999999);
				//编码json，信息无关性储存
				$content=json_encode(array($code,$email,$name,$pass,$area,$ip));
				$sql->bind_param("siiis",$type,$ts,$validTime,$aid,$content);//10800s=3h
				$sql->execute();
				//取回任务id
				$tid=mysqli_insert_id($conn);
				loger($conn,"task-create","success",$ts,"info",json_encode(array("email"=>$email)));
				$conn->close();
				return array($tid,$code);
			case "oauthCreate":
				$validTime=$ts+3600;//一个小时
				$app=new app;
				if($app->checkToken($token)){
					$aid=$app->getAidToken($token);
					$content="oauth";
					$sql->bind_param("siiis",$type,$ts,$validTime,$aid,$content);//10800s=3h
					$sql->execute();
					//取回任务id
					$tid=mysqli_insert_id($conn);
					loger($conn,"task-create","success",$ts,"info",json_encode(array("token"=>$token)));
					$conn->close();
					return $tid;
				}else{
					loger($conn,"task-create","failed",$ts,"info",json_encode(array("token"=>$token)));
					$conn->close();
					return false;
				}
		}
	}
	//创建实名认证任务
	function createAuth($veriToken,$uid){
		//记录时间戳
        $ts=time();
		//数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
		$sql=$conn->prepare("INSERT INTO tasks 
		(tid,type,createTime,invalidTime,aid,status,content) VALUES
		(NULL,?,?,?,?,\"open\",?)");
		$validTime=$ts+7200;//两个小时
		$app=new app;
		$aid=0;
		$type="auth";
		$content=json_encode(array($uid,$veriToken));
		$sql->bind_param("siiis",$type,$ts,$validTime,$aid,$content);//10800s=3h
		$sql->execute();
		//取回任务id
		$tid=mysqli_insert_id($conn);
		loger($conn,"task-createAuth","success",$ts,"info",json_encode(array("uid"=>$uid)));
		$conn->close();
		return $tid;
	}
	//从tid获取任务发起的应用aid
	function getAidTid($tid){
		//记录时间戳
        $ts=time();
		//数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        $sql=$conn->prepare("SELECT aid FROM `tasks` WHERE `tid`=?");
        $sql->bind_param('i',$tid);
        $sql->execute();
        $sql->bind_result($aid);
        while($sql->fetch()){
            $rAid=$aid;
        }
        if($rAid!=""){
    		loger($conn,"task-getAidTid","success",$ts,"info",json_encode(array("tid"=>$tid)));
            $conn->close();
            return $aid;
        }else{
    		loger($conn,"task-getAidTid","failed",$ts,"info",json_encode(array("tid"=>$tid)));
            $conn->close();
            return false;
        }
	}
	//取回Auth任务详情
	function getBackAuth($tid){
		//记录时间戳
        $ts=time();
		//数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        $sql=$conn->prepare("SELECT status,content FROM `tasks` WHERE `tid`=?");
        $sql->bind_param('i',$tid);
        $sql->execute();
        $sql->bind_result($status,$content);
        while($sql->fetch()){
            $rStatus=$status;
            $rContent=$content;
        }
		if($rStatus!=""){
			//核销Task
			$sql=$conn->prepare("UPDATE `tasks` SET `status`=\"close\" WHERE `tid`=?");
			$sql->bind_param("i",$tid);
			$sql->execute();
    		if($rStatus!="open"){
        		loger($conn,"task-getBackAuth","failed",$ts,"info",json_encode(array("tid"=>$tid)));
        		$conn->close();
    			return false;
    		}else{
        		loger($conn,"task-getBackAuth","success",$ts,"info",json_encode(array("tid"=>$tid)));
        		$conn->close();
    		    $result=json_decode($rContent,true);
                return $result;
    		}
		}else{
    		loger($conn,"task-getBackAuth","failed",$ts,"info",json_encode(array("tid"=>$tid)));
    		$conn->close();
			return false;
		}
	}
	//取回oauth结果（用户Token）
	function getbackOauth($tid){
		if($this->checkOauth($tid)){
			//记录时间戳
			$ts=time();
			//数据库连接
			$conn=new mysqli(database::addr,database::user,database::pass,database::name);
			//取回Token
			$sql=$conn->prepare("SELECT content FROM `tasks` WHERE `tid`=?");
			$sql->bind_param('i',$tid);
			$sql->execute();
			$sql->bind_result($content);
			while($sql->fetch()){
				$rContent=$content;
			}
			//核销Task
			$sql=$conn->prepare("UPDATE `tasks` SET `status`=\"close\" WHERE `tid`=?");
			$sql->bind_param("i",$tid);
			$sql->execute();
			return $rContent;
		}else{
			return false;
		}
	}
	//oauth登陆任务记录
	function recordOauth($tid,$token){
		if($this->checkOauth($tid)){
			//记录时间戳
			$ts=time();
			//数据库连接
			$conn=new mysqli(database::addr,database::user,database::pass,database::name);
			//记录Token
			$sql=$conn->prepare("UPDATE `tasks` SET `content`=? WHERE `tid`=?");
			$sql->bind_param("si",$token,$tid);
			$sql->execute();
			$conn->close();
			return true;
		}else{
			return false;
		}
	}
	//检测oauth登录任务是否有效
	function checkOauth($tid){
		//记录时间戳
        $ts=time();
		//数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        $sql=$conn->prepare("SELECT status FROM `tasks` WHERE `tid`=?");
        $sql->bind_param('i',$tid);
        $sql->execute();
        $sql->bind_result($status);
        while($sql->fetch()){
            $rStatus=$status;
        }
		loger($conn,"task-checkOauth","success",$ts,"info",json_encode(array("tid"=>$tid)));
		$conn->close();
		if($rStatus!="open"){
			return false;
		}else{
			return true;
		}
	}
}