<?php 
include("../center/entrance.php");
include("../key.php");
$sdk=new ghinkPassport($global_id,$global_key);
$cookie=file_get_contents("cookie.conf");

header("content-type: application/json");


function songBalace($limit,$used,$lastDate){
    $lastDay=date("w",strtotime($lastDate));
    $toDay=date("w");
    if($toDay==6){//星期六时
        if($lastDay<$toDay){
            $used=0;
        }
    }else{
        if($lastDay>$toDay){//星期日到星期五
            $used=0;
        }
    }
    if(($limit-$used)>0){
        return true;
    }else{
        return false;
    }
}
function getSat(){
    $ts=time();
    $today=date("w",$ts);
    if($today==6){
        return date("Y-m-d",$today);
    }else{
        $result=$ts-(84600*abs($today+1));
        return date("Y-m-d",$result);
    }
}
function sendGet($url, $get_data) {
    $result="?";
    foreach ($get_data as $key => $value){
        if($key==end($get_data)){
            $result=$result.$key."=".urlencode($value);
        }else{
            $result=$result.$key."=".urlencode($value)."&";
        }
    }
    $aContext = array(
        'ssl' => array(
            'verify_peer' => false,
        ),
    );
    $cxContext = stream_context_create($aContext);
    return file_get_contents($url.$result, false, $cxContext);
}

if(@$_GET["type"]!=""){
    switch($_GET["type"]){
        case "system":
            //数据库连接
            $conn=new mysqli(database::addr,database::user,database::pass,database::name);
            $sql=$conn->prepare("SELECT `notice`,`switcher`,`ignoreTime`,`timeInterval` FROM `system` WHERE `systemKey`=1");
            $sql->execute();
            $sql->bind_result($notice,$switcher,$ignoreTime,$timeInterval);
            while($sql->fetch()){
                $rNotice=$notice;
                $rSwitcher=$switcher;
                $rIgnoreTime=$ignoreTime;
                $rTimeInterval=$timeInterval;
            }
            if($rSwitcher=="true"){
                $rSwitcher=true;
            }else{
                $rSwitcher=false;
            }
            if($rIgnoreTime=="true"){
                $rIgnoreTime=true;
            }else{
                $rIgnoreTime=false;
            }
            $conn->close();
            exit(json_encode(array("notice"=>$rNotice,"switcher"=>$rSwitcher,"igTime"=>$rIgnoreTime,"timeInt"=>json_decode($rTimeInterval))));
            break;
        case "user":
            if($_COOKIE["token"]==""){
                exit(json_encode(array("status"=>false)));
            }else{
                if(!$sdk->checkToken($_COOKIE["token"])){
                    setcookie("token","",-1,"/");
                    setcookie("uid","",-1,"/");
                    setcookie("name","",-1,"/");
                    header("Refresh:0");
                    $conn->close();
                    exit(json_encode(array("status"=>false)));
                }else{
                    //刷新账号信息
                    $uid=$sdk->getUid($_COOKIE["token"]);
                    $nicknameRead=$sdk->getName($uid);
                    $authInfo=json_decode($sdk->readAuth($uid));
                    $name=$authInfo->idcard_confirm->name;
                    $number=$authInfo->idcard_confirm->idcard_number;
                    $authList=array();
                    for($i=2019;$i<=2021;$i++){
                        $authList=array_merge($authList,json_decode(file_get_contents("information/".strval($i).".json"),true));
                    }
                    $authStatus=null;
                    foreach ($authList as $value){
                        if(md5($number)==$value && $number!=null){
                            $authStatus="passed";
                        }
                    }
                    //数据库连接
                    $conn=new mysqli(database::addr,database::user,database::pass,database::name);
                    $sql=$conn->prepare("UPDATE `users` SET `name`=?,`nickname`=?,`status`=? WHERE `uid`=?");
                    echo mysqli_error($conn);
                    $sql->bind_param('sssi',$name,$nicknameRead,$authStatus,$uid);
                    $sql->execute();
                    $sql=$conn->prepare("SELECT `status`,`songNumsLimit`,`songTimeUsed`,`songLastTime`,`name`,`type`,`auth` FROM `users` WHERE `uid`=?");
                    $sql->bind_param("i",$_COOKIE["uid"]);
                    $sql->execute();
                    $sql->bind_result($status,$songNumsLimit,$songTimeUsed,$songLastTime,$name,$type,$auth);
                    while($sql->fetch()){
                        $rStatus=$status;
                        $rSongTimeLimit=$songNumsLimit;
                        $rSongTimeUsed=$songTimeUsed;
                        $rSongLastTime=$songLastTime;
                        $rName=$name;
                        $rType=$type;
                        $rAuth=$auth;
                    }
                    if($rStatus=="passed"){
                        $auth=true;
                    }else{
                        $auth=false;
                    }
                    if($rAuth!=null){
                        $examineAuth=true;
                    }else{
                        $examineAuth=false;
                    }
                    $conn->close();
                    $ghinkAuth=$sdk->checkAuth(intval($_COOKIE["uid"]));
                    exit(json_encode(array("status"=>true,"detail"=>array("uid"=>$_COOKIE["uid"],"nickname"=>$_COOKIE["name"],"name"=>$name,"auth"=>$auth,"examineAuth"=>$examineAuth,"ghinkAuth"=>$ghinkAuth,"type"=>$rType,"song"=>array("limit"=>$rSongTimeLimit,"used"=>$rSongTimeUsed,"last"=>$rSongLastTime,"balace"=>songBalace($rSongTimeLimit,$rSongTimeUsed,$rSongLastTime))))));
                }
            }
            break;
        case "list":
            //数据库连接
            $conn=new mysqli(database::addr,database::user,database::pass,database::name);             
            //获取黑名单
            $black=array();
            $sql=$conn->prepare("SELECT `id` FROM `blacklist` WHERE 1");
            $sql->execute();
            $sql->bind_result($id);
            while($sql->fetch()){
                
                $aContext = array(
                    'ssl' => array(
                        'verify_peer' => false,
                    ),
                );
                $cxContext = stream_context_create($aContext);
                $result=json_decode(file_get_contents("https://apiv1.radio.fmhs.club/id/?id=".$id, false, $cxContext));
                array_push($black,$result->result[0]);
            }
            
            //获取白名单
            $white=array();
            $sql=$conn->prepare("SELECT `id` FROM `whitelist` WHERE 1");
            $sql->execute();
            $sql->bind_result($id);
            while($sql->fetch()){
                
                $aContext = array(
                    'ssl' => array(
                        'verify_peer' => false,
                    ),
                );
                $cxContext = stream_context_create($aContext);
                $result=json_decode(file_get_contents("https://apiv1.radio.fmhs.club/id/?id=".$id, false, $cxContext));
                array_push($white,$result->result[0]);
            }
            $list=array("white"=>$white,"black"=>$black);
            $conn->close();
            exit(json_encode($list));
            break;
        case "record":
            //数据库连接
            $conn=new mysqli(database::addr,database::user,database::pass,database::name);   
            //获取列表
            $records=array();
            $sql=$conn->prepare("SELECT `date`, `id`, `uid` FROM `record` WHERE `status`=\"passed\" ORDER BY `time` DESC");
            $sql->execute();
            $sql->bind_result($date,$id,$uid);
            while($sql->fetch()){
                                
                $aContext = array(
                    'ssl' => array(
                        'verify_peer' => false,
                    ),
                );
                $cxContext = stream_context_create($aContext);
                $result=json_decode(file_get_contents("https://apiv1.radio.fmhs.club/id/?id=".$id, false, $cxContext));
                array_push($records,array("date"=>$date,"user"=>$uid,"song"=>$result->result[0]));
            }
            foreach ($records as $key => $value){
                $sql=$conn->prepare("SELECT `nickname` FROM `users` WHERE `uid`=?");
                $sql->bind_param("i",$records[$key]["user"]);
                $sql->execute();
                $sql->bind_result($nickname);
                while($sql->fetch()){
                    $records[$key]["user"]=$nickname;
                }
            }
            $conn->close();
            exit(json_encode($records));
            break;
        case "myRecord":
            if($_COOKIE["token"]==""){
                exit(json_encode(array("status"=>false)));
            }else{
                if(!$sdk->checkToken($_COOKIE["token"])){
                    setcookie("token","",-1,"/");
                    setcookie("uid","",-1,"/");
                    setcookie("name","",-1,"/");
                    header("Refresh:0");
                    $conn->close();
                    exit(json_encode(array("status"=>false)));
                }else{
                    //数据库连接
                    $conn=new mysqli(database::addr,database::user,database::pass,database::name);   
                    //获取列表
                    $records=array();
                    $sql=$conn->prepare("SELECT `date`, `id` FROM `record` WHERE `uid`=? ORDER BY `date` DESC");
                    $sql->bind_param("i",$_COOKIE["uid"]);
                    $sql->execute();
                    $sql->bind_result($date,$id);
                    while($sql->fetch()){
                        
                        $aContext = array(
                            'ssl' => array(
                                'verify_peer' => false,
                            ),
                        );
                        $cxContext = stream_context_create($aContext);
                        $result=json_decode(file_get_contents("https://apiv1.radio.fmhs.club/id/?id=".$id, false, $cxContext));
                        array_push($records,array("date"=>$date,"song"=>$result->result[0]));
                    }
                    $conn->close();
                    exit(json_encode($records));
                }
            }
            break;
        case "playlist":
            //数据库连接
            $conn=new mysqli(database::addr,database::user,database::pass,database::name);   
            //获取列表
            $records=array();
            $sql=$conn->prepare("SELECT `id`, `uid` FROM `record` WHERE `status`=\"passed\" AND `date`=? ORDER BY `time`");
            $sql->bind_param("s",date("Y-m-d",time()));
            $sql->execute();
            $sql->bind_result($id,$uid);
            while($sql->fetch()){
                                
                $aContext = array(
                    'ssl' => array(
                        'verify_peer' => false,
                    ),
                );
                $cxContext = stream_context_create($aContext);
                $result=json_decode(file_get_contents("https://apiv1.radio.fmhs.club/id/?id=".$id, false, $cxContext));
                array_push($records,array("user"=>$uid,"song"=>$result->result[0]));
            }
            foreach ($records as $key => $value){
                $sql=$conn->prepare("SELECT `nickname` FROM `users` WHERE `uid`=?");
                $sql->bind_param("i",$records[$key]["user"]);
                $sql->execute();
                $sql->bind_result($nickname);
                while($sql->fetch()){
                    $records[$key]["user"]=$nickname;
                }
            }
            $conn->close();
            echo json_encode($records);
            break;
        case "adminAuth":
            if($_COOKIE["token"]==""){
                exit(json_encode(array("status"=>false)));
            }else{
                if(!$sdk->checkToken($_COOKIE["token"])){
                    setcookie("token","",-1,"/");
                    setcookie("uid","",-1,"/");
                    setcookie("name","",-1,"/");
                    header("Refresh:0");
                    $conn->close();
                    exit(json_encode(array("status"=>false)));
                }else{
                    //数据库连接
                    $conn=new mysqli(database::addr,database::user,database::pass,database::name);
                    $sql=$conn->prepare("SELECT `type` FROM `users` WHERE `uid`=?");
                    $sql->bind_param("i",$_COOKIE["uid"]);
                    $sql->execute();
                    $sql->bind_result($type);
                    while($sql->fetch()){
                        $rType=$type;
                    }
                    if($rType=="member"){
                        $authList=array();
                        $sql=$conn->prepare("SELECT `uid`, `nickname`, `name`, `auth` FROM `users` WHERE `status` IS NULL AND `auth` IS NOT NULL");
                        $sql->execute();
                        $sql->bind_result($uid,$nickname,$name,$auth);
                        while($sql->fetch()){
                            array_push($authList,array($uid,$nickname,$name,json_decode($auth)));
                        }
                        $conn->close();
                        exit(json_encode(array("status"=>true,"detail"=>$authList)));
                    }else{
                        $conn->close();
                        exit(json_encode(array("status"=>false)));
                    }
                }
            }
            break;
        case "adminSong":
            if($_COOKIE["token"]==""){
                exit(json_encode(array("status"=>false)));
            }else{
                if(!$sdk->checkToken($_COOKIE["token"])){
                    setcookie("token","",-1,"/");
                    setcookie("uid","",-1,"/");
                    setcookie("name","",-1,"/");
                    header("Refresh:0");
                    $conn->close();
                    exit(json_encode(array("status"=>false)));
                }else{
                    //数据库连接
                    $conn=new mysqli(database::addr,database::user,database::pass,database::name);
                    $sql=$conn->prepare("SELECT  `recId`, `date`, `pid`, `id`, `uid`, `time` FROM `record` WHERE `status` IS NULL ORDER BY time");
                    $sql->execute();
                    $sql->bind_result($recId,$date,$pid,$id,$uid,$time);
                    $songFall=array();
                    $outOfDateFall=array();
                    while($sql->fetch()){
                        if(($time/1000 >= strtotime(getSat())) || ((((strtotime($date) >= time())) && date("w",time())!=0) || (((time() - strtotime($date)) >= 86400)) && date("w",time())==0)){
                            switch($pid){
                                case 1:
                                    $getData=array("keyword"=>$id);
                                    $reqResult=json_decode(sendGet("https://apiv1.radio.fmhs.club/search/",$getData));
                                    foreach ($reqResult->result as $value){
                                        if($value->id==$id){
                                            $result=$value;
                                        }
                                    }
                                    break;
                            }
                            array_push($songFall,array("recId"=>$recId,"date"=>$date,"pid"=>$pid,"id"=>$id,"uid"=>$uid,"time"=>$time,"detail"=>$result));
                        }else{
                            array_push($outOfDateFall,$recId);
                        }
                    }
                    for($i=0;$i<count($outOfDateFall);$i++){
                        $sql=$conn->prepare("UPDATE `record` SET `status`='outOfDate' WHERE `recId`=?");
                        $sql->bind_param("i",intval($outOfDateFall[$i]));
                        $sql->execute();
                    }
                    $conn->close();
                    exit(json_encode(array("status"=>true,"detail"=>$songFall)));
                }
            }
            break;
        case "adminSongOperate":
            if($_COOKIE["token"]==""){
                exit(json_encode(array("status"=>false)));
            }else{
                if(!$sdk->checkToken($_COOKIE["token"])){
                    setcookie("token","",-1,"/");
                    setcookie("uid","",-1,"/");
                    setcookie("name","",-1,"/");
                    header("Refresh:0");
                    $conn->close();
                    exit(json_encode(array("status"=>false)));
                }else{
                    if(@$_GET["operate"]!="" && @$_GET["recId"]!=""){
                        //数据库连接
                        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
                        $sql=$conn->prepare("SELECT `id`,`status` FROM `record` WHERE `recId`=?");
                        $recId=intval($_GET["recId"]);
                        $sql->bind_param("i",$recId);
                        $sql->execute();
                        $sql->bind_result($id,$status);
                        $flag=false;
                        while($sql->fetch()){
                            if($status==null){
                                $flag=true;
                            }
                            $rId=$id;
                        }
                        if($flag && ($_GET["operate"]=="pass" || $_GET["operate"]=="reject" || $_GET["operate"]=="ignore")){
                            $operate=array("pass"=>"passed","reject"=>"rejected","ignore"=>"ignored");
                            $sql=$conn->prepare("UPDATE `record` SET `status`=? WHERE `recId`=?");
                            $recId=intval($_GET["recId"]);
                            $sql->bind_param("si",$operate[$_GET["operate"]],$recId);
                            $sql->execute();
                            if($_GET["operate"]=="pass"){
                                $sql=$conn->prepare("SELECT `id` FROM `whitelist` WHERE `id`=?");
                                $sql->bind_param("i",$rId);
                                $sql->execute();
                                $sql->bind_result($id);
                                $exists=false;
                                while($sql->fetch()){
                                    if($id!=null){
                                        $exists=true;
                                    }
                                }
                                if(!$exists){
                                    $sql=$conn->prepare("INSERT INTO `whitelist`(`id`) VALUES (?)");
                                    $sql->bind_param("i",$rId);
                                    $sql->execute();
                                }
                            }else if($_GET["operate"]=="reject"){
                                $sql=$conn->prepare("SELECT `id` FROM `blacklist` WHERE `id`=?");
                                $sql->bind_param("i",$rId);
                                $sql->execute();
                                $sql->bind_result($id);
                                $exists=false;
                                while($sql->fetch()){
                                    if($id!=null){
                                        $exists=true;
                                    }
                                }
                                if(!$exists){
                                    $sql=$conn->prepare("INSERT INTO `blacklist`(`id`) VALUES (?)");
                                    $sql->bind_param("i",$rId);
                                    $sql->execute();
                                }
                            }
                            echo json_encode(array("status"=>true));
                        }else{
                            echo json_encode(array("status"=>false));
                        }
                        $conn->close();
                    }else{
                        echo json_encode(array("status"=>false));
                    }
                }
            }
            break;
        default:
            break;
    }
}