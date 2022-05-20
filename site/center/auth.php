<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>实名认证系统 - 凤鸣高级中学广播站</title>
    <!-- Bootstrap -->
    <link href="https://resource.ghink.net/site/public/css/bootstrap_4.4.1.css" rel="stylesheet">
	<style>
		#qrcode{
		/*text-align: center;*/
		/*display: table-cell;*/
		/*width: 120px;*/
		/*height: 120px;*/
		/*vertical-align:middle;*/
		/*position: relative;*/
		}
	</style>
  </head>
  <body>
    <div class="container">
      <hr>
      <div class="row">
        <div class="col-12">
          <h3>在线点歌系统</h3>
        </div>
        <div class="col-12" id="top">
          <a href="https://radio.fmhs.club/">返回首页</a>&nbsp;<a href="../song/index.php">在线点歌</a>&nbsp;<a href="../song/record.php">点歌记录</a>&nbsp;<a href="../song/list.php">黑白名单</a>
          <div style="text-align:right;"><a href="login.php?location=https://radio.fmhs.club/song/">未登录</a></div>
        </div>
      </div>
      <hr>
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-8  col-12 jumbotron" id="body">
			    <center><h3>加载中</h3></center>
		        <hr>
			    <center><h5>客官请稍后，结果马上来...</h5></center>
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
    <script src="https://resource.ghink.net/site/public/js/qrcode.js"></script>
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
              <a href="https://radio.fmhs.club/">返回首页</a>&nbsp;<a href="../song/index.php">在线点歌</a>&nbsp;<a href="../song/record.php">点歌记录</a>&nbsp;<a href="../song/list.php">黑白名单</a>&nbsp;<a href="../song/myRecord.php">我的已点</a>
              <div style="text-align:right;"><a href="user.php">欢迎您！`+data["detail"]["nickname"]+`</a>&nbsp;<a href='logout.php'>退出登陆</a></div>`;
              var body="";
              body+="<h4>人脸识别状态：</h4>";
              if(data["detail"]["ghinkAuth"]){
                  body+="<h5>&nbsp;&nbsp;已完成</h5>";
                  body+="<h4>学生信息实名状态：</h4>";
                  if(data["detail"]["auth"]){
                    body+="<h5>&nbsp;&nbsp;已完成 &nbsp;"+data["detail"]["name"]+"</h5>";
                  }else{
                      if(data["detail"]["examineAuth"]){
                          body+=`<h5>&nbsp;&nbsp;审核中</h5>`;
                      }else{
                        body+=`<h5>&nbsp;&nbsp;未完成</h5>
                            <form id="upload" action="authProcess.php" method="post" enctype="multipart/form-data">
                            上传学生证/胸卡：<input type="file" id="upload" name="upload"/><br/><input type="submit" value="上传"/>
                            </form>`;
                      }
                  }
              }else{
                  body+="<h5>&nbsp;&nbsp;未完成&nbsp;<a href=\"https://center.ghink.net/v1/interface/oauth.php?type=auth\" target=\"_blank\">点我去实名</a></h5>";
                  body+="&nbsp;&nbsp;完成后请刷新本页面！";
              }
              document.getElementById("body").innerHTML=body;
            }
        })
		function back(){
			history.go(-1);
		}
	</script>
  </body>
</html>