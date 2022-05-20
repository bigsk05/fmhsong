<?php 

function addSong($conn,$sName,$sAlid,$sArid,$sHash,$sAlias=NULL){
    $sql=$conn->prepare("SELECT `sid`, `name`, `alias`, `alid`, `arid`, `hash` FROM `songs` WHERE `name`=?");
    $sql->bind_param("s",$sName);
	$sql->execute();
	$sql->bind_result($sid,$name,$alias,$alid,$arid,$hash);
    $name="";
    while($sql->fetch()){
        if($name==$sName){
            $rSId=$sid;
            $rName=$name;
            $rAlias=$alias;
            $rArId=$arid;
            $rAlId=$alid;
            $rHash=$hash;
        }
    }
    if($name!="" && json_decode($rArId)==json_decode($sArid)){
        return array("sid"=>$rSId,"name"=>$rName,"alias"=>$rAlias,"arid"=>json_decode($rArId),"alid"=>$rAlId,"hash"=>$rHash);
    }else{
        $sql=$conn->prepare("INSERT INTO `songs`(`name`, `alias`, `alid`, `arid`, `hash`) VALUES (?,?,?,?,?)");
        $sql->bind_param("ssiss",$sName,$sAlias,$sAlid,$sArid,$sHash);
	    $sql->execute();
        $sid=mysqli_insert_id($conn);
        return array("sid"=>$sid,"name"=>$sName,"alias"=>$sAlias,"arid"=>json_decode($sArid),"alid"=>$sAlid,"hash"=>$sHash);
    }
}
function addAlbum($conn,$alName,$alAlias=NULL,$alCover=NULL){
    $sql=$conn->prepare("SELECT `alid`, `name`, `alias`, `cover` FROM `albums` WHERE `name`=?");
    $sql->bind_param("s",$alName);
	$sql->execute();
	$sql->bind_result($alid,$name,$alias,$cover);
    $name="";
    while($sql->fetch()){
        if($name==$alName){
            $rAlId=$alid;
            $rName=$name;
            $rAlias=$alias;
            $rCover=$cover;
        }
    }
    if($rName!=""){
        return array("alid"=>$rAlId,"name"=>$rName,"alias"=>$rAlias,"cover"=>$rCover);
    }else{
        $sql=$conn->prepare("INSERT INTO `albums`(`name`, `alias`, `cover`) VALUES (?,?,?)");
        $sql->bind_param("sss",$alName,$alAlias,$alCover);
	    $sql->execute();
        $alid=mysqli_insert_id($conn);
        return array("alid"=>$alid,"name"=>$alName,"alias"=>$alAlias,"cover"=>$alCover);
    }
}
function addArtist($conn,$arName,$arAlias=NULL,$arPic=NULL){
    $sql=$conn->prepare("SELECT `arid`, `name`, `alias`, `pic` FROM `artists` WHERE `name`=?");
    $sql->bind_param("s",$arName);
	$sql->execute();
	$sql->bind_result($arid,$name,$alias,$pic);
    $name="";
    while($sql->fetch()){
        if($name==$arName){
            $rArId=$arid;
            $rName=$name;
            $rAlias=$alias;
            $rPic=$pic;
        }
    }
    if($name!=""){
        return array("arid"=>$rArId,"name"=>$rName,"alias"=>$rAlias,"pic"=>$rPic);
    }else{
        $sql=$conn->prepare("INSERT INTO `artists`(`name`, `alias`, `pic`) VALUES (?,?,?)");
        $sql->bind_param("sss",$arName,$arAlias,$arPic);
	    $sql->execute();
        $arid=mysqli_insert_id($conn);
        return array("arid"=>$arid,"name"=>$arName,"alias"=>$arAlias,"pic"=>$arPic);
    }
}
function getArtist($conn,$arid){
    $sql=$conn->prepare("SELECT `arid`, `name`, `alias`, `pic` FROM `artists` WHERE `arid`=?");
    $sql->bind_param("i",$arid);
	$sql->execute();
	$sql->bind_result($arid,$name,$alias,$pic);
    $name="";
    while($sql->fetch()){
        $rArId=$arid;
        $rName=$name;
        $rAlias=$alias;
        $rPic=$pic;
    }
    return array("arid"=>$rArId,"name"=>$rName,"alias"=>$rAlias,"pic"=>$rPic);
}
function getAlbum($conn,$alid){
    $sql=$conn->prepare("SELECT `alid`, `name`, `alias`, `cover` FROM `albums` WHERE `alid`=?");
    $sql->bind_param("i",$alid);
	$sql->execute();
	$sql->bind_result($alid,$name,$alias,$cover);
    $name="";
    while($sql->fetch()){
        $rAlId=$alid;
        $rName=$name;
        $rAlias=$alias;
        $rCover=$cover;
    }
    return array("alid"=>$rAlId,"name"=>$rName,"alias"=>$rAlias,"cover"=>$rCover);
}