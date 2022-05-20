<?php
//初始化基础信息
include("../libraries/entrance.php");
include("../libraries/key.php");
include("../libraries/func.php");
//初始化SDK
$sdk=new ghinkPassport($global_id,$global_key);
//初始化请求头
header("content-type: application/json");
//DEBUG开关
$debug=false;

if($debug || @$_POST["token"]!=""){
    if($debug || @$_POST["pid"]!=""){
        if($debug || @$sdk->checkTokenApp($_POST["token"])){
            if($debug || @$sdk->checkLevelApp($_POST["token"])=="official"){
        		//数据库连接
                $conn=new mysqli(database::addr,database::user,database::pass,database::name);
                switch (@$_POST["pid"]){
                    case "0":
                        $conn->close();
                        exit();
                    case "1"://手动添加
                        if(@$_POST["info"]!=""){
                            $json=json_decode($_POST["info"]);
                        }else{
                            exit(json_encode(array("status"=>"failed","message"=>"need song info")));
                        }
                        $conn->close();
                        exit();
                    case "2"://网易云
                        if(@$_POST["sid"]!=""){
                            //不阻塞开关
                            if(@$_POST["noWait"]=="true"){
                                echo(json_encode(array("status"=>"success","message"=>"task add success")));
                                fastcgi_finish_request();
                            }
                            //取回cookie
                            $cookie=file_get_contents("../cookie.conf");
                            //拉取歌曲信息
                            $details=json_decode(file_get_contents("http://cloud-music.pl-fe.cn/song/detail?ids=".$_POST["sid"]."&cookie=".urlencode($cookie)));
                            $sql=$conn->prepare("SELECT `sid`, `name`, `alias`, `alid`, `arid`, `hash` FROM `songs` WHERE `name`=?");
                            $sql->bind_param("s",$details->songs[0]->name);
            				$sql->execute();
            				$sql->bind_result($sid,$name,$alias,$alid,$arid,$hash);
            				$rName="";
                            while($sql->fetch()){
                                if($name==$details->songs[0]->name){
                                    $rSId=$sid;
                                    $rName=$name;
                                    $rAlias=$alias;
                                    $rAlId=$alid;
                                    $rArId=$arid;
                                    $rHash=$hash;
                                }
                            }
                            //检测是否已经存在
                            //理顺歌手信息
                            $artists=$details->songs[0]->ar;
                            $artistsFormat=array();
                            foreach ($artists as $value){
                                array_push($artistsFormat,addArtist($conn,$value->name));
                            }
                            $artistsList=array();
                            foreach ($artistsFormat as $value){
                                array_push($artistsList,$value["arid"]);
                            }
                            //检索本地歌手信息
                            $artistsLocal=array();
                            foreach (json_decode($rArId) as $value){
                                array_push($artistsLocal,getArtist($conn,$value));
                            }
                            if($rName==$details->songs[0]->name && $artistsList==$artistsLocal){
                                $album=getAlbum($conn,$rAlId);
    				            echo(json_encode(array("status"=>"success","message"=>"success","result"=>array("id"=>$rSId,"name"=>$rName,"url"=>"https://cache.music.ghink.net/song/".$rHash,"hash"=>$rHash,"ar"=>$artistsLocal,"al"=>$album))));
                            }else{
                                //判断是否有返回值
                                if(count($details->songs)==0){
                                    echo(json_encode(array("status"=>"failed","message"=>"cannot find the song")));
                                }else{
                                    //拉取URL
                                    $data=json_decode(file_get_contents("http://cloud-music.pl-fe.cn/song/url?id=".$_POST["sid"]."&cookie=".urlencode($cookie)));
                                    $url=$data->data[0]->url;
                                    //判断是否拉取成功（避免网易云去世）
                                    if($url!=""){
                                        //拉取歌曲文件信息
                                        $md5=$data->data[0]->md5;
                                        $name=$details->songs[0]->name;
                                        //拉取封面
                                        $coverUrl=$details->songs[0]->al->picUrl;
                                        $coverFile=file_get_contents($coverUrl);
                                        $coverHash=md5($coverFile);
                                        file_put_contents("../cache/cover/".$coverHash,$coverFile);
                                        //理顺专辑信息
                                        $albumFormat=addAlbum($conn,$details->songs[0]->al->name,NULL,$coverHash);
                                        //拉取歌曲文件
                                        while (true){
                                            $songFile=file_get_contents($url);
                                            $songHash=md5($songFile);
                                            if($songHash==$md5){
                                                break;
                                            }
                                        }
                                        file_put_contents("../cache/song/".$songHash,$songFile);
                                        //入库
                                        $song=addSong($conn,$name,$albumFormat["alid"],json_encode($artistsList),$md5);
                                        //输出
            				            echo(json_encode(array("status"=>"success","message"=>"success","result"=>array("id"=>$song["sid"],"name"=>$song['name'],"url"=>"https://cache.music.ghink.net/song/".$song['hash'],"hash"=>$song['hash'],"ar"=>$artistsFormat,"al"=>$albumFormat))));
                                    }else{
                                        echo(json_encode(array("status"=>"failed","message"=>"failed to quest the api")));
                                    }
                                }
                            }
                        }else{
                            echo(json_encode(array("status"=>"failed","message"=>"need song info")));
                        }
                        $conn->close();
                        exit();
                    default:
                        $conn->close();
                        exit(json_encode(array("status"=>"failed","message"=>"wrong platform")));
                }
            }else{
                exit(json_encode(array("status"=>"failed","message"=>"no permission")));
            }
        }else{
            exit(json_encode(array("status"=>"failed","message"=>"wrong app token")));
        }
    }else{
        exit(json_encode(array("status"=>"failed","message"=>"need song info")));
    }
}else{
    exit(json_encode(array("status"=>"failed","message"=>"need app token")));
}