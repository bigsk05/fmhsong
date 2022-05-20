<?php 
//初始化基础信息
include("../libraries/entrance.php");
include("../libraries/key.php");
include("../libraries/func.php");
header("Access-Control-Allow-Origin: *");
//初始化SDK
$sdk=new ghinkPassport($global_id,$global_key);
//初始化请求头
header("content-type: application/json");

if(@$_GET["keyword"]!=""){
	//数据库连接
    $conn=new mysqli(database::addr,database::user,database::pass,database::name);
    $result=array();
    $songs=array();
    //按歌名查找歌曲
    $sql=$conn->prepare("SELECT `sid`, `name`,  `alid`, `arid`, `hash` FROM `songs` WHERE `name` LIKE ? OR `sid`=?");
    $keyword="%".$_GET['keyword']."%";
    $sql->bind_param("si",$keyword,$_GET['keyword']);
    $sql->execute();
    $sql->bind_result($sid,$name,$alid,$arid,$hash);
    while($sql->fetch()){
        array_push($songs,array("sid"=>$sid,"name"=>$name,"alid"=>$alid,"arid"=>$arid,"hash"=>$hash));
    }
    //理顺结果
    foreach ($songs as $value){
        $artistsFormat=array();
        foreach (json_decode($value["arid"]) as $value2){
            array_push($artistsFormat,getArtist($conn,$value2));
        }
        $albumFormat=getAlbum($conn,$value["alid"]);
        array_push($result,array("id"=>$value["sid"],"name"=>$value["name"],"url"=>"https://cachev1.radio.fmhs.club/song/".$value["hash"],"hash"=>$value["hash"],"ar"=>$artistsFormat,"al"=>$albumFormat));
    }
    $conn->close();
    exit(json_encode(array("status"=>"success","message"=>"success","result"=>$result)));
}else{
    exit(json_encode(array("status"=>"failed","message"=>"need song info")));
}