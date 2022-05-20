<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>管理后台 - 凤鸣高级中学广播站</title>
    <!-- Bootstrap -->
    <link href="https://resource.ghink.net/site/public/css/bootstrap_4.4.1.css" rel="stylesheet">
  </head>
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
              <div class="form-group">
					<div id="body">
					    <center><h3>加载中</h3></center>
				        <hr>
					    <center><h5>客官请稍后，结果马上来...</h5></center>
				    </div>
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
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://resource.ghink.net/site/public/js/jquery.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
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
                    type: "system"
                };
                $.get("https://radio.fmhs.club/song/api.php", JsonData,
                function(data) {
                    console.log(data);
                    var body="";
                    var notice=data["notice"].replace(new RegExp("<br>", 'g'),"\n");
                    body+=` <form method="get" class="form-example">
                              <div>
                                <h5><label for="notice">公告: </label></h5>
                                <textarea type="text" name="notice" id="notice" style="width: 100%;">`+notice+`</textarea>
                              </div>
                              <div>
                                </br>
                                <input type="submit" class="btn btn-primary" value="保存">
                              </div>
                            </form>`;
                    document.getElementById("body").innerHTML=body;
                })
            }else{
                alert("非法访问！");
                window.location.href='../center/login.php?location=https://radio.fmhs.club/song/';
            }
        })
	</script>
  </body>
</html>