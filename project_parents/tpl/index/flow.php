 <?php $this->tpl('header','index'); ?>
 <body>
 <?php $this->tpl('bar','index'); ?>
 <div class="panel panel-primary">
 <div class="panel-heading">资金流水</div>
 <div class="panel-body">
  <table class="table table-striped">
	<tr>
		<th>时间</th>
		<th>变动金额</th>
		<th>变动后金额</th>
		<th>备注</th>
	</tr>
<?php foreach($VAL['list'] as $em){ ?>
<tr>
	<td><?=date('Y-m-d',$em['createtime'])?></td>
	<td><?=$em['change']/100?></td>
	<td><?=$em['after']/100?></td>
	<td><?=$em['note']?></td>
</tr>
<?php } ?>
  </table>
  <?=$VAL['str_page']?>
  </div>
 </div>
<br/><br/><br/>
 </body>
</html>