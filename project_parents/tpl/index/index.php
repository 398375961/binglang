<?php $this->tpl('header','index'); ?>
<body>
<?php $this->tpl('bar','index'); ?>
<div class="panel panel-primary">
 <div class="panel-heading">校园卡[<?=$_REQUEST['cardid']?>]消费记录</div>
 <div class="panel-body">
	<ul class="list-group">
	<?php foreach($VAL['list'] as $item){ ?>
	   <li class="list-group-item">
			<h4 class="list-group-item-heading"><?=date('Y-m-d H:i:s',$item['saletime'])?>购买【<?=$item['goods_name']?>】价格：<font color="red"><?=$item['salemoney']/100?></font>元</h4>
	  </li>
	<?php }?>
	</ul>
	<?=$VAL['str_page']?>
</div>
</div>
<br/><br/><br/>
</body>
</html>