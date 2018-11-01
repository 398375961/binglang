<!doctype html>
<html lang="en">
 <head>
  <meta charset="UTF-8">
  <meta name="Author" content="">
  <meta name="Keywords" content="">
  <meta name="Description" content="">
  <title>广告预览</title>
 </head>
 <body style="padding:0px;margin:0px;">
<?php foreach((array)$VAL['item']['materials'] as $m){ 
	if($m['type'] == 1){
?>
<div style="position:absolute;top:<?=$m['top']?>px;left:<?=$m['left']?>px;width:<?=$m['width']?>px;height:<?=$m['height']?>px;z-index:<?=$m['order_no']?>;">
<video src="public/<?=$m['url']?>" <?php if($_POST['is_view']){?>controls="controls"<?php }else{ ?>loop="loop" autoplay="autoplay"<?php }?> width="<?=$m['width']?>" height="<?=$m['height']?>">您的浏览器不支持 video 标签。</video>
</div>
<?php }elseif($m['type'] == 2){ ?>
<audio src="public/<?=$VAL['item']['url']?>" autoplay="autoplay" loop="loop">您的浏览器不支持 audio 标签。</audio>
<?php }else{ ?>
<div style="position:absolute;top:<?=$m['top']?>px;left:<?=$m['left']?>px;width:<?=$m['width']?>px;height:<?=$m['height']?>px;z-index:<?=$m['order_no']?>">
<?php if(!empty($m['link'])){ ?><a href="<?php if(is_numeric($m['link'])) echo 'api.php?a=ads&m=ads_view&id='; echo $m['link'];?>"><?php }?>
<img src="public/<?=$m['url']?>" width="<?=$m['width']?>" height="<?=$m['height']?>" />
<?php if(!empty($m['link'])) echo '</a>';?>
</div>
<?php	
	}
}?>
</body></html>