<?php 

include "htmllib/lib/apilib.phps";

api_main();

function api_main()
{

	list($server, $port) = explode(":", $_SERVER['SERVER_NAME']);

	print("Add an openvz vps: <br> \n");
	print("<form method=get action=http://{$_SERVER['SERVER_NAME']}/bin/webcommand.php>"); 
?> 

<input type=hidden name=login-class value=client>
<input type=hidden name=login-name value=admin>
<input type=hidden name=login-password value=hell>
<input type=hidden name=action value=add>
<input type=hidden name=class value=vps>
<input type=hidden name=v-type value=openvz>
Vps Type: openvz <br> 
Vps name: <br> 
<input type=text name=name value=> <br> 
Number of Ip addresses <br> 
<input type=text name=v-num_ipaddress_f value=> <br> 
Contact Email <br> 
<input type=text name=v-contactemail value=> <br> 

Password<br> 
<input type=text name=v-password value=> <br> 
<?php 

	get_and_print_a_select_variable("Ostemplate", "ostemplate_openvz", "v-ostemplate");
	get_and_print_a_select_variable("Server", "vpspserver_openvz", "v-syncserver");
	get_and_print_a_select_variable("Plan", "resourceplan", "v-plan_name");
	?> 
<input type=submit name=submit value=submit> <br> 
<?php 


}


