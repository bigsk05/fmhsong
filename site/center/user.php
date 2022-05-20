<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>个人信息 - 凤鸣高级中学广播站</title>
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
          <a href="https://radio.fmhs.club/">返回首页</a>&nbsp;<a href="index.php">在线点歌</a>&nbsp;<a href="record.php">点歌记录</a>&nbsp;<a href="list.php">黑白名单</a>
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
            if(data["status"]==true){
                document.getElementById("top").innerHTML=`
              <a href="https://radio.fmhs.club/">返回首页</a>&nbsp;<a href="../song/index.php">在线点歌</a>&nbsp;<a href="../song/record.php">点歌记录</a>&nbsp;<a href="list.php">黑白名单</a>&nbsp;<a href="../song/myRecord.php">我的已点</a>
              <div style="text-align:right;"><a href="user.php">欢迎您！`+data["detail"]["nickname"]+`</a>&nbsp;<a href='logout.php'>退出登陆</a></div>`;
                var body="";
                body+="<h4>个人信息：</h4>";
                body+="<h5>昵称："+data["detail"]["nickname"]+"</h5>";
                body+="<h5>UID："+data["detail"]["uid"]+"</h5>";
                if(data["detail"]["type"]=="member"){
                    var type="管理员（广播站成员）";
                }else{
                    var type="用户";
                }
                body+="<h5>权限："+type+"</h5>";
                if(data["detail"]["auth"]==true){
                    var auth="<a href=\"auth.php\">"+data["detail"]["name"]+"</a>";
                }else{
                    var auth="您还没有实名！<a href='auth.php'>点我去实名</a>";
                }
                body+="<h5>实名信息："+auth+"</h5>";
                body+="<hr>";
                body+="<h4>点歌信息：</h4>";
                body+="<h5>周限制量："+data["detail"]["song"]["limit"]+"</h5>";
                body+="<h5>已用量："+data["detail"]["song"]["used"]+"</h5>";
                if(data["detail"]["song"]["last"]!="1970-01-01"){
                    var date=data["detail"]["song"]["last"];
                }else{
                    var date="您还没有点过歌！";
                }
                body+="<h5>最后点歌于："+date+"</h5>";
                
                document.getElementById("body").innerHTML=body;
            }
        })
	</script>
  </body>
</html>