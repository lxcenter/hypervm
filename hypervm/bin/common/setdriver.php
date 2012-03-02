<?php 
/**
 *    HyperVM, Server Virtualization GUI for OpenVZ and Xen
 *
 *    Copyright (C) 2000-2009	LxLabs
 *    Copyright (C) 2009-2011	LxCenter
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * setdriver.php file.
 * 
 * It handles the change of drivers.
 * 
 * setdriver.php --server=[HOSTNAME] --class=[CLASS] --driver=[DRIVER]
 * 
 * HOSTNAME: it could be 'localhost' or another slave-id name.
 * 
 * CLASS:
 * 
 * It is the classified group for a set of drivers. Available are:
 * 
 * Class
 * - vps: sets the related vps drivers for a host
 * 
 * 	 Drivers:
 *     - openvz:   OpenVZ based virtual machines
 *     - xen: 	   Xen based virtual machines
 *     - kvm: 	   KVM based virtual machines
 *     - vmware:   VMWare based virtual machines
 *   
 *   Example: setdriver.php --server=localhost --class=vps --driver=openvz
 *     
 * - dns: set the DNS related drivers for a host
 * 
 * 	 Drivers:
 * 	   - powerdns: PowerDNS driver DNS management
 *     - ?
 *     
 *   Example: setdriver.php --server=localhost --class=dns --driver=powerdns
 *   
 * - ?
 * 
 * @todo UNDOCUMENTED (needs more work)
 * 
 * @copyright 2012, (c) LxCenter.
 * @license   AGPLv3 http://www.gnu.org/licenses/agpl-3.0.en.html
 * @author    Anonymous <anonymous@lxcenter.org>
 * @author    Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
 * @version   v1.0 20120302 build
 * @package   scripts
 */
include_once "htmllib/lib/include.php"; 

initProgram('admin');

$list = parse_opt($argv);

checkIfVariablesSet($list, array('server', 'class'));

$server = $list['server'];
$class = $list['class'];

if (!isset($list['driver'])) {
	$driverapp = $gbl->getSyncClass(null, $server, $class);
	echo 'Driver for class ' . $class . ' is ' . $driverapp . PHP_EOL;
	exit;
}
else 
{
	$driver = $list['driver'];
	
	changeDriverFunc($server, $class, $driver);
}