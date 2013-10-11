
<style type="text/css"> 
       @import url("/img/loginskin/login_page.css");
</style>

<div class="content">
<div class="logo"><img src="/img/loginskin/hypervm.png"></div>
      <div class="login">
          <div class="loginform">
              <form name=sendmail action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                  <div class="inputfield">

                    <table>
                    <tr>
                      <td>
                        <img src="/img/loginskin/user_icon.png" height="20" />
                      </td>
                      <td>
                        <input name="frm_clientname" type="text" class="inputbox" size=30 placeholder="Username" />
                      </td>
                    </tr>
                    </table>
                    <table>
                    <tr>
                      <td>
                        <img src="/img/loginskin/mail_icon.png" height="20" />
                      </td>
                      <td>
                        <input name="frm_email" type="text" class="inputbox" size=30 placeholder="Email Address" />
                      </td>
                    </tr>
                    </table>

                  </div>
                  <div class="central"><input type="submit" name="login" class="button" value="Reset Password" /></div>
          </div>
          <div class="forgotpasswd">
              <a class="forgotpwd" href="javascript:history.go(-1);">Back to Login</a>

              <input type="hidden" name="frm_forgotpwd" value="2">
              </form>
              <script> document.sendmail.frm_clientname.focus(); </script>
          </div>
      </div>
  </div>
  <div id="break"></div>
</div>