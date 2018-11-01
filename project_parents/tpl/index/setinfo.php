<?php $this->tpl('header','index'); ?>
<body>
<?php $this->tpl('bar','index'); ?>

<div class="panel panel-primary">
 <div class="panel-heading">校园卡[<?=$VAL['item']['cardid']?>]设置</div>
 <div class="panel-body">
    <form class="form-horizontal" role="form" method="post">
	<div class="form-group">
    <label class="col-sm-2 control-label">持卡人</label>
    <div class="col-sm-10">
      <p class="form-control-static"><?=$VAL['item']['username']?></p>
    </div>
  </div>
  <div class="form-group">
    <label for="mobile" class="col-sm-2 control-label">联系电话</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" value="<?=$VAL['item']['mobile']?>" id="mobile" name="mobile" placeholder="联系电话">
    </div>
  </div>
   <div class="form-group">
    <label for="day_limit" class="col-sm-2 control-label">日限额(0表示不限制)</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" value="<?=round($VAL['item']['day_limit']/100,2)?>" id="day_limit" name="day_limit" placeholder="日限额(0表示不限制)">
    </div>
  </div>
  <div class="form-group">
    <label for="day_limit" class="col-sm-2 control-label">卡片状态</label>
    <div class="col-sm-10">
      <label class="radio-inline">
		<input type="radio" name="status" id="status1" <?php if(!$VAL['item']['status'])echo 'checked';?> value="0"> 锁定(挂失)
	  </label>
	  <label class="radio-inline">
		<input type="radio" name="status" id="status2" <?php if($VAL['item']['status'])echo 'checked';?> value="1"> 正常
	  </label>
    </div>
  </div>
  <div class="form-group">
    <label for="password" class="col-sm-2 control-label">卡片密码</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="password" name="password" value="<?=$VAL['item']['password']?>" placeholder="不填表示不需要密码就可以消费"/>
    </div>
  </div>
  <div class="form-group">
    <label for="day_limit" class="col-sm-2 control-label">允许购买的商品</label>
    <div class="col-sm-10">
      <?php 
		$type = 0;
		$allows = empty($VAL['item']['goods_allow']) ? array() : explode(',',$VAL['item']['goods_allow']);
		foreach($VAL['goods'] as $g){ 
			if($type != $g['goods_type']){
				if($type) echo '<br/><br/>';
				$type = $g['goods_type'];
			}
	  ?>
	  <label class="checkbox-inline"><input type="checkbox" name="goods_allow[]" <?php if(in_array($g['id'],$allows)) echo 'checked';?> value="<?=$g['id']?>"><?=$g['goods_name']?></label>
	  <?php }?>
    </div>
  </div>
  <div class="form-group">
    <label for="day_limit" class="col-sm-2 control-label">禁止购买的商品</label>
    <div class="col-sm-10">
      <?php 
		$type = 0;
		$allows = empty($VAL['item']['goods_fobid']) ? array() : explode(',',$VAL['item']['goods_fobid']);
		foreach($VAL['goods'] as $g){ 
			if($type != $g['goods_type']){
				if($type) echo '<br/><br/>';
				$type = $g['goods_type'];
			}
	  ?>
	  <label class="checkbox-inline"><input type="checkbox" name="goods_fobid[]" <?php if(in_array($g['id'],$allows)) echo 'checked';?> value="<?=$g['id']?>"><?=$g['goods_name']?></label>
	  <?php }?>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10 bg-warning">
	 注意：如果设置了允许购买的商品，则以允许购买的商品为准；否则以限制购买的商品为准
    </div>
  </div>
   <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10 text-center">
      <button type="submit" class="btn btn-primary">提 交</button>
    </div>
  </div>
  <input type="hidden" name="cardid" value="<?=$VAL['item']['cardid']?>"/>
  <input type="hidden" name="a" value="<?=ACTION?>"/>
  <input type="hidden" name="m" value="<?=METHOD?>"/>
</form>
  </div>
</div>
<br/><br/><br/>
</body>
</html>