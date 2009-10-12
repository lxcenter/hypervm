<?PHP
//
//    HyperVM, Server Virtualization GUI for OpenVZ and Xen
//
//    Copyright (C) 2000-2009     LxLabs
//    Copyright (C) 2009          LxCenter
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU Affero General Public License as
//    published by the Free Software Foundation, either version 3 of the
//    License, or (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU Affero General Public License for more details.
//
//    You should have received a copy of the GNU Affero General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
?>

<?php 
$accountlist = array('client' => "Kloxo Account",'domain' => 'Domain Owner', 'mailaccount' => "Mail Account");

   if(!$cgi_forgotpwd ){
	$ghtml->print_message();


	if (if_demo()) {
		include_once "lib/demologins.php";
	} else {
?>
        <table align=center cellpadding=0 cellspacing=0 border=0 width=314>
		<tr><td><img src="/img/generic-login.gif"></td></tr>
		<tr><td background="/img/login_02.gif">
		<form name=loginform action="/htmllib/phplib/" onsubmit="return fieldcheck(this);" method=get>


		<table cellpadding=2 cellspacing=2 border=0 width=100%>
		<tr><td width=20 height=10 ></td><td ></td></tr>    
		<tr><td width=20></td><td ><font name=Verdana size=2 color=#3992DE><b>Username</b></font></td><td ><input type=text name=frm_clientname size=30 class=logintextbox> </td></tr>  
		<tr><td width=20></td><td ><font name=Verdana size=2 color=#3992DE><b>Password</b></font></td><td ><input type=password name=frm_password size=30 class=logintextbox ></td></tr>  

		<tr><td colspan=3 height=10></td></tr>
		<?php 
		if ($ghtml->iset("frm_nf")) {
			print("<input type=hidden name=frm_nf value=" . $ghtml->frm_nf . ">");
		}
		?> 
		<input type=hidden name=id value="<?php echo mt_rand() ?>"> 
		<tr><td width=20></td><td >  </td><td ></td> </tr>  
		</table>
		<table cellpadding=0 cellspacing=0 border=0 bgcolor=#ddf2fb width=100%>
		<tr><td width=30 ></td><td width=150><a class=forgotpwd  href="javascript:document.forgotpassword.submit()">Forgot Password?</a></td><td align=right ><input name=login type=image src="/img/loginbtn.gif"  onMouseOver="swapImage('login','','/img/loginbtn_1.gif',1);"  onMouseOut="swapImgRestore();" ></td><td width=20></td></tr>
		</table>
		</form>
		</td></tr>
		<tr><td><img src="img/login_03.gif"></td></tr>
		</table>
		<form name="forgotpassword" method="post"  action="/">
		<input type="hidden" name=frm_forgotpwd value="1">
		</form>
         <script> document.loginform.frm_clientname.focus(); </script>

		<?php

	}
		

}
elseif ($cgi_forgotpwd == 1) {
?>
        <table align=center cellpadding=0 cellspacing=0 border=0 width=314>
		<tr><td><img src="/img/forgot_01.gif"></td></tr>
		<tr><td background="/img/forgot_02.gif">
<form name=sendmail action="<?php echo $_SERVER['PHP_SELF']; ?>"  method="post">
		<table cellpadding=2 cellspacing=2 border=0 width=100%>
		<tr><td width=20 height=10 ></td><td ></td></tr>    
		<tr><td width=20></td><td ><font name=Verdana size=2 color=#3992DE><b>Username</b></font></td><td ><input type=text name=frm_clientname size=30 class=forgottextbox> </td></tr>  
		<tr><td width=20></td><td ><font name=Verdana size=2 color=#3992DE><b>Email Id</b></font></td><td ><input type=text name=frm_email size=30 class=forgottextbox ></td></tr>  

		<tr><td colspan=3 height=10></td></tr>
		<tr><td width=20></td><td >  </td><td ></td> </tr>  
		</table>
		<table cellpadding=0 cellspacing=0 border=0 width=100%>
		<tr><td width=30 ></td><td width=150><a class=forgotpwd href="javascript:history.go(-1);">Back to login</a></td><td align=right ><input name=forgot type=image src="/img/forgotbtn.gif"  onMouseOver="swapImage('forgot','','/img/forgotbtn_1.gif',1);"  onMouseOut="swapImgRestore();" ></td><td width=20></td></tr>
		</table>
          <input type="hidden" name="frm_forgotpwd" value="2">    
	</form>
		</td></tr>
		<tr><td><img src="/img/forgot_03.gif"></td></tr>
		</table>



<script> document.sendmail.frm_clientname.focus(); </script>

<?php
} elseif ($cgi_forgotpwd==2) {



	$cgi_clientname = $ghtml->frm_clientname;
	$cgi_email = $ghtml->frm_email;

	Htmllib::checkForScript($cgi_clientname);
		
	$classname = getClassFromName($cgi_clientname);


	/*
	if ($cgi_clientname == 'admin') {
		$ghtml->print_redirect("/?frm_emessage=cannot_reset_admin");
	}
*/

	if ($cgi_clientname != "" && $cgi_email != "") { 
		$tablename = $classname;
		$rawdb = new Sqlite(null, $tablename);
		$email = $rawdb->rawQuery("select contactemail from $tablename where nname = '$cgi_clientname';");


		if($email && $cgi_email == $email[0]['contactemail']) {
			$rndstring =  randomString(8);
			$pass = crypt($rndstring);

			$rawdb->rawQuery("update $tablename set password = '$pass' where nname = '$cgi_clientname'");
			$mailto = $email[0]['contactemail'];
			$name = "HyperVm";
			$email = "Admin";

			$cc = "";
			$subject = "Hypervm Password Reset Request";
			$message = "\n\n\nYour password has been reset to the one below for your HyperVm login.\n";
			$message .= "The Client IP address which requested the Reset: {$_SERVER['REMOTE_ADDR']}\n";
			$message .= 'Username: '. $cgi_clientname."\n";
			$message .= 'New Password: '. $rndstring.'';

			//$message = nl2br($message);

			lx_mail(null, $mailto, $subject, $message);

			$ghtml->print_redirect("/?frm_smessage=password_sent");

		} else {
			$ghtml->print_redirect("/?frm_emessage=nouser_email");
		}
	}
}
?>
</center>
</html>
