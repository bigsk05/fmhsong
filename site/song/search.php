<?php

function sendPost($url, $post_data) {
    $postdata=http_build_query($post_data);
    $options=array(
        'ssl' => array(
        'verify_peer' => false,
        ),
        'http' => array(
            'method' => 'POST',
            'header' => 'Content-type:application/x-www-form-urlencoded',
            'content' => $postdata,
            'timeout' => 15*60
        )
    );
    $context=stream_context_create($options);
    $result=file_get_contents($url,false,$context);
    return $result;
}
function sendGet($url, $get_data) {
    $result="?";
    $options=array(
        'ssl' => array(
        'verify_peer' => false,
        ),
        'http' => array(
            'method' => 'POST',
            'header' => 'Content-type:application/x-www-form-urlencoded',
            'content' => $postdata,
            'timeout' => 15*60
        )
    );
    $context=stream_context_create($options);
    foreach ($get_data as $key => $value){
        if($key==end($get_data)){
            $result=$result.$key."=".urlencode($value);
        }else{
            $result=$result.$key."=".urlencode($value)."&";
        }
    }
    return file_get_contents($url.$result,false,$context);
}

$cookie=file_get_contents("cookie.conf");
header("Content-type: application/json");

if(@$_GET["keyword"]!=""){
    
    if(@$_GET["page"]!=""){
        $page=intval($_GET["page"])-1;
    }else{
        $page=0;
    }
    
    $result=array();
    
    //极科音乐
    //构建查询数据
    $getData=array(
        "limit"=>2,
        "keyword"=>$_GET["keyword"]
        );
    $data=sendGet("https://apiv1.radio.fmhs.club/search/",$getData);
    $ghink=json_decode($data);
    $ghinkFormat=array();
    foreach ($ghink->result as $value){
        $artists=array();
        foreach ($value->ar as $artist){
            $artist=json_decode(json_encode($artist),true);
            unset($artist["alias"]);
            unset($artist["pic"]);
            array_push($artists,$artist);
        }
        array_push($ghinkFormat,array("name"=>$value->name,"id"=>$value->id,"ar"=>$artists,"url"=>$value->url));
    }
    array_push($result,array("platform"=>"ghink","result"=>$ghinkFormat));
    
    //网易云
    //构建查询数据
    $postData=array(
        "limit"=>2,
        "offset"=>2*$page,
        "keywords"=>$_GET["keyword"],
        "cookie"=>$cookie
        );
    $data=sendGet("http://cloud-music.pl-fe.cn/cloudsearch",$postData);
    $netease=json_decode($data);
    $neteaseFormat=array();
    if($netease->result->songCount!=0){
        foreach ($netease->result->songs as $value){
            $artists=array();
            foreach ($value->ar as $artist){
                $artist=json_decode(json_encode($artist),true);
                unset($artist["alias"]);
                unset($artist["alia"]);
                unset($artist["tns"]);
                array_push($artists,$artist);
            }
            $data=json_decode(file_get_contents("http://cloud-music.pl-fe.cn/song/url?id=".$value->id."&cookie=".urlencode($cookie)));
            $url=$data->data[0]->url;
            array_push($neteaseFormat,array("name"=>$value->name,"id"=>$value->id,"ar"=>$artists,"url"=>str_replace("http://","https://",$url)));
        }
        array_push($result,array("platform"=>"netease","result"=>$neteaseFormat));
    }
    
    exit(json_encode($result));
    
}else{
    exit(json_encode(array("status"=>"failed","message"=>"wrong keywords")));
}