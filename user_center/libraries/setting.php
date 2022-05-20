<?php 
/*
Copyright GHINK Network Studio
Author: Bigsk(https://www.xiaxinzhe.cn)
*/
if(!defined('IN_SYS')){//Defined entreance security.
    header('HTTP/1.1 403 Forbidden');
    exit(file_get_contents(setting::root."template/403.html"));
}
//全局设置信息
class setting{
    const url="https://center.ghink.net";
    const root="/www/wwwroot/official/center/v1/";
    const domain="center.ghink.net";
    const baiduCloud=array("uAga4MeDGj9Cj6YNcQ1D8G3B","h0dGFWtHBlSKcTiPwjKm6x0oRlVkiRdV");
    const baiduAuthPlanId="12406";
    function initialization(){
        $db=new database;
        $db->reset();
        $dev=new developer;
        $did=$dev->create(1,"official");
        $app=new app;
        $app->create($did,"Ghink Application");
    }
}