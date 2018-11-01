 <?php
 $title = '登录';
 $this->tpl('header','index');
 ?>
 <style>
 body {
  padding-top: 40px;
  padding-bottom: 40px;
  background-color: #eee;
}

.form-signin {
  max-width: 330px;
  padding: 15px;
  margin: 0 auto;
}
.form-signin .form-signin-heading,
.form-signin .checkbox {
  margin-bottom: 10px;
}
.form-signin .checkbox {
  font-weight: normal;
}
.form-signin .form-control {
  position: relative;
  height: auto;
  -webkit-box-sizing: border-box;
     -moz-box-sizing: border-box;
          box-sizing: border-box;
  padding: 10px;
  font-size: 16px;
}
.form-signin .form-control:focus {
  z-index: 2;
}
.form-signin input[type="email"] {
  margin-bottom: -1px;
  border-bottom-right-radius: 0;
  border-bottom-left-radius: 0;
}
.form-signin input[type="password"] {
  margin-bottom: 10px;
  border-top-left-radius: 0;
  border-top-right-radius: 0;
}
 </style>
 <body>
  <div class="container">
      <form class="form-signin" role="form" method="post">
        <h2 class="form-signin-heading">请登录</h2>
        <input type="text" name="username" class="form-control" placeholder="登录账号" required autofocus>
        <input type="password" name="password" class="form-control" placeholder="登录密码" required>
		<!--
        <div class="checkbox">
          <label>
            <input type="checkbox" value="remember-me"> Remember me
          </label>
        </div>-->
        <button class="btn btn-lg btn-primary btn-block" type="submit">登录</button>
		<input type="hidden" name="a" value="<?=ACTION?>"/>
		<input type="hidden" name="m" value="<?=METHOD?>"/>
      </form>
    </div>
 </body>
</html>