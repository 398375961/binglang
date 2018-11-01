<!doctype html>
<html lang="en">
 <head>
  <meta charset="UTF-8">
  <meta name="Generator" content="EditPlus®">
  <meta name="Author" content="">
  <meta name="Keywords" content="">
  <meta name="Description" content="">
  <title>领取奖品</title>
<style>
body{
background-image:url('<?=SOURCE_ROOT?>api/bg/bg1.jpg');
background-size:cover;
padding:0px;
margin:0px;
}
.num_area{width:80%;margin:auto;margin-top:185px;}
.block{
	float:left;
	width:25%;
}
.block div{
	border-radius:5px;
	text-align:center;
	cursor:pointer;
	font-size:80px;
	background:#FFFFFF;
}
.block div:hover{background:#ff2626;color:#FFFFFF;}
.block .num_c,.block .num_del{
	background:#b9dcff;
	font-size:45px;
}
.in_area{
	clear:both;
	width:80%;margin:auto;
}
.code{
	width:90%;
	font-size:25px;
	font-weight:bold;
	text-align:center;
	line-height:40px;
}
.btn{
	width:80%;
	font-size:25px;
	font-weight:bold;
	line-height:40px;
}
.back{width:60%;cursor:pointer;margin-top:15px;color:#FFFFFF;background:#006fdd;
padding:15px 45px;font-weight:bold;font-size:24px;border-radius: 8px;}
.back:hover{background:#ff0000;}
.cent{text-align:center;padding:5px 0px;clear:both;}
#msg{position:fixed;z-index:1000;width:100%;top:100px;display:none;}
#msg div{margin:auto;width:350px;border:solid 2px #0069d2;border-radius:10px;background:#b0d8ff;padding:30px 15px;color:red;line-height:40px;font-weight:bold;text-align:center;}
</style>
<script type="text/javascript" src="<?=SOURCE_ROOT?>jquery.js"></script>
<script type="text/javascript">
var vmid = '<?=$VAL[vmid]?>';
$(document).ready(function(){
	var w = $('.num_area').width();
	var _w = parseInt(w/4*0.85);
	var margin = parseInt(w/4*0.068);
	$('.block').css({'height':parseInt(w/4)});
	$('.block>div').css({'width':_w,'height':_w,'line-height':_w+'px','margin':margin});
	$('.code').css({'margin':margin});
	$('.btn').css({'margin':margin});
	$('.block>div').click(function(){
		var key = $(this).html();
		var n = $('.code').val();
		switch(key){
			case '清空':
				$('.code').val('');
			break;
			case 'Del':
				if(n.length < 2) n = '';
				else n = n.substr(0,n.length - 1);
				$('.code').val(n);
			break;
			default:
				$('.code').val(n + key);
			break;
		}
	});
	get_vmid();
  });

//提交验证码
var allow = true;
function do_prize(){
	if(!allow) return;
	allow = !allow;
	var code = $('.code').val();
	if(vmid == ''){
		show_msg('未设定机器编号，请联系管理员！',3000);
		return;
	}
	parms = {
		'a':'<?=ACTION?>',
		'm':'ajax_prize_code',
		'vmid':vmid,
		'code':code
	};
	$.get('index.php',parms,function(res){
		allow = true;
		show_msg(res,4500);
	});
}

//弹窗提示
function show_msg(str,t){
	str = '<div>'+str+'</div>';
	$('#msg').html(str).show();
	setTimeout(function(){$('#msg').hide();},t);
}

//取得机器编号
function get_vmid(){
	if(vmid == ''){
		var socket = new WebSocket('ws://localhost:1234');
		// 打开Socket 
		socket.onopen = function(event){ 
			// 发送一个初始化消息
			socket.send('vmid'); 
			// 监听消息
			socket.onmessage = function(data){
				socket.close();
				vmid = data.data;
			};
			// 监听Socket的关闭
			socket.onclose = function(event){}; 
		};
	}
 }

</script>
 </head>
 <body>
  <div id="msg"></div>
  <div class="num_area">
	<div class="block"><div>1</div></div>
	<div class="block"><div>2</div></div>
	<div class="block"><div>3</div></div>
	<div class="block"><div>4</div></div>
	<div class="block"><div>5</div></div>
	<div class="block"><div>6</div></div>
	<div class="block"><div>7</div></div>
	<div class="block"><div>8</div></div>
	<div class="block"><div>9</div></div>
	<div class="block"><div>0</div></div>
	<div class="block"><div class="num_c">清空</div></div>
	<div class="block"><div class="num_del">Del</div></div>
  </div>
  <div class="in_area">
	<div style="width:66%;float:left;">
		<input class="code" type="text" placeholder="请输入验证码"/>
	</div>
	<div style="width:33%;float:right;">
	<button class="btn" onclick="do_prize()">确定</button>
	</div>
  </div>
  <div class="cent">
	<button class="back" onclick="history.back(-1)">返回 (Go Back)</button>
 </div>
 </body>
</html>