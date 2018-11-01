<!doctype html>
<html lang="en">
 <head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script type="text/javascript" src="<?=SOURCE_ROOT?>admin/js/jquery.js"></script>
</head>
<body style="margin:0;padding:0;">
<div style="text-align:center;">
	<br/><br/>
</div>
<div id="div_info" style="width:80%;margin:auto;padding:1em;border:solid 1px #1c8110;border-radius:0.5em;background:#0f8a3e">
   <table style="width:100%"><tr><td align="right" width="20%">验证码:</td>
	<td><input type="text" id="code" value="" style="border:solid 1px;font-weight:bold;height:3em;line-height:3em;width:100%;text-align:center;" placeholder="输入验证码，马上领奖"/>
	</td></tr>
	<tr><td colspan="2" align="center">
	<br/>
	<input type="button" onclick="send()" style="border:0px;border-radius:1em;background:#fb2434;width:100%;height:3em;line-height:3em;font-size:1em;font-weight:bold;color:#FFFFFF" value="发送验证码"/>
	</td></tr>
	</table>
</div>
<div style="text-align:center;"></div>
 </body>
</html>
<script type="text/javascript">
var url = 'qr_prize.php';
var is_sending = false;
function send(){
	var code = $('#code').val();
	if(code == ''){
		alert('请输入在机器上看到的验证码');
		return;
	}
	if(is_sending) return;
	is_sending = true;
	var wx = (typeof WeixinJSBridge == "undefined") ? 0 : 1;
	$.post(url,{'code':code,'weichat':'<?=$_REQUEST[weichat]?>','wx':wx},function(s){
		is_sending = false;
		alert(s);
	});
}
function onBridgeReady(){
	WeixinJSBridge.call('hideOptionMenu');
}
if(typeof WeixinJSBridge == "undefined"){
    if(document.addEventListener){
        document.addEventListener('WeixinJSBridgeReady',onBridgeReady, false);
    }else if (document.attachEvent){
        document.attachEvent('WeixinJSBridgeReady',onBridgeReady); 
        document.attachEvent('onWeixinJSBridgeReady',onBridgeReady);
    }
}else{
    onBridgeReady();
}
</script>