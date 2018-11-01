<!doctype html>
<html lang="en">
 <head>
  <meta charset="UTF-8">
  <meta name="Generator" content="EditPlus®">
  <meta name="Author" content="">
  <meta name="Keywords" content="">
  <meta name="Description" content="">
  <title>微信二维码生成</title>
<style type="text/css">
#road_map{width:400px;margin:auto;}
#image_div{margin:auto;width:780px;}
.item{width:155px;float:left;text-align:center;}
.item image{width:150px;}
table{width:100%;}
td{text-align:center;border:solid 1px #ccc;width:10%;height:25px;}
</style>
<script type="text/javascript" src="<?=SOURCE_ROOT?>jquery.js"/></script>
<script type="text/javascript">
var vmid = '';
var pacode = '';
function create_one(){
	vmid = $('#vmid').val();
	pacode = $('#pacode').val();
	if(vmid == '' || pacode == ''){
		alert('请填写机器编号和货到编号！');
		return;
	}
	$('#image_div').html('');
	create_image(pacode);
}
function create_all(){
	vmid = $('#vmid').val();
	pacode = $('#pacode').val();
	$('#image_div').html('<br/><br/><font color="red">请稍后，系统正在处理...</font>');
	$('#road_map').html('');
	$.get('online_pay.php?a=<?=ACTION?>&m=<?=METHOD?>&api=pacodes&vmid=' + vmid,function(res){
		$('#image_div').html('');
		var codes = res.split('|');
		if(codes.length < 2) alert('没有查询到相关货道！');
		else{
			var str = '<table>';
			str += '<tr><td colspan="10">'+vmid+'货道分布图</td></tr>';
			var current = parseInt(parseInt(codes[0]) / 10)*10;
			for(var i = 0; i < codes.length - 1;i++){
				while(codes[i] >= current){
					if(current % 10 == 0) str += '<tr>';
					if(codes[i] == current) str += '<td>' + current + '</td>';
					else  str += '<td></td>';
					if(current % 10 == 9) str += '</tr>';
					current += 1;
				}
			}
			var max = parseInt(parseInt(codes[codes.length - 2]) / 10)*10 + 9;
			while(max >= current){
					if(current % 10 == 0) str += '<tr>';
					if(codes[i] == current) str += '<td>' + current + '</td>';
					else  str += '<td></td>';
					if(current % 10 == 9) str += '</tr>';
					current += 1;
			}
			str += '</table>';
			$('#road_map').html(str);
			for(var i = 0; i < codes.length - 1;i++) create_image(codes[i]);
		}
	});
}

function create_image(code){
	$.ajax({
		url:'online_pay.php?a=<?=ACTION?>&m=<?=METHOD?>&api=1&vmid='+vmid+'&pacode=' + code,
		async:false,
		success:function(s){
			var str = '<div class="item">';
			str += '<img alt="扫码支付:' + s + '" title="扫码支付:' + s + '" src="http://paysdk.weixin.qq.com/example/qrcode.php?data=' + encodeURIComponent(s) + '"/>';
			str += '<div>货道：' + code + '</div>';
			str += '</div>';
			$('#image_div').append(str);
		}
	});
}
</script>
 </head>
 <body>
	<center>
	机器编号：<input type="text" id="vmid" value="<?=$_REQUEST['vmid']?>"/>
	货道编号：<input type="text" id="pacode" value="<?=$_REQUEST['pacode']?>"/>
	<input type="button" value="生成本货道二维码" onclick="create_one()"/>
	<input type="button" value="生成机器所有二维码" onclick="create_all()"/>
	</center>
	<div id="road_map"></div>
	<div id="image_div">
<?php if($VAL['url']){ ?>
		<div class="item">
			<img alt="扫码支付:<?=$VAL['url']?>" title="扫码支付:<?=$VAL['url']?>" src="/api/weixin/example/qrcode.php?data=<?=urlencode($VAL['url'])?>"/>
			<div>货道：<?=$_REQUEST['pacode']?></div>
		</div>
<?php }?>
	</div>
 </body>
</html>