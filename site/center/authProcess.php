<?php
include("../key.php");

$allowedExts = array("gif", "jpeg", "jpg", "png", "JPG");
$temp = explode(".", $_FILES["upload"]["name"]);
$extension = end($temp);

function alert($text){
    echo "<script>alert('".$text."');</script>";
}

if ((($_FILES["upload"]["type"] == "image/gif")
|| ($_FILES["upload"]["type"] == "image/jpeg")
|| ($_FILES["upload"]["type"] == "image/jpg")
|| ($_FILES["upload"]["type"] == "image/pjpeg")
|| ($_FILES["upload"]["type"] == "image/x-png")
|| ($_FILES["upload"]["type"] == "image/png"))
&& in_array($extension, $allowedExts)){
  if ($_FILES["uploadfile"]["error"] > 0){
    alert("错误：".$_FILES["upload"]["error"]);
  }else{
        //数据库连接
        $conn=new mysqli(database::addr,database::user,database::pass,database::name);
        $file=file_get_contents($_FILES["upload"]["tmp_name"]);
        $file=base64_encode($file);
        $info=json_encode(array($_FILES["upload"]["type"],$file));
        $sql=$conn->prepare("UPDATE `users` SET `auth`=? WHERE `uid`=?");
        $sql->bind_param("si",$info,$_COOKIE["uid"]);
        $sql->execute();
        $conn->close();
        alert("上传成功！请等待审核！");
        /*
    alert("上传文件名: ".$_FILES["upload"]["name"]);
    alert("文件类型: ".$_FILES["upload"]["type"]);
    alert("文件大小: ".($_FILES["upload"]["size"] / 1024)."kB");
    alert("文件临时存储的位置: ".$_FILES["upload"]["tmp_name"]);
    if (file_exists("upload/".$_FILES["upload"]["name"])){
      alert($_FILES["upload"]["name"]." 文件已经存在");
    }else{
      move_uploaded_file($_FILES["upload"]["tmp_name"], "upload/".$_FILES["upload"]["name"]);
      alert("文件存储在: "."upload/".$_FILES["upload"]["name"]);
    }
    */
  }
}else{
  alert("非法的文件格式");
}
echo "<script>window.location.href='../center/login.php?location=https://radio.fmhs.club/center/auth.php';</script>";