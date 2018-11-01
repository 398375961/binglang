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
			<div style="padding-left:5px;background:#1351f2;color:#ffffff;line-height:28px;"><strong>创建数据库，表结构</strong></div>
			<div id="result" style="padding-left:5px;height:400px;overflow:scroll;overflow-x:hidden;"></div>
		</div>
	</div>
 </body>
 <script type="text/javascript">
var tables = new Array();
<?php foreach($VAL['tables'] as $t){ ?>tables.push('<?=$t?>');<?php } ?>
function create_table(){
	var t = tables.pop();
	if(t){
		$.get('?m=create_table&ajax=1&table=' + t,function(str){
			$('#result').append(str).scrollTop(500);
			create_table();
		});
	}else{
		//所有表创建完毕
		location.href = '?m=over';
	}
}
$(document).ready(function(){
	$.get('?m=create_db',function(str){
		$('#result').append(str);
		create_table();
	});
});
 </script>
</html>