
<style type="text/css"> 
       @import url("/img/loginskin/login_page.css");
</style>

<div class="content">
<div class="logo"><img src="/img/loginskin/hypervm.png"></div>
      <div class="login">
          <div class="loginform">
              <div align="center" class="LoginScreenTextHeader">Forgot Password</div>
              <br>

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
                        <img src="/img/loginskin/user_icon.png" height="20" />
                      </td>
                      <td>
                        <input name="frm_email" type="text" class="inputbox" size=30 placeholder="Email Id" />
                      </td>
                    </tr>
                    </table>

                  </div>
                  <div class="central"><input type="submit" name="login" class="button" value="Send" /></div>
          </div>
          <div class="login-text">
              <div class="ctr"><img src="/img/login/icon1.gif" width="64" height="64" style="float:left;margin-right: 5px;" alt="security"/></div>
              <p><h3>Welcome to <?php echo  $sgbl->__var_program_name?> </h3></p>

              <p>Use a valid username and email-id to get password.</p>
              <a class="forgotpwd" href="javascript:history.go(-1);">Back to login</a>

              <input type="hidden" name="frm_forgotpwd" value="2">
              </form>
              <script> document.sendmail.frm_clientname.focus(); </script>

          </div>
          <div class="clr"></div>
      </div>
  </div>
  <div id="break"></div>
</div>