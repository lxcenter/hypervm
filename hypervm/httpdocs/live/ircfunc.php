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

$mode = 1;

function irc_read($socket, $len) {
	global $mode;

	$res = fread($socket, $len);
	$res = rtrim($res);
	return $res;
}

function irc_write($socket, $msg) {
    global $mode;
	return fputs($socket, $msg);
}

function irc_nb($socket) {
    global $mode;
	stream_set_blocking($socket, false);
}

function irc_open($serv_addr, $serv_port, &$errno, &$errstr) {
	global $mode;
	$fd =  stream_socket_client("ssl://$serv_addr:$serv_port");
	return $fd;
}

function irc_close($socket) {
    global $mode;
	return @fclose($socket);
}

function flush_server_buffer() {
    // flush();
    @ob_flush();
}

