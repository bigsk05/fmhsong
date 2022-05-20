<?php 
/*
Copyright GHINK Network Studio
Author: Bigsk(https://www.xiaxinzhe.cn)
*/
if(!defined('IN_SYS')){//Defined entreance security.
    header('HTTP/1.1 403 Forbidden');
    exit(file_get_contents(setting::root."template/403.html"));
}
//日志记录
function loger($conn,$name,$status,$ts,$type,$info){
    $sql=$conn->prepare("INSERT INTO `logs` VALUES (NULL,?,?,?,0,?,?)");
    $sql->bind_param('ssiss',$name,$status,$ts,$type,$info);
    $sql->execute();
}