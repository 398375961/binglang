<!doctype html>
<html lang="en">
 <head>
  <meta charset="UTF-8">
  <title>商品轮播</title>
<style>
body{background-image:url('<?=SOURCE_ROOT?>api/bg/bg2.jpg');background-color:#cdcdcd;padding:0px;margin:0px;}
.container{width:100%;height:100%;overflow:hidden;margin-top:5px;}
.list{width:10000px;height:100%;}
.cel{float:left;}
.img{width:100%;text-align:center;}
.img img{width:90%;height:100%}
.info{width:100%;margin-top:5px;}
.info div{margin:0px 15px; padding:5px 15px;text-align:center;background:#009eea;line-height:25px;color:#FFFFFF;font-size:17px;font-weight:bold;border-radius:8px;}
a{text-decoration:none;}
.info div:hover{background:red;}
</style>
 <script type="text/javascript" src="<?=SOURCE_ROOT?>jquery.js"></script>
 <script type="text/javascript">
var cols = <?=$VAL['cols']?>;
var margin_left = 0;
var cursor = false;
var max = 0;
 $(document).ready(function(){
	var screen_w = $(window).width();
	var screen_h = $(window).height();
	$('.cel').css({'width':screen_w/cols,height:screen_h - 10});
	$('.img').css({'height':screen_h - 70});
	max = screen_w/cols;
	max = max * <?=count($VAL['goods'])?>;
	var html = $('.list').html();
	$('.list').html(html + html);
	$('.list').mouseover(function(){
		cursor = true;
	}).mouseout(function(){
		cursor = false;
	});
	var speed = 30 - <?=$VAL['speed']?>*3;
	if(speed < 5) speed = 5;
	setInterval(scroll,speed);
 });

function scroll(){
	if(cursor) return;
	margin_left += 1;
	if(margin_left >= max) margin_left = 0;
	$('.list').css({'margin-left':-margin_left});
}
 </script>
 </head>
<body>
 <div class="container">
 <div class="list">
 <?php foreach($VAL['goods'] as $g){ ?>
	<div class="cel">
		<a href="api.php?a=<?=ACTION?>&m=buy&gid=<?=$g['id']?>&vmid=<?=$VAL['vmid']?>" target="_top">
		<div class="img"><img src="<?php echo SOURCE_ROOT.$g['goods_pic'];?>"/></div>
		<div class="info">
			<div><?=$g['goods_name']?><br/>
			￥<?=number_format($g['goods_price']/100,2)?>元</div>
		</div>
		</a>
	</div>
 <?php }?>
 </div>
 </div>
</body>
</html>