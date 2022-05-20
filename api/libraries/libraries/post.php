<?php 
/*
Copyright GHINK Network Studio
Author: Bigsk(https://www.xiaxinzhe.cn)
*/
if(!defined('IN_SYS')){//Defined entreance security.
    header('HTTP/1.1 403 Forbidden');
    exit();
}

function sendPost($url, $post_data) {
    $postdata=http_build_query($post_data);
    $options=array(
      'http' => array(
        'method' => 'POST',
        'header' => 'Content-type:application/x-www-form-urlencoded',
        'content' => $postdata,
        'timeout' => 15*60
      ),
        'ssl' => array(
        'verify_peer' => false,
        ),
    );
    $context=stream_context_create($options);
    $result=file_get_contents($url,false,$context);
    return $result;
}