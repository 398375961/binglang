<?php $this->tpl('header','index'); ?>
<body>
<?php $this->tpl('bar','index');?>

<div class="panel panel-primary">
 <div class="panel-heading">绑定的校园卡【<?=count($VAL['list'])?>】</div>
 <div class="panel-body">
	<ul class="list-group">
<?php foreach((array)$VAL['list'] as $item){ ?>
   <li class="list-group-item">
		<h4 class="list-group-item-heading"><?=$item['cardid']?></h4>
		<div class="container">
		<table class="table table-bordered">
			<tr>
				<td class="text-right">学生证：</td>
				<td><?=$item['studentid']?></td>
				<td class="text-right">余  额：</td>
				<td><?=$item['account']/100?></td>
			</tr>
			<tr>
				<td class="text-right">状 态：</td>
				<td><?php echo $item['status'] ? '正常':'已锁定';?></td>
				<td class="text-right">持卡人：</td>
				<td><?=$item['username']?></td>
			</tr>
			<tr>
				<td class="text-right">日限额：</td>
				<td><?php if($item['day_limit']) echo sprintf('%.2f元',$item['day_limit']/100); else echo '无限制';?></td>
				<td colspan="2" class="text-center"><div class="btn-group">
						<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
						  操作
						  <span class="caret"></span>
						</button>
						<ul class="dropdown-menu" role="menu">
						  <li><a href="?m=index&cardid=<?=$item['cardid']?>">消费记录</a></li>
						  <li><a href="?m=flow&cardid=<?=$item['cardid']?>">资金流水</a></li>
						  <li><a href="?m=setinfo&cardid=<?=$item['cardid']?>">卡片设置</a></li>
						</ul>
					</div></td>
			</tr>
		</table>
		</div>
  </li>
<?php }?>
</ul>
 </div>
</div>
<br/><br/><br/>
</body>
</html>