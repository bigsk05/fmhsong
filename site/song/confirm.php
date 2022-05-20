<?php 
include("../center/entrance.php");
include("../key.php");
$sdk=new ghinkPassport($global_id,$global_key);
$cookie=file_get_contents("cookie.conf");

function songBalace($limit,$used,$last,$refresh){
    if((time()-strtotime($last))>=604800){
        return true;
    }else{
        if($refresh==6){
            $refresh=date("Y-m-d",strtotime(getDay($refresh))-604800);
            if(strtotime($refresh)>=strtotime($last) && $limit!=0){
                return "true";
            }else{
                if(($limit-$used)>0){
                    return "true";
                }else{
                    return "false";
                }
            }
        }else{
            $refresh=getDay($refresh);
            if(strtotime($refresh)<=strtotime($last) && $limit!=0){
                return "true";
            }else{
                if(($limit-$used)>0){
                    return "true";
                }else{
                    return "false";
                }
            }
        }
    }
}
function getDay($week){
    $ts=time();
    $today=date("w",$ts);
    if($today==0){ //礼拜天忽略当天禁止点播当天（审核日与节目日重合）
        $result=$ts+(84600*abs($today-$week));
        return date("Y-m-d",$result);
    }else if($today==6){ //礼拜六计算下个礼拜（当天放假）
        $result=$ts+(84600*($week+1));
        return date("Y-m-d",$result);
    }else if($today>=$week){
        return false;
    }else{
        $result=$ts+(84600*abs($today-$week));
        return date("Y-m-d",$result);
    }
}
//数据库连接
$conn=new mysqli(database::addr,database::user,database::pass,database::name);
$sql=$conn->prepare("SELECT `switcher`,`ignoreTime`,`timeInterval`,`openWeek`,`refreshDay` FROM `system` WHERE `systemKey`=1");
$sql->execute();
$sql->bind_result($switcher,$ignoreTime,$timeInterval,$openWeek,$refreshDay);
while($sql->fetch()){
    $rSwitcher=$switcher;
    $rIgnoreTime=$ignoreTime;
    $rTimeInterval=$timeInterval;
    $rOpenWeek=$openWeek;
    $rRefreshDay=$refreshDay;
}
$sql=$conn->prepare("SELECT `status`,`songNumsLimit`,`songTimeUsed`,`songLastTime` FROM `users` WHERE `uid`=?");
$sql->bind_param("i",$_COOKIE["uid"]);
$sql->execute();
$sql->bind_result($status,$songNumsLimit,$songTimeUsed,$songLastTime);
while($sql->fetch()){
    $rStatus=$status;
    $rSongTimeLimit=$songNumsLimit;
    $rSongTimeUsed=$songTimeUsed;
    $rSongLastTime=$songLastTime;
}
$balace=songBalace($rSongTimeLimit,$rSongTimeUsed,$rSongLastTime,$rRefreshDay);
if($rStatus=="passed"){
    $auth="true";
}else{
    $auth="false";
}
$conn->close();
$option="";
$openWeek=json_decode($rOpenWeek,true);
$arr=array("天","一","二","三","四","五","六");
foreach ($openWeek as $value){
    $week=getDay($value);
    if($week!=false){
        $option=$option.'<option value=\''.$week.'\'>'.$week." 星期".$arr[intval(date("w",strtotime($week)))].'</option>';
    }
}
echo str_replace("{{option}}",$option,str_replace("{{balace}}",$balace,str_replace("{{timeQuatum}}",$rTimeInterval,str_replace("{{igTime}}",$rIgnoreTime,str_replace("{{switcher}}",$rSwitcher,str_replace("{{auth}}",$auth,str_replace("{{uid}}",$_GET["uid"],str_replace("{{name}}",$_GET["name"],str_replace("{{cookie}}",$cookie,str_replace("{{id}}",$_GET["id"],str_replace("{{pid}}",$_GET["pid"],file_get_contents("template/confirm.html"))))))))))));
?>