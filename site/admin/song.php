<!DOCTYPE html>
<html lang="en">
  
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>管理后台-凤鸣高级中学广播站</title>
    <link href="https://resource.ghink.net/site/public/css/bootstrap_4.4.1.css" rel="stylesheet">
  
  <body>
    <div class="container">
      <hr>
      <div class="row">
        <div class="col-12">
          <h1>在线点歌系统</h1>
        </div>
        <div class="col-12" id="top">
          <a href="https://radio.fmhs.club/song">返回点歌系统</a>
          <div style="text-align:right;"><a href="login.php?location=https://radio.fmhs.club/song/">未登录</a></div>
        </div>
      </div>
      <hr>
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-8  col-12 jumbotron">
              <div id="body">
					    <center><h3>加载中</h3></center>
				        <hr>
					    <center><h5>客官请稍后，结果马上来...</h5></center>
              </div>
          </div>
        </div>
      </div>
      <hr>
      <footer class="text-center">
        <div class="container">
          <div class="row">
            <div class="col-12">
              <p>Copyright © Ghink Network Studio 2014. All rights reserved.</p>
            </div>
          </div>
        </div>
      </footer>
    </div>
    <script src="https://resource.ghink.net/site/public/js/jquery.js"></script>
    <script src="https://resource.ghink.net/site/public/js/popper.min.js"></script>
    <script src="https://resource.ghink.net/site/public/js/bootstrap_4.4.1.js"></script>
    <script>
        function pass(id){
            var ensure=confirm("您确定要通过吗？通过的同时会将歌曲加入白名单。");
            if(ensure){
                console.log("Pass "+id);
                var JsonData = {
                    type: "adminSongOperate",
                    operate: "pass",
                    recId: id
                };
                $.get("https://radio.fmhs.club/song/api.php", JsonData,
                function(data) {
                    if(data["status"]==true){
                        alert("成功！");
                        getList();
                    }else{
                        alert("失败！");
                    }
                })
            }
        }
        function reject(id){
            var ensure=confirm("您确定要驳回吗？驳回的同时会将歌曲加入黑名单。如果不希望将其加入黑名单，请使用忽略！");
            if(ensure){
                console.log("Reject "+id);
                var JsonData = {
                    type: "adminSongOperate",
                    operate: "reject",
                    recId: id
                };
                $.get("https://radio.fmhs.club/song/api.php", JsonData,
                function(data) {
                    if(data["status"]==true){
                        alert("成功！");
                        getList();
                    }else{
                        alert("失败！");
                    }
                })
            }
        }
        function ignore(id){
            var ensure=confirm("您确定要忽略吗？忽略不会对歌曲做任何操作，仅仅会忽略本次点歌。");
            if(ensure){
                console.log("Ignore "+id);
                var JsonData = {
                    type: "adminSongOperate",
                    operate: "ignore",
                    recId: id
                };
                $.get("https://radio.fmhs.club/song/api.php", JsonData,
                function(data) {
                    if(data["status"]==true){
                        alert("成功！");
                        getList();
                    }else{
                        alert("失败！");
                    }
                })
            }
        }
        function getList(){
    	    var JsonData = {
                type: "adminSong"
            };
            $.get("https://radio.fmhs.club/song/api.php", JsonData,
            function(data) {
                list=data["detail"];
                console.log(list);
                var result="";
                for(let i=0;i<list.length;i++){
                    console.log(list[i]);
                    var artist="";
                    for(let j=0;j<list[i]["detail"]["ar"].length;j++){
                        if(j==list[i]["detail"]["ar"].length-1){
                            artist+=list[i]["detail"]["ar"][j]["name"];
                        }else{
                            artist+=list[i]["detail"]["ar"][j]["name"]+" , ";
                        }
                    }
                    result+=`<li>`+list[i]["detail"]["name"]+`&nbsp;-&nbsp;`+artist+`&nbsp;<br>
                        <audio src="`+list[i]["detail"]["url"]+`" controls loop></audio><br>
                        <span></span><br>
                        <span onClick="pass(`+list[i]["recId"]+`)" style="color:dodgerblue;">通过</span>&nbsp;<span onClick="reject(`+list[i]["recId"]+`)" style="color:dodgerblue;">驳回</span>&nbsp;<span onClick="ignore(`+list[i]["recId"]+`)" style="color:dodgerblue;">忽略</span></li>`;
                }
                if (result==""){
                    result+="<li>找不到任何结果！</li>";
                }
                document.getElementById("body").innerHTML=result;
            });
        }
	    var JsonData = {
            type: "user"
        };
        $.get("https://radio.fmhs.club/song/api.php", JsonData,
        function(data) {
            console.log(data);
            if(data["status"]==true && data["detail"]["type"]=="member"){
                document.getElementById("top").innerHTML=`
              <a href="https://radio.fmhs.club/song">返回点歌系统</a>&nbsp;<a href="index.php">系统信息</a>&nbsp;<a href="song.php">歌曲审核</a>&nbsp;<a href="auth.php">实名审核</a>&nbsp;<a href="list.php">今日播放</a>
              <div style="text-align:right;"><a href="../center/user.php">欢迎您！`+data["detail"]["nickname"]+`</a>&nbsp;<a href='../center/logout.php'>退出登陆</a></div>`;
                getList();
            }else{
                alert("非法访问！");
                window.location.href='../center/login.php?location=https://radio.fmhs.club/song/';
            }
        });
        </script>
  </body>

</html>