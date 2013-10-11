<style type="text/css">
        @import url("/img/loginskin/login_page.css");
</style>
<div class="content">
<div class="logo"><img src="/img/loginskin/hypervm.png"></div>
  <div class="login">
          <form name="loginform" action="/htmllib/phplib/" onSubmit="encode_url(loginform) ; return fieldcheck(this);" method="post">
                  <div class="inputfield"><table><tr><td><img
          src="/img/loginskin/user_icon.png" height="20" /></td><td><input name="frm_clientname" type="text" class="inputbox" size=30 placeholder="Username" /></td></tr></table></div>
                  <div class="inputfield"><table><tr><td><img
          src="/img/loginskin/password_icon.png" height="20" /></td><td><input name="frm_password" type="password" class="inputbox" size=30 placeholder="Password" /></td></tr></table></div><br>
                  <input type=hidden name=id value="<?php echo mt_rand() ?>">
                  <div class="central"><input type="submit" name="login" class="button" value="Login" /></div>
          </form>
  <div class="forgotpassword"><a class="forgotpwd"  href="javascript:document.forgotpassword.submit()"><font color="black">Forgot Password?</a>
          <form name="forgotpassword" method="post"  action="/login/">
                  <input type="hidden" name="frm_forgotpwd" value="1">
          </form>
  </div>
          <script> document.loginform.frm_clientname.focus(); </script>
  </div>
</div>
