<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style type="text/css">
body{font-size:13px;}
</style>
</head>
<body>
<script type="text/javascript">
<?php if($seconds > 0){ ?>
  var sec = <?=$seconds?>;
  var t = setInterval(function(){
	sec = sec - 1;	
	if(sec < 1){
		clearInterval(t);
		return;
	}
	document.getElementById('seconds').innerHTML = sec;
  },1000);
  setTimeout(function(){
	window.location.href = "<?=$url?>";
  },<?=$seconds?>000);
<?php }else{ ?>
	window.location.href = "<?=$url?>";
<?php }?>
</script>
<div style="padding-top:10px;text-align:left;line-height:25px;">
	<table align="center" width="400" bgcolor="#3399ff" cellpadding="1" cellspacing="1">
		<tr bgcolor="#e1f0fd"><td width="95" align="center">提示信息：</td><td><strong style="color:red;"><?=$msg?></strong></td></tr>
		<?php if($seconds > 0){ ?>
		<tr bgcolor="#e1f0fd"><td align="center">自动跳转：</td><td><span id="seconds"><?=$seconds?></span>秒后自动跳转！</td></tr>
		<?php } ?>
		<tr bgcolor="#e1f0fd"><td colspan="2" align="center"><a href="<?=$url?>">立即跳转</a></td>
	</table>
</div>
</body>
</html>