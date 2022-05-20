 <!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>点歌记录 - 凤鸣高级中学广播站</title>
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
					<div id="record">
					    <center><h3>加载中</h3></center>
				        <hr>
					    <center><h5>客官请稍后，结果马上来...</h5></center>
				    </div>
			  </div>
			  <strong><center>（仅显示通过审核的曲目）</center></strong>
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
            type: "record"
        };
        $.get("https://radio.fmhs.club/song/api.php", JsonData,
        function(data) {
            console.log(data);
            var record=`<table width="100%"><tr><th>日期</th><th>曲目</th><th>点播者</th></tr>`;
            for(let i=0;i<data.length;i++){
                if(i==data.length-1){
                    record+="<tr><th>"+data[i]["date"]+"</th>";
                    record+="<th>"+data[i]["song"]["name"]+" - ";
                    for(let i2=0;i2<data[i]["song"]["ar"].length;i2++){
                        if(i2==data[i]["song"]["ar"].length-1){
                            record+=data[i]["song"]["ar"][i2]["name"]+"</th>";
                        }else{
                            record+=data[i]["song"]["ar"][i2]["name"]+" , ";
                        }
                    }
                    record+="<th>"+data[i]["user"]+"</th></tr></table>";
                }else{
                    record+="<tr><th>"+data[i]["date"]+"</th>";
                    record+="<th>"+data[i]["song"]["name"]+" - ";
                    for(let i2=0;i2<data[i]["song"]["ar"].length;i2++){
                        if(i2==data[i]["song"]["ar"].length-1){
                            record+=data[i]["song"]["ar"][i2]["name"]+"</th>";
                        }else{
                            record+=data[i]["song"]["ar"][i2]["name"]+" , ";
                        }
                    }
                    record+="<th>"+data[i]["user"]+"</th></tr>";
                }
            }
            document.getElementById("record").innerHTML=record;
        })
	</script>
  </body>
</html>