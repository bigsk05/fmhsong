<?php 
include("../center/entrance.php");
include("../key.php");
$sdk=new ghinkPassport($global_id,$global_key);
function mts(){
    $t=explode(' ', microtime());
    return (floatval($t[0])+floatval($t[1]));
}
$mts=mts();
$ts=round($mts);
function songBalace($limit,$used,$last,$refresh){
    if((time()-strtotime($last))>=604800){
        return true;
    }else{
        if($refresh==6){
            $refresh=date("Y-m-d",strtotime(getDay($refresh))-604800);
            if(strtotime($refresh)>=strtotime($last) && $limit!=0){
                return true;
            }else{
                if(($limit-$used)>0){
                    return true;
                }else{
                    return false;
                }
            }
        }else{
            $refresh=getDay($refresh);
            if(strtotime($refresh)<=strtotime($last) && $limit!=0){
                return true;
            }else{
                if(($limit-$used)>0){
                    return true;
                }else{
                    return false;
                }
            }
        }
    }
}
function getDay($var){
    $ts=time();
    $var2=date("w",$ts);
    if($var2=="6"){
        $var3=$ts+(84600*(abs(0-$var)+1));
        return date("Y-m-d",$var3);
    }else if($var2>=$var){
        return false;
    }else{
        $var3=$ts+(84600*abs($var2-$var));
        return date("Y-m-d",$var3);
    }
}
$domain=explode("/",preg_replace("/https:\/\/|http:\/\//","",$_SERVER["HTTP_REFERER"]))[0];

function duringTime($start,$end){
    $Day = date('Y-m-d ',time());
    $timeBegin = strtotime($Day.$start);
    $timeEnd = strtotime($Day.$end);
    $curr_time = time();
    if($curr_time >= $timeBegin && $curr_time <= $timeEnd){
        return true; 
    }else{
        return false;
    }
}
if($domain=="radio.fmhs.club" || true){
    if(@$_GET['pid']!="" && @$_GET['id']!="" && @$_GET['date']!=""){
        $token=false;
        if($_GET["id"]!="1"){
            $tokenClass=new token;
            $token=$tokenClass->getTokenApp($global_id,$global_key);
            if($token!=false){
                $data=array("token"=>$token,"pid"=>$_GET['pid'],"sid"=>$_GET['id']);
                $result=sendPost("https://apiv1.radio.fmhs.club/new/",$data);
                //var_dump($result);
            }
        }
        if($_COOKIE["token"]==""){
            exit(json_encode(array("status"=>"failed")));
        }else{
            if($sdk->checkToken($_COOKIE["token"])){
                $uid=$_COOKIE["uid"];
        		//数据库连接
                $conn=new mysqli(database::addr,database::user,database::pass,database::name);
                $sql=$conn->prepare("SELECT `status` FROM `users` WHERE `uid`=?");
                $sql->bind_param("i",$uid);
                $sql->execute();
                $sql->bind_result($status);
                while($sql->fetch()){
                    $rStatus=$status;
                }
                if($rStatus=="passed"){
                    $sql=$conn->prepare("SELECT `switcher`,`ignoreTime`,`timeInterval`,`refreshDay` FROM `system` WHERE `systemKey`=1");
                    $sql->execute();
                    $sql->bind_result($switcher,$ignoreTime,$timeInterval,$refreshDay);
                    while($sql->fetch()){
                        $rSwitcher=$switcher;
                        $rIgnoreTime=$ignoreTime;
                        $rTimeInterval=$timeInterval;
                        $rRefreshDay=$refreshDay;
                    }
                    $json=json_decode($rTimeInterval);
                    if($rIgnoreTime=="true"){
                        $igTime=true;
                    }else{
                        $igTime=false;
                    }
                    if($rSwitcher=="true"){
                        $switcher=true;
                    }else{
                        $switcher=false;
                    }
                    $timeSwitcher=false;
                    foreach ($json as $key => $value){
                        if($value[0]==date("w") && duringTime($value[1],$value[2])){
                            $timeSwitcher=true;
                        }
                    }
                    if($timeSwitcher || $igTime){
                        $sql=$conn->prepare("SELECT `songNumsLimit`,`songTimeUsed`,`songLastTime` FROM `users` WHERE `uid`=?");
                        $sql->bind_param("i",$uid);
                        $sql->execute();
                        $sql->bind_result($songNumsLimit,$songTimeUsed,$songLastTime);
                        while($sql->fetch()){
                            $rSongNumsLimit=$songNumsLimit;
                            $rSongTimeUsed=$songTimeUsed;
                            $rSongLastTime=$songLastTime;
                        }
                        if(songBalace($rSongNumsLimit,$rSongTimeUsed,$rSongLastTime,$rRefreshDay)){
                            if($switcher){
                                if($_GET["pid"]=="1"){
                                    $cacheId=intval($_GET["id"]);
                                }else if($_GET["pid"]=="2"){
                                    $data=array("token"=>$token,"pid"=>$_GET['pid'],"sid"=>$_GET['id']);
                                    $result=sendPost("https://apiv1.radio.fmhs.club/new/",$data);
                                    //var_dump($result);
                                    $result=json_decode($result);
                                    $cacheId=$result->result->id;
                                }                            
                                //获取黑名单
                                $sql=$conn->prepare("SELECT `id` FROM `blacklist` WHERE `id`=?");
                                $sql->bind_param("i",$cacheId);
                                $sql->execute();
                                $sql->bind_result($id);
                                while($sql->fetch()){
                                    $rId=$id;
                                }
                                if($rId==null){
                                    $songStatus=null;               
                                    //获取白名单
                                    $sql=$conn->prepare("SELECT `id` FROM `whitelist` WHERE `id`=?");
                                    $sql->bind_param("i",$cacheId);
                                    $sql->execute();
                                    $sql->bind_result($id);
                                    while($sql->fetch()){
                                        $rId=$id;
                                    }
                                    if($rId!=null){
                                        $songStatus="passed";
                                    }
                                    $pid=1;
                                    $mts=intval($mts*1000);
                                    $sql=$conn->prepare("INSERT INTO `record`(`date`, `pid`, `id`, `uid`, `time`, `status`) VALUES (?,?,?,?,?,?)");
                                    $sql->bind_param("sisiis",$_GET['date'],$pid,$cacheId,$uid,$mts,$songStatus);
                                    $sql->execute();
                                    if((time()-strtotime($rSongLastTime))>=604800){
                                        $used=1;
                                    }else{
                                        if($rRefreshDay==6){
                                            $refresh=date("Y-m-d",strtotime(getDay($rRefreshDay))-604800);
                                            if(strtotime($refresh)>=strtotime($rSongLastTime) && $rSongNumsLimit!=0){
                                                $used=1;
                                            }else{
                                                $used=$rSongTimeUsed+1;
                                            }
                                        }else{
                                            $refresh=getDay($rRefreshDay);
                                            if(strtotime($refresh)<=strtotime($rSongLastTime) && $rSongNumsLimit!=0){
                                                $used=1;
                                            }else{
                                                $used=$rSongTimeUsed+1;
                                            }
                                        }
                                    }
                                    $sql=$conn->prepare("UPDATE `users` SET `songTimeUsed`=?,`songLastTime`=? WHERE `uid`=?");
                                    $today=date("Y-m-d",$ts);
                                    $sql->bind_param("isi",$used,$today,$uid);
                                    $sql->execute();
                                    $conn->close();
                                    exit("点歌成功！");
                                }else{
                                    $conn->close();
                                    exit("您点播的歌曲为黑名单歌曲！");
                                }
                            }else{
                                $conn->close();
                                exit("点歌通道已经关闭！");
                            }
                        }else{
                            $conn->close();
                            exit("您的本周点歌余额不足！");
                        }
                    }else{
                        $conn->close();
                        exit("当前非点歌时间段！");
                    }
                }else{
                    $conn->close();
                    exit("请先实名！");
                }
            }else{
                exit("请先登录！");
            }
        }
    }else{
        exit("参数无效！");
    }
}else{
    exit("非法访问！");
}