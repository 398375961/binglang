<?php $this->tpl('header','index'); ?>
<body>
<?php $this->tpl('bar','index'); ?>
 <div class="panel panel-primary">
 <div class="panel-heading">修改密码</div>
 <div class="panel-body">
<form class="form-horizontal" role="form" method="post">
  <div class="form-group">
    <label for="mobile" class="col-sm-2 control-label">当前密码</label>
    <div class="col-sm-10">
      <input type="password" class="form-control" value="" name="password" placeholder="当前密码" required>
    </div>
  </div>
  <div class="form-group">
    <label for="mobile" class="col-sm-2 control-label">新的密码</label>
    <div class="col-sm-10">
      <input type="password" class="form-control" value="" name="newpwd" placeholder="新的密码" required>
    </div>
  </div>
  <div class="form-group">
    <label for="mobile" class="col-sm-2 control-label">确认密码</label>
    <div class="col-sm-10">
      <input type="password" class="form-control" value="" name="newpwd2" placeholder="确认密码" required>
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