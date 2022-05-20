<?php 
/*
Copyright GHINK Network Studio
Author: Bigsk(https://www.xiaxinzhe.cn)
*/
if(!defined('IN_SYS')){//Defined entreance security.
    header('HTTP/1.1 403 Forbidden');
    exit(file_get_contents(setting::root."template/403.html"));
}
//全局IP处理
class ip{
    //真实IP获取    
    function getReal(){
        if (getenv("HTTP_CLIENT_IP"))
            $ip=getenv("HTTP_CLIENT_IP");
        else if(getenv("HTTP_X_FORWARDED_FOR"))
            $ip=getenv("HTTP_X_FORWARDED_FOR");
        else if(getenv("REMOTE_ADDR"))
            $ip=getenv("REMOTE_ADDR");
        else 
            $ip=false;
        return $ip;
    }
    //获取最后登录/请求ip
    function getLast($id){
        //记录时间戳
        $ts=time();
        //创建数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        //获取数据库返回值
        $sql=$conn->prepare("SELECT logIP FROM `users` WHERE `id`=?");
        $sql->bind_param('s',$id);
        $sql->execute();
        $sql->bind_result($logIP);
        while($sql->fetch()){
            $rLogIP=$logIP;
        }
        //非用户
        if($rLogIP==""){
            $sql=$conn->prepare("SELECT lastIP FROM `apps` WHERE `id`=?");
            $sql->bind_param('s',$id);
            $sql->execute();
            $sql->bind_result($lastIP);
            while($sql->fetch()){
                $rLastIP=$lastIP;
            }
            if($rLastIP==""){
                loger($conn,"user-getLast","success",$ts,"info",$id);
                $conn->close();
                return false;
            }else{
                loger($conn,"user-getLast","success",$ts,"info",$id);
                $conn->close();
                return $rLastIP;
            }
        }else{
            loger($conn,"user-getLast","success",$ts,"info",$id);
            $conn->close();
            return $logIP;
        }
    }
}