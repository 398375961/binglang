<!doctype html>
<html lang="en">
 <head>
<meta charset="UTF-8">
<meta name="viewport" content="user-scalable=no, initial-scale=1.0, maximum-scale=1.0, width=device-width">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta content="black" name="apple-mobile-web-app-status-bar-style">
<meta name="format-detection" content="telephone=no">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<script type="text/javascript" src="<?=SOURCE_ROOT?>jquery.js"></script>
<script type="text/javascript" src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
  <title><?=$VAL['article']['title']?></title>
 <style type="text/css">
 body{padding:0px 5px;font-size:1.2em;}
 img{max-width:100%;}
 #_con_{margin:auto;max-width:640px;}
 </style>
 </head>
 <body>
	<div id="_con_"><?=$VAL['article']['content']?></div>
 </body>
<?php if($_REQUEST['key']){ ?>
 <script type="text/javascript">
wx.config({
    debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
    appId: "<?=$VAL['weixin_mp']['appid']?>", // 必填，公众号的唯一标识
    timestamp: <?=NOW_TIME?>, // 必填，生成签名的时间戳
    nonceStr: "<?=$VAL['weixin_mp']['noncestr']?>", // 必填，生成签名的随机串
    signature: "<?=$VAL['weixin_mp']['signature']?>",// 必填，签名，见附录1
    jsApiList: ['onMenuShareTimeline','onMenuShareAppMessage'] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
});
wx.ready(function(){
    // config信息验证后会执行ready方法，所有接口调用都必须在config接口获得结果之后，config是一个客户端的异步操作，所以如果需要在页面加载时就调用相关接口，则须把相关接口放在ready函数中调用来确保正确执行。对于用户触发时才调用的接口，则可以直接调用，不需要放在ready函数中。
	
	//分享到朋友圈
	wx.onMenuShareTimeline({
		title: "<?=$VAL['article']['title']?>", // 分享标题
		link: "<?=$VAL['article']['link']?>", // 分享链接
		imgUrl: "<?=$VAL['article']['pic']?>", // 分享图标
		success: function () { 
			// 用户确认分享后执行的回调函数
			var parms = {
				'a':'news',
				'm':'shared',
				'aid':"<?=$VAL['article']['id']?>",
				'scene_id':"<?=$_REQUEST['scene_id']?>",
				'key':"<?=$_REQUEST['key']?>",
				'client':"<?=$_REQUEST['client']?>"
			};
			$.post('index.php',parms,function(res){
				alert(res);
			});
		},
		cancel: function (){ 
			// 用户取消分享后执行的回调函数
			//alert('取消分享');
<?php if($VAL['article']['is_test']){ ?>
			var parms = {
				'a':'news',
				'm':'shared',
				'aid':"<?=$VAL['article']['id']?>",
				'scene_id':"<?=$_REQUEST['scene_id']?>",
				'key':"<?=$_REQUEST['key']?>",
				'client':"<?=$_REQUEST['client']?>"
			};
			$.post('index.php',parms,function(res){
				alert(res);
			});
<?php } ?>
		}
	});
/*
	//分享给朋友
	wx.onMenuShareAppMessage({
		title: "<?=$VAL['article']['title']?>", // 分享标题
		desc: 'ceshi', // 分享描述
		link: "<?=$VAL['article']['link']?>", // 分享链接
		imgUrl: "<?=$VAL['article']['pic']?>", // 分享图标
		type: 'link', // 分享类型,music、video或link，不填默认为link
		dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
		success: function () { 
			// 用户确认分享后执行的回调函数
			alert('分享成功了');
		},
		cancel: function () { 
			// 用户取消分享后执行的回调函数
			alert('取消分享');
		}
	});
*/
});
wx.error(function(res){
    // config信息验证失败会执行error函数，如签名过期导致验证失败，具体错误信息可以打开config的debug模式查看，也可以在返回的res参数中查看，对于SPA可以在这里更新签名。
	//alert(res);
});
 </script>
<?php }?>
</html>
