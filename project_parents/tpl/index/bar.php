<nav class="navbar navbar-default navbar-fixed-bottom" role="navigation">
  <div class="container">
    <ul class="nav nav-pills" role="tablist">
	  <li role="presentation" <?php if(METHOD == 'index') echo 'class="active"';?>><a href="?m=index">校园卡管理</a></li>
	  <li role="presentation" <?php if(METHOD == 'pwd') echo 'class="active"';?>><a href="?m=pwd">修改密码</a></li>
	  <li role="presentation"><a href="?m=logout">安全退出</a></li>
	</ul>
  </div>
</nav>