<!doctype html>
<html lang="en">
 <head>
  <meta charset="UTF-8">
  <title>贩卖</title>
<style>
body{background-image:url('<?=SOURCE_ROOT?>api/bg/bg2.jpg') ;padding:0px;margin:0px;}
.cent{text-align:center;padding:5px 0px;}
#qr_code{position:absolute;z-index:100;line-height:200px;text-align:center;border:solid 1px red;background:#ffffff;display:none;}
#msg{position:fixed;z-index:1000;width:100%;top:100px;}
#msg div{margin:auto;width:350px;border:solid 2px #0069d2;background:#ffd6ac;padding:30px 15px;color:red;line-height:40px;font-weight:bold;}
.back{width:60%;cursor:pointer;margin-top:15px;color:#FFFFFF;background:#006fdd;
padding:15px 45px;font-weight:bold;font-size:24px;border-radius: 8px;border:solid 1px #ccc;}
.back:hover{background:#ff0000;}
.tab_info td{padding:0px 15px;line-height:30px;color:#FFFFFF;font-size:17px;font-weight:bold;}
</style>
 <script type="text/javascript" src="<?=SOURCE_ROOT?>jquery.js"></script>
 <script type="text/javascript">
 var vmid = '';
 var max_id = '<?=$VAL[max_order_id]?>';
 var allow_query = false;
 $(document).ready(function(){
	get_vmid(); //获取机器编号
	setInterval(ajax_check_buy,3000);
 });
 
 function ajax_check_buy(){
	if(!allow_query) return;
	if(vmid == '') return;
	var Time = new Date().getTime();
	$.getJSON('api.php',{'a':'ads','m':'ajax_check_buy','vmid':vmid,'max_id':max_id,'t':Time},function(res){
		if(res.ok == '1'){
			allow_query = false;
			max_id = res.id;
			$('#qr_code').hide();
			show_msg('支付成功，机器正在出货...<br/>请从取物口取出商品！',5000);
		}
	});
 }

 function get_vmid(){
	vmid = '<?=$VAL[vmid]?>';
	if(vmid != ''){
		$('#vmid').html(vmid);
		load_machine_goods();
	}else{
		var socket = new WebSocket('ws://localhost:1234');
		// 打开Socket 
		socket.onopen = function(event){ 
			// 发送一个初始化消息
			socket.send('vmid'); 
			// 监听消息
			socket.onmessage = function(data){
				socket.close();
				vmid = data.data;
				$('#vmid').html(vmid);
				load_machine_goods();
			};
			// 监听Socket的关闭
			socket.onclose = function(event){}; 
		};
	}
 }

 //获取商品信息，是否可卖
 function load_machine_goods(){
	if(vmid == '' || vmid.length != 10){
		//错误处理
		show_msg('与服务器失去连接，暂停购物',3500);
	}
	var parms = {
		'a' : 'ads',
		'm' : 'buy',
		'act' : 'ajax_machine_goods',
		'vmid' : vmid,
		'gid' : <?=$VAL['goods']['id']?>
	};
	$.getJSON('api.php',parms,function(res){
		if(res.error == '1'){
			$('#num').html(0);
			$('#price').html((parseFloat(<?=$VAL['goods']['goods_price']?>)/100).toFixed(2));
		}else{
			$('#num').html(res.num);
			$('#price').html((parseFloat(res.price)/100).toFixed(2));
		}
	});
 }

 function show_msg(str,t){
	str = '<div>'+str+'</div>';
	$('#msg').html(str).show();
	setTimeout(function(){$('#msg').hide();},t);
 }

 function pay(type){
	var left = $('#'+type).offset().left;
	var width = parseInt($('#'+type).css('width'));
	var height = parseInt($('#'+type).css('height'));
	var top = $('#'+type).offset().top + height;
	if(vmid == ''){
		//错误处理
		get_vmid();
		return show_msg('与服务器失去连接，请稍后再试.',3500);
	}
	$('#qr_code').html('二维码加载中...').css({'top':top,'left':left,'width':width,'height':width}).show();
	var parms = {
		'a' : 'ads',
		'm' : 'buy',
		'act' : 'ajax_qrcode',
		'pay_type' : type,
		'vmid' : vmid,
		'gid' : <?=$VAL['goods']['id']?>
	};
	$.getJSON('api.php',parms,function(res){
		if(res.error == '1'){
			$('#qr_code').hide();
			show_msg(res.msg,3500);
		}else{
			var url = 'http://paysdk.weixin.qq.com/example/qrcode.php?data=';
			url += res.qrcode;
			$('#qr_code').html('<img src="'+url+'" width="100%"/>');
			allow_query = true;
		}
	});
 }
 </script>
 </head>
<body>
 <div style="height:30px"></div>
 <div class="cent">
	<button class="back" onclick="history.back(-1)">返回 (Go Back)</button>
 </div>
 <div class="cent">
	<table bgcolor="#FFFFFF" class="tab_info" cellpadding="2" cellspacing="2" align="center" width="60%">
		<tr bgcolor="#ff0000"><td width="40%" align="right">机器编号:</td><td align="left"><span id="vmid"></span></td></tr>
		<tr bgcolor="#ff0000"><td align="right">商品名称:</td><td align="left"><?=$VAL['goods']['goods_name']?></td></tr>
		<tr bgcolor="#ff0000"><td align="right">商品价格:</td><td align="left"><span id="price">0.00</span> 元</td></tr>
		<tr bgcolor="#ff0000"><td align="right">可卖数量:</td><td align="left"><span id="num">0</span> <?=$VAL['goods']['goods_guige']?></td></tr>
	</table>
 </div>
  <div class="cent">
  <table width="60%" align="center">
		<tr><td align="left" width="50%"><a href="javascript:void(0)" onclick="pay('weixin')"><img id="weixin" width="300" src="<?=SOURCE_ROOT?>api/weixin_1.jpg"/></a></td><td align="right"><a href="javascript:void(0)" onclick="pay('alipay')"><img id="alipay" width="300" src="<?=SOURCE_ROOT?>api/alipay_1.jpg"/></a></td></tr>
  </table>
 </div>
 <div class="cent">
	<img src="<?php echo SOURCE_ROOT.$VAL['goods']['goods_pic'];?>"/>
 </div>
 <div id="qr_code"></div>
 <div id="msg"></div>
</body>
</html>