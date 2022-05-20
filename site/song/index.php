<!DOCTYPE html>
<html lang="en">
  
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>在线点歌-凤鸣高级中学广播站</title>
	<link rel="shortcut icon" href="https://cdn.fmhs.club/image/radio/favicon.png">
    <link href="https://cdn.ghink.net/assembly/bootstrap/4.6.1/css/bootstrap.min.css" rel="stylesheet">
  
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
      <h5>
          <div id="notice"></div>
      </h5>
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
    <script src="https://cdn.ghink.net/js/jquery/jquery-3.6.0.min.js"></script>
    <script src="https://resource.ghink.net/site/public/js/popper.min.js"></script>
    <script src="https://cdn.ghink.net/assembly/bootstrap/4.6.1/js/bootstrap.min.js"></script>
    <script>
        var pageGlobal = 1;
	    var JsonData = {
            type: "user"
        };
        $.get("https://radio.fmhs.club/song/api.php", JsonData,
        function(data) {
            if(data["status"]==true){
                document.getElementById("top").innerHTML=`
              <a href="https://radio.fmhs.club/">返回首页</a>&nbsp;<a href="index.php">在线点歌</a>&nbsp;<a href="record.php">点歌记录</a>&nbsp;<a href="list.php">黑白名单</a>&nbsp;<a href="myRecord.php">我的已点</a>
              <div style="text-align:right;"><a href="../center/user.php">欢迎您！`+data["detail"]["nickname"]+`</a>&nbsp;<a href='../center/logout.php'>退出登陆</a></div>`;
                window.uid=String(data["detail"]["uid"]);
                window.auth=data["detail"]["auth"];
                window.balace=data["detail"]["song"]["balace"];
            }else{
                window.uid="";
                window.auth=false;
                window.balace=false;
            }
    	    var JsonData = {
                type: "system"
            };
            $.get("https://radio.fmhs.club/song/api.php", JsonData,
            function(data) {
                window.switcher=data["switcher"];
                window.timeQuatum=data["timeInt"];
                window.igTime=data["igTime"];
                document.getElementById("notice").innerHTML="<center>"+data["notice"]+"</center>";
                document.getElementById("body").innerHTML=
                `
                    <form onsubmit="return false">
                      <div class="form-group" id="songSelect"></div>
                      <div class="form-group" id="search">
                        <label for="name">请输入要查找的歌曲名</label>
                        <input type="text" class="form-control" id="info" name="info" placeholder="歌曲名"></div>
                      <div class="text-center">
                        <button class="btn btn-primary" id="searchSumbit" onClick="search()">搜索</button></div>
                    </form>
                `;
            })
            
        })
        function search() {
            pageGlobal=1;
            searchSong();
        }
        function searchSong() {
            var info = $('input[name="info"]').val();
            document.getElementById("searchSumbit").innerHTML="加载中";
            
            var JsonData = {
                keyword: info,
                page: pageGlobal
            };
            $.get("https://radio.fmhs.club/song/search.php", JsonData,
            function(data) {
                console.log(data);
                var result="";
                for(let i=0;i<data.length;i++){
                    if(data[i]["platform"]=="ghink"){
                        for(let i2=0;i2<data[i]["result"].length;i2++){
                            console.log(data[i]["result"][i2]);
                            var artist="";
                            for(let i3=0;i3<data[i]["result"][i2]["ar"].length;i3++){
                                if(i3==data[i]["result"][i2]["ar"].length-1){
                                    artist+=data[i]["result"][i2]["ar"][i3]["name"];
                                }else{
                                    artist+=data[i]["result"][i2]["ar"][i3]["name"]+" , ";
                                }
                            }
                            result+=`<li>(极科音乐) `+data[i]["result"][i2]["name"]+`&nbsp;-&nbsp;`+artist+`&nbsp;<br><audio src="`+data[i]["result"][i2]["url"]+`" controls loop></audio><br><span onClick="confirm('`+data[i]["result"][i2]["id"]+`',1,'`+data[i]["result"][i2]["name"]+`&nbsp;-&nbsp;`+artist+`')" style="color:dodgerblue;">就它了</span></li>`;
                        }
                    }else if(data[i]["platform"]=="netease"){
                        for(let i2=0;i2<data[i]["result"].length;i2++){
                            console.log(data[i]["result"][i2]);
                            var artist="";
                            for(let i3=0;i3<data[i]["result"][i2]["ar"].length;i3++){
                                if(i3==data[i]["result"][i2]["ar"].length-1){
                                    artist+=data[i]["result"][i2]["ar"][i3]["name"];
                                }else{
                                    artist+=data[i]["result"][i2]["ar"][i3]["name"]+" , ";
                                }
                            }
                            result+=`<li>(网易云音乐) `+data[i]["result"][i2]["name"]+`&nbsp;-&nbsp;`+artist+`&nbsp;<br><audio src="`+data[i]["result"][i2]["url"]+`" controls loop></audio><br><span onClick="confirm('`+data[i]["result"][i2]["id"]+`',2,'`+data[i]["result"][i2]["name"]+`&nbsp;-&nbsp;`+artist+`')" style="color:dodgerblue;">就它了</span></li>`;
                        }
                    }
                }
                if (result==""){
                    result+="<li>找不到任何结果！</li>";
                }else{
                    result+=`<span onClick="pageControl('down')" style="color:dodgerblue;">上一页</span>&nbsp;<span onClick="pageControl('up')" style="color:dodgerblue;">下一页</span>&nbsp;&nbsp;`;
                    result+=`当前为第`+pageGlobal+`页`;
                }
                document.getElementById("songSelect").innerHTML=result;
                document.getElementById("searchSumbit").innerHTML="再搜一首";
            });
        }
        function pageControl(operate) {
            if(operate=="up"){
                pageGlobal+=1;
                searchSong();
            }else{
                if(pageGlobal==1){
                    print("已经是第一页啦！");
                }else{
                    pageGlobal-=1;
                    searchSong();
                }
            }
        }
        function print(data) {
          alert(data);
          console.log(data);
        }
        function isWechat(){
            var ua = window.navigator.userAgent.toLowerCase();
            if(ua.match(/MicroMessenger/i) == 'micromessenger'){
                return true;
            }else{
                return false;
            }
        }
        function isDuring(beginDateStr, endDateStr) {
            var curDate = new Date(),
                beginDate = new Date(beginDateStr),
                endDate = new Date(endDateStr);
            if (curDate >= beginDate && curDate <= endDate) {
                return true;
            }
            return false;
        }
        function confirm(id, platform, name) {
            if(uid!=""){
                if(auth){
                    var timeLimit = timeQuatum;
                    var day = new Date().getDay();
                    var date = new Date().getFullYear()+"-"+(new Date().getMonth()+1)+"-"+new Date().getDate();
                    var timestamp = Date.parse(new Date())/1000;
                    var timeSwitcher = false;
                    for (i in timeLimit){
                        if((day==timeLimit[i][0] && isDuring(date+" "+timeLimit[i][1],date+" "+timeLimit[i][2])) || igTime){
                            timeSwitcher = true;   
                        }
                    }
                    if(timeSwitcher){
                        if(balace){
                            if(switcher){
        		                window.open('confirm.php?uid='+uid+'&name='+name+'&id='+id+'&pid='+platform,"_blank","alwaysRaised=yes");
                            }else{
                                print("点歌通道已经关闭！");
                            }
                        }else{
                            print("您的本周点歌余额不足！");
                        }
                    }else{
                        print("当前非点歌时间段！");
                    }
                }else{
                    print("请先实名！");
                    window.location.href='https://radio.fmhs.club/center/auth.php';
                }
            }else{
                print("请先登录！");
                window.location.href='../center/login.php?location=https://radio.fmhs.club/song/';
            }
        }
        </script>
  </body>

</html>