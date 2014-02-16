<?php 

// OA Why the heck is this here?
$accountlist = array('client' => "Kloxo Account", 'domain' => 'Domain Owner', 'mailaccount' => "Mail Account");

// If existing, use a own header for the login page else use the default.
if (lxfile_exists("__path_program_htmlbase/login/login_header_vendor.html")) {
    lreadfile("__path_program_htmlbase/login/login_header_vendor.html");
} else {
    lreadfile("__path_program_htmlbase/login/login_header.html");
}


// !!! OA  WE DO NOT USE THIS, Am I right?
// Load the lx javascript library
$ghtml->print_jscript_source("/htmllib/js/lxa.js");

// Is this a Slave server?
if ($sgbl->is_this_slave()) {
    // If existing, use a own header for the slave page else use the default.
    if (lxfile_exists("__path_program_htmlbase/login/login_slave_vendor.html")) {
        lreadfile("__path_program_htmlbase/login/login_lave_vendor.html");
    } else {
        lreadfile("__path_program_htmlbase/login/login_slave.html");
    }
    // If existing, use a own footer for the footer page else use the default.
    if (lxfile_exists("__path_program_htmlbase/login/login_footer_vendor.html")) {
        lreadfile("__path_program_htmlbase/login/login_footer_vendor.html");
    } else {
        lreadfile("__path_program_htmlbase/login/login_footer.html");
    }
    exit;
}

// Load the Login-Pre text from the database
$logfo = db_get_value("general", "admin", "login_pre");

// Replace tags
$logfo = str_replace("<%programname%>", ucfirst($sgbl->__var_program_name), $logfo);


if (!$cgi_forgotpwd) {
    $ghtml->print_message();


    // If the program is in demo mode then do not show the login page.
    if (if_demo()) {
        include_once("lib/demologins.php");
    } else {
        include("login/login_main.php");
    }


}
elseif ($cgi_forgotpwd == 1) {
    include("login/login_forgot.php");
} 
elseif ($cgi_forgotpwd == 2) {

    $progname = $sgbl->__var_program_name;
    $cprogname = ucfirst($progname);

    $cgi_clientname = $ghtml->frm_clientname;
    $cgi_email = $ghtml->frm_email;


    htmllib::checkForScript($cgi_clientname);
    $classname = $ghtml->frm_class;

    if (!$classname) {
        $classname = getClassFromName($cgi_clientname);
    }


    if ($cgi_clientname != "" && $cgi_email != "") {
        $tablename = $classname;
        $rawdb = new Sqlite(null, $tablename);
        $email = $rawdb->rawQuery("select contactemail from $tablename where nname = '$cgi_clientname';");


        if ($email && $cgi_email == $email[0]['contactemail']) {
            $rndstring = randomString(8);
            $pass = crypt($rndstring);

            $rawdb->rawQuery("update $tablename set password = '$pass' where nname = '$cgi_clientname'");
            $mailto = $email[0]['contactemail'];
            $name = "$cprogname";
            $email = "Admin";

            $cc = "";
            $subject = "$cprogname Password Reset Request";
            $message = "\n\n\nYour password has been reset to the one below for your $cprogname login.\n";
            $message .= "The Client IP address which requested the Reset: {$_SERVER['REMOTE_ADDR']}\n";
            $message .= 'Username: ' . $cgi_clientname . "\n";
            $message .= 'New Password: ' . $rndstring . '';


            lx_mail(null, $mailto, $subject, $message);

            $ghtml->print_redirect("/login/?frm_smessage=password_sent");

        } else {
            $ghtml->print_redirect("/login/?frm_emessage=nouser_email");
        }
    }
}
// If existing, use a own footer for the login page else use the default.
if (lxfile_exists("__path_program_htmlbase/login/login_footer_vendor.html")) {
    lreadfile("__path_program_htmlbase/lib/login_footer_vendor.html");
} else {
    lreadfile("__path_program_htmlbase/login/login_footer.html");
}

include("login/login_credit.php");
?>
</body>
</html>
