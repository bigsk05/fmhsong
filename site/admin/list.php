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
        	    var JsonData = {
                    type: "playlist"
                };
                $.get("https://radio.fmhs.club/song/api.php", JsonData,
                function(data) {
                    console.log(data);
                    var result="";
                    for(let i=0;i<data.length;i++){
                        console.log(data[i]);
                        var artist="";
                        for(let j=0;j<data[i]["song"]["ar"].length;j++){
                            if(j==data[i]["song"]["ar"].length-1){
                                artist+=data[i]["song"]["ar"][j]["name"];
                            }else{
                                artist+=data[i]["song"]["ar"][j]["name"]+" , ";
                            }
                        }
                        result+=`<li>`+data[i]["song"]["name"]+`&nbsp;-&nbsp;`+artist+`&nbsp;<br><audio src="`+data[i]["song"]["url"]+`" controls loop></audio><br><span style="color:dodgerblue;">点播者：`+data[i]["user"]+`</span></li>`;
                    }
                    if (result==""){
                        result+="<li>找不到任何结果！</li>";
                    }
                    document.getElementById("body").innerHTML=result;
                });
            }else{
                alert("非法访问！");
                window.location.href='../center/login.php?location=https://radio.fmhs.club/song/';
            }
        });
        </script>
  </body>

</html>