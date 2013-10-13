
<style type="text/css"> 
       @import url("/img/loginskin/login_page.css");
</style>

<div class="content">
<div class="logo"><img src="/img/loginskin/hypervm.png"></div>
      <div class="login">
              <form name=sendmail action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <div class="inputfield"><table><tr><td><img
                    src="/img/loginskin/user_icon.png" height="20" /></td><td><input name="frm_clientname" type="text" class="inputbox" size=30 placeholder="Username" /></td></tr></table></div>
                    <div class="inputfield"><table><tr><td><img
                    src="/img/loginskin/mail_icon.png" height="20" /></td><td><input name="frm_email" type="text" class="inputbox" size=30 placeholder="Email Address" /></td></tr></table></div><br>
                  <div class="central"><input type="submit" name="login" class="button_wide" value="Reset Password" /></div>
                    <input type="hidden" name="frm_forgotpwd" value="2">
            </form>
          <div class="forgotpassword">
              <a class="forgotpwd" href="javascript:history.go(-1);"><font color="black">Back to Login</a>
        </div>
              <script> document.sendmail.frm_clientname.focus(); </script>
      </div>
</div>