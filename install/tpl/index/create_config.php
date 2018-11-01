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
	<div style="width:500px;margin:auto;">
		<form action="" method="post">
		<div style="margin-top:30px;border:solid 1px #0629f2;line-height:22px;background:#efefef">
			<div style="padding-left:5px;background:#1351f2;color:#ffffff;line-height:28px;"><strong>系统配置</strong></div>
			<table>
				<tr><td align="right">数据库主机地址：</td><td><input type="text" name="db_host" value="localhost:3306"/></td></tr>
				<tr><td align="right">数据库用户名：</td><td><input type="text" name="db_user" value=""/></td></tr>
				<tr><td align="right">数据库用密码：</td><td><input type="text" name="db_pwd" value=""/></td></tr>
				<tr><td align="right">数据库名称：</td><td><input type="text" name="database" value="autosale"/></td></tr>
				<tr><td align="right">数据表前缀：</td><td><input type="text" name="db_pre" value="t_vm_"/></td></tr>
			</table>
			<input type="hidden"  name="a" value="<?=ACTION?>"/>
			<input type="hidden"  name="m" value="<?=METHOD?>"/>
		</div>
		<div style="text-align:center;margin:10px;"><input type="submit" style="background:#0629f2;border-radius:6px;color:#FFFFFF;font-weight:bold;padding:4px 15px" value="下一步"/></div>
		</form>
	</div>
 </body>
</html>
