<?php 
/*
Copyright GHINK Network Studio
Author: Bigsk(https://www.xiaxinzhe.cn)
*/
if(!defined('IN_SYS')){//Defined entreance security.
    header('HTTP/1.1 403 Forbidden');
    exit(file_get_contents(setting::root."template/403.html"));
}
//全局数据库类
class database{
    //定义数据库信息
    const addr="127.0.0.1";
    const user="center";
    const pass="Ghink@2014";
    const name="center";
}