<!doctype html>
<html lang="en">
 <head>
  <meta charset="UTF-8">
  <meta name="Generator" content="EditPlus®">
  <meta name="Author" content="">
  <meta name="Keywords" content="">
  <meta name="Description" content="">
  <script type="text/javascript" src="<?=SOURCE_ROOT?>jquery.js"></script>
  <script type="text/javascript" src="<?=SOURCE_ROOT?>js.js"></script>
  <title>系统安装</title>
 </head>
 <body>
	<div style="width:600px;margin:auto;">
		<div style="margin-top:30px;border:solid 1px #0629f2;line-height:22px;background:#efefef">
			<div style="padding-left:5px;background:#1351f2;color:#ffffff;line-height:28px;"><strong>文件读写权限检测</strong></div>
			<table border="0" width="100%">
				<?php foreach($VAL['filewrite'] as $f){ ?>
				<tr><td><?=$f[0]?></td><td><img src="<?php echo SOURCE_ROOT; echo $f[1] ? 'allow.gif" title="可写':'b_drop.png" title="不可写'; ?>"/></td></tr>
				<?php } ?>
			</table>
		</div>
		<div style="text-align:center;margin:10px;"><input type="button" style="background:#0629f2;border-radius:6px;color:#FFFFFF;font-weight:bold;padding:4px 15px" value="安装" id="btn_install" onclick="location.href='?m=create_config'"/></div>
	</div>
 </body>
</html>
