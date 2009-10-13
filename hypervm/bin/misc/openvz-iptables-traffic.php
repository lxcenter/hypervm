<?php 
include_once "htmllib/lib/include.php"; 

vpstraffic__openvz::iptables_delete();
vpstraffic__openvz::iptables_create();
