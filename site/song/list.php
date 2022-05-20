<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>黑/白名单 - 凤鸣高级中学广播站</title>
	<link rel="shortcut icon" href="https://cdn.fmhs.club/image/radio/favicon.png">
    <link href="https://cdn.ghink.net/assembly/bootstrap/4.6.1/css/bootstrap.min.css" rel="stylesheet">
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
          <div style="text-align:right;"><a href="../center/login.php?location=https://radio.fmhs.club/song/">未登录</a></div>
        </div>
      </div>
      <hr>
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-8  col-12 jumbotron">
              <div class="form-group">
					<div id="black">
					    <center><h3>加载中</h3></center>
				    </div>
				    <hr>
					<div id="white">
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
    <script src="https://cdn.ghink.net/js/jquery/jquery-3.6.0.min.js"></script>
    <script src="https://resource.ghink.net/site/public/js/popper.min.js"></script>
    <script src="https://cdn.ghink.net/assembly/bootstrap/4.6.1/js/bootstrap.min.js"></script>
	<script>
	    var JsonData = {
            type: "user"
        };
        $.get("https://radio.fmhs.club/song/api.php", JsonData,
        function(data) {
            console.log(data);
            if(data["status"]==true){
                document.getElementById("top").innerHTML=`
              <a href="https://radio.fmhs.club/">返回首页</a>&nbsp;<a href="index.php">在线点歌</a>&nbsp;<a href="record.php">点歌记录</a>&nbsp;<a href="list.php">黑白名单</a>&nbsp;<a href="myRecord.php">我的已点</a>
              <div style="text-align:right;"><a href="../center/user.php">欢迎您！`+data["detail"]["nickname"]+`</a>&nbsp;<a href='../center/logout.php'>退出登陆</a></div>`;
            }
        })
	    var JsonData = {
            type: "list"
        };
        $.get("https://radio.fmhs.club/song/api.php", JsonData,
        function(data) {
            console.log(data);
            //黑名单
            var black=`<h5>黑名单</h5><table width="100%"><tr><th>曲目</th><th>艺术家</th></tr>`;
            if(data["black"].length==0){
                black+=`<tr><th>暂无更多数据</th></tr></table>`;
            }else{
                for(let i=0;i<data["black"].length;i++){
                    if(i==data["black"].length-1){
                        black+="<tr><th>"+data["black"][i]["name"]+"</th>";
                        black+="<th>";
                        for(let i2=0;i2<data["black"][i]["ar"].length;i2++){
                            if(i2==i2<data["black"][i]["ar"].length-1){
                                black+=data["black"][i]["ar"][i2]["name"]+"</th></tr></table>";
                            }else{
                                black+=data["black"][i]["ar"][i2]["name"]+" , ";
                            }
                        }
                    }else{
                        black+="<tr><th>"+data["black"][i]["name"]+"</th>";
                        black+="<th>";
                        for(let i2=0;i2<data["black"][i]["ar"].length;i2++){
                            if(i2==i2<data["black"][i]["ar"].length-1){
                                black+=data["black"][i]["ar"][i2]["name"]+"</th><tr>";
                            }else{
                                black+=data["black"][i]["ar"][i2]["name"]+" , ";
                            }
                        }
                    }
                }
            }
            document.getElementById("black").innerHTML=black;
            //白名单
            var white=`<h5>白名单</h5><table width="100%"><tr><th>曲目</th><th>艺术家</th></tr>`;
            if(data["white"].length==0){
                black+=`<tr><th>暂无更多数据</th></tr></table>`;
            }else{
                for(let i=0;i<data["white"].length;i++){
                    if(i==data["white"].length-1){
                        white+="<tr><th>"+data["white"][i]["name"]+"</th>";
                        white+="<th>";
                        for(let i2=0;i2<data["white"][i]["ar"].length;i2++){
                            if(i2==i2<data["white"][i]["ar"].length-1){
                                white+=data["white"][i]["ar"][i2]["name"]+"</th></tr></table>";
                            }else{
                                white+=data["white"][i]["ar"][i2]["name"]+" , ";
                            }
                        }
                    }else{
                        white+="<tr><th>"+data["white"][i]["name"]+"</th>";
                        white+="<th>";
                        for(let i2=0;i2<data["white"][i]["ar"].length;i2++){
                            if(i2==i2<data["white"][i]["ar"].length-1){
                                white+=data["white"][i]["ar"][i2]["name"]+"</th><tr>";
                            }else{
                                white+=data["white"][i]["ar"][i2]["name"]+" , ";
                            }
                        }
                    }
                }
            }
            document.getElementById("white").innerHTML=white;
        })
	</script>
  </body>
</html>