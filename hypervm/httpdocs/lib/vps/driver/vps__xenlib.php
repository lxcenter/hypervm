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
* vps__xen class file.
* 
* It handles the driver for Xen VPS.
* 
* @copyright 2012, (c) LxCenter.
* @license AGPLv3 http://www.gnu.org/licenses/agpl-3.0.en.html
* @author Anonymous <anonymous@lxcenter.org>
* @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
* @version v1.0 20120218 build
*/
class vps__xen extends Lxdriverclass {

	/**
	 * @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
	 * 
	 * @const XEN_HOME The home path for Xen virtual machines
	 */
	const XEN_HOME = '/home/xen';
	
	/**
	* @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
	*
	* @const XEN_CONSOLE_BINARY The home path for Xen virtual machine console binary
	*/
	const XEN_CONSOLE_BINARY = '/usr/bin/lxxen';
	
	/**
	 * Finds the cpu usage on every xen machine.
	 * 
	 * It check the list returned by "xm list" command.
	 * 
	 * @author Anonymous <anonymous@lxcenter.org>
	 * @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
	 * 
	 * @return void
	 */
	public static function find_cpuusage()
	{
		$xen_list_output = lxshell_output('xm', 'list');
		$xen_list_lines = explode(PHP_EOL, $xen_list_output);
	
		if(!empty($xen_list_lines)) {
			foreach($xen_list_lines as $line) {
				$line = trimSpaces($line);
				$value = explode(' ', $line);
		
				if (!char_search_end($value[0], '.vm')) {
					continue;
				}
				
				execRrdSingle('cpu', 'DERIVE', $value[0], $value[5]);
			}
		}
	}

	/**
	* Finds the xen traffic on all the interfaces.
	* 
	* It recollect the total, incoming and outgoing traffic from
	* all the xen interfaces.
	*
	* @author Anonymous <anonymous@lxcenter.org>
	* @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
	*
	* @return void
	*/
	public static function find_traffic()
	{
		global $gbl, $sgbl, $login, $ghtml; 
	
		// Only apply if xeninterface.list file exist
		if (lxfile_exists('__path_program_etc/xeninterface.list')) {

			// Get the data trimmed from xeninterface.list
			$interfaces_list = lfile_trim('__path_program_etc/xeninterface.list');
		
			if (!lxfile_exists('__path_program_etc/newxeninterfacebw.data')) {
				
				$total = NULL;
				
				if (!empty($interfaces_list)) {
					foreach($interfaces_list as $interface) {
						$total[$interface] = self::get_bytes_for_interface($interface);
					}
				}
				
				// Print debugging info recollected
				dprintr($total);
				
				lfile_put_contents('__path_program_etc/newxeninterfacebw.data', serialize($total));
				return;
			}
		
			$data = unserialize(lfile_get_contents('__path_program_etc/newxeninterfacebw.data'));
		
			$total = NULL;
		
			if (!empty($interfaces_list)) {
				foreach($interfaces_list as $interface) {
					$total[$interface] = self::get_bytes_for_interface($interface);
			
					if (isset($data[$interface])) {
						if ($total[$interface]['total'] < $data[$interface]['total']) {
							$total_traffic          = $total[$interface]['total'];
							$total_traffic_incoming = $total[$interface]['incoming'];
							$total_traffic_outgoing = $total[$interface]['outgoing'];
						} else {
							$total_traffic          = $total[$interface]['total'] - $data[$interface]['total'];
							$total_traffic_incoming = $total[$interface]['incoming'] - $data[$interface]['incoming'];
							$total_traffic_outgoing = $total[$interface]['outgoing'] - $data[$interface]['outgoing'];
						}
					} else {
						$total_traffic          = $total[$interface]['total'];
						$total_traffic_incoming = $total[$interface]['incoming'];
						$total_traffic_outgoing = $total[$interface]['outgoing'];
					}
			
					execRrdTraffic('xen-' . $interface, $total_traffic, '-' . $total_traffic_incoming, $total_traffic_outgoing);
					$stringa[] = time() . ' ' . date('d-M-Y:H:i') . ' ' . $interface . ' ' . $total_traffic . ' ' . $total_traffic_incoming . ' ' . $total_traffic_outgoing;
				}
			}
		
			dprintr($total);
			$string = implode(PHP_EOL, $stringa);
			lfile_put_contents('/var/log/lxinterfacetraffic.log', $string . PHP_EOL, FILE_APPEND);
			lfile_put_contents('__path_program_etc/newxeninterfacebw.data', serialize($total));
		}
	}
	
	/**
	* Get the bytes for a given interface.
	*
	* Recollect the bytes column (incoming and outgoing) from
	* the file /proc/net/dev for a given interface on a machine.
	*
	* @author Anonymous <anonymous@lxcenter.org>
	* @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
	* 
	* @param string $interface The interface name. NULL by default.
	* @return array[integer] total, incoming, outgoing
	*/
	public static function get_bytes_for_interface($interface = NULL)
	{
		static $networks; // Make a cache with the networks available
	
		// Recollect the data for first time if not cached
		if (!isset($networks) || empty($networks)) {
			$networks = lfile_get_contents('/proc/net/dev');
			$networks = explode(PHP_EOL, $networks);
		}
	
		foreach($networks as $network) {
			$vif_interface = 'vif' . $interface . ':';
			$network = trimSpaces($network);
			
			if (!char_search_beg($network, $vif_interface)) {
				continue;
			}
			
			// Parse the data for get total/incoming/outgoing
			$network = strfrom($network, $vif_interface);
			$network = trimSpaces($network);
			$network_bytes = explode(' ', $network);
			
			$total_incoming = $network_bytes[8];
			$total_outgoing = $network_bytes[0];
			$total          = $total_outgoing + $total_incoming;
			
			// It seems for xen it is the reverse. The input for the vif is the output for the virtual machine.
			return array(
						 'total'    => $total, 
						 'incoming' => $total_incoming, 
						 'outgoing' => $total_outgoing,
						);
		}
		
		return 0; // Return 0 bytes
	}

	/**
	* 
	* @todo Check the behaviour of this fuction, maybe is @deprecated
	*
	* @author Anonymous <anonymous@lxcenter.org>
	* @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
	*
	* @param $vpsid
	* @param $command
	* @return void
	*/
	public static function execCommand($vpsid, $command)
	{
		global $global_shell_error, $global_shell_ret;
	}

	/**
	 * Get the list of operating system templates.
	 * 
	 * Search on xen/template/ folder each type of
	 * template, uncompress if necessary the template and
	 * calculate the size of each template on a list.
	 * 
	 * @author Anonymous <anonymous@lxcenter.org>
	 * @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
	 * 
	 * @param string $type the template list to fetch. Available add|img|tar.gz by default add
	 * @return array[string] size of each template indexed by template name
	 */
	public static function getOsTemplatelist($type = 'add')
	{
		$template_list = lscandir_without_dot('__path_program_home/xen/template/');
	
		$template_sizes = array();
		foreach($template_list as $template) {
			// Get the template size analizing the type
			switch($type) {
				case 'add':
					if (!char_search_end($template, '.tar.gz') && !char_search_end($template, '.img')) {
						continue; // Skip the template if not contains .tar.gz and .img
					}
					
					if (char_search_end($template, '.tar.gz')) {
						$size = lxfile_get_uncompressed_size('__path_program_home/xen/template/' . $template);
					} else {
						$size = lxfile_size('__path_program_home/xen/template/' . $template);
					}
				break;
				case 'img':
					if (!char_search_end($template, '.img')) {
						continue; // Skip the template if not contains .img
					}
					
					$size = lxfile_size('__path_program_home/xen/template/' . $template);
				break;
				case 'tar.gz':
					if (!char_search_end($template, '.tar.gz')) {
						continue; // Skip the template if not contains .tar.gz
					}
					
					$size = lxfile_get_uncompressed_size('__path_program_home/xen/template/' . $template);
				break;
			}
	
			$template_filtered = strtil($template, '.tar.gz');
			$size_MB = round($size / (1024 * 1024), 2);
			$template_sizes[$template_filtered] = $template_filtered . ' (' . $size_MB . 'MB)';
		}
		
		return $template_sizes;
	}

	/**
	* Check the Xen availability.
	*
	* Check if exists /proc/xen 
	*
	* @author Anonymous <anonymous@lxcenter.org>
	* @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
	*
	* @throws lxException
	* @return void
	*/
	public static function checkIfXenOK()
	{
		if (!lxfile_exists('/proc/xen')) {
			throw new lxException('no_kernel_support_for_xen._boot_into_the_right_kernel');
		}
	}

	/**
	* Get the status of Xen virtual machine.
	*
	* Check the status of background script. It
	* could have the create, createfailed or deleted.
	* 
	* If not is running, it returns on or off searching
	* by name on xm list command.
	*
	* @author Anonymous <anonymous@lxcenter.org>
	* @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
	*
	* @param string $virtual_machine_name The name of xen virtual machine
	* @param string $rootdir The root folder fot the virtual machine
	* @throws lxException
	* @return string The status of virtual machine
	*/
	public static function getStatus($virtual_machine_name, $rootdir)
	{
		self::checkIfXenOK();
	
		// Check if background create script is running
		if (lx_core_lock_check_only('background.php', $virtual_machine_name . '.create')) {
			return 'create';
		}
	
		// Check if background create failed
		if (lxfile_exists('__path_program_root/tmp/' . $virtual_machine_name . '.createfailed')) {
			$reason = lfile_get_contents('__path_program_root/tmp/' . $virtual_machine_name . '.createfailed');
			return 'createfailed: ' . $reason;
		}
	
		// Check if background script is deleted
		if (!lxfile_exists($rootdir . '/' . $virtual_machine_name)) {
			return 'deleted';
		}
	
		/*
		if (lx_core_lock("$virtual_machine_name.status")) {
			throw new lxException("xm_status_locked");
		}
		*/
		
		// List info about the virtual machine
		exec('xm list ' . $virtual_machine_name, $output, $status);
	
		if (!empty($status)) {
			return 'on';
		}
		else {
			return 'off';
		}
	}
	
	/**
	* Get the disk usage for a given disk on a windows based Xen virtual machine.
	*
	* Get the data from ntfscluster output processing the bytes per volume,
	* and bytes of user data.
	*
	* Calculate the total disk space and total disk space used.
	*
	* @author Anonymous <anonymous@lxcenter.org>
	* @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
	*
	* @param string $disk The disk on a xen virtual machine. Default NULL.
	* @param string $root_path The location for root path needed for windows based Xen virtual machine.
	* @return array[string] The total and used integer space indexed as string
	*/
	private static function getDiskUsageWindows($disk = NULL, $root_path = NULL)
	{
		$root_path = fix_vgname($root_path);
		$partition = get_partition($disk, $root_path);
		
		// @todo Check if the ntfscluster it's available to use and exists (never trusts on users)
		$output = lxshell_output('ntfscluster', '-f', $partition);
			
		// Disconnect the partition from the file on device mapper.
		// @todo Check if the kpartx it's available to use and exists (never trusts on users)
		$base = basename($disk);
		$image_file = '/dev/mapper/' . $root_path . '-' . $base;
		lxshell_return('kpartx', '-d', $image_file);

		if(!empty($output)) {
			// If no output returned we return 0 MBytes (fallback mode)
			$ouput_lines = explode(PHP_EOL, $output);
			
			// Process the ntfscluster output
			if(!empty($ouput_lines)) // Ensure not process truncate output
			{
				foreach($ouput_lines as $line) {
					$line = trim($line);
				
					// Only process lines with :
					if (char_search_a($line, ':')) {
						list($variable_header, $value) = explode(':', $line);
					
						$variable_header = trim($variable_header);
						$value           = trim($val);
					
						// Get the bytes per volume line
						if ($variable_header === 'bytes per volume') {
							$total_disk = $value;
						}
					
						// Get the bytes of user data line
						if ($variable_header === 'bytes of user data') {
							$total_disk_used = $value;
						}
					}
				}
			}
			
			// Round total and used to MBytes with 2 decimals
			$result['total'] = round($total_disk / (1024 * 1024), 1);
			$result['used']  = round($total_disk_used / (1024 * 1024), 1);
		}
		else { // Fallback mode
			$result['total'] = 0;
			$result['used']  = 0;
		}
		
		return $result;
	}
	
	/**
	* Get the disk usage for a given disk on a Xen virtual machine.
	*
	* Get the data from dumpe2fs output processing the block size,
	* block count and free blocks.
	* 
	* Calculate the total disk space and total disk space used.
	*
	* @author Anonymous <anonymous@lxcenter.org>
	* @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
	*
	* @param string $disk The disk on a xen virtual machine. Default NULL.
	* @param boolean $is_windows TRUE if the Xen virtual machine is windows based.
	* @param string $root_path The location for root path needed for windows based Xen virtual machine.
	* @return array[string] The total and used integer space indexed as string
	*/
	public static function getDiskUsage($disk = NULL, $is_windows = FALSE, $root_path = NULL)
	{
		global $global_dontlogshell;
		
		// Initialize 0 MBytes default usage (prevent errors with fallback)
		$result['total'] = 0;
		$result['used']  = 0;
		
		// @todo Check if it is a valid disk path (never trusts on users)
		$disk = expand_real_root($disk);
		
		// Check if the Xen virtual machine is windows based
		if($is_windows)
		{
			$result = $this->getDiskUsageWindows($disk, $root_path);
		}
		else { // For Unix based Xen virtual machine
			// @todo Check if the dumpe2fs it's available to use and exists (never trusts on users)
			$global_dontlogshell = TRUE;
			$output = lxshell_output('dumpe2fs', '-h', $disk);
			$global_dontlogshell = FALSE;
			
			if(!empty($output)) { // If no output returned we return 0 MBytes (fallback mode)
				$ouput_lines = explode(PHP_EOL, $output);
				
				// Process the dumpe2fs output
				if(!empty($ouput_lines)) // Ensure not process truncate output
				{
					foreach($ouput_lines as $line) {
						// Get the Block size line (on bytes) 
						if (char_search_beg($line, 'Block size:')) {
							$blocksize = intval(trim(strfrom($line, 'Block size:'))); 
						}
						
						// Get the Block count number line
						if (char_search_beg($line, 'Block count:')) {
							$block_count = intval(trim(strfrom($line, 'Block count:')));
						}
						
						// Get the Free blocks number line
						if (char_search_beg($line, 'Free blocks:')) {
							$free_blocks = intval(trim(strfrom($line, 'Free blocks:')));
						}
					}
					
					$blocksize         = $blocksize / 1024; // Convert total bytes to KBytes
					$total_disk_space  = $block_count * $blocksize;
					$total_free_blocks = $free_blocks * $blocksize;
					$total_disk_used   = $total_disk_space - $total_free_blocks;
					
					// Round total and used to MBytes with 2 decimals
					$result['total'] = round($total_disk_space / 1024, 2);
					$result['used']  = round($total_disk_used / 1024, 2);
				}
			}
		}
		
		return $result;
	}

	/**
	* Init the main Xen Virtual Machine vars.
	* 
	* It check if a LVM is found for change the normal paths.
	*
	* @author Anonymous <anonymous@lxcenter.org>
	* @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
	*
	* @return void
	*/
	public function initXenVars()
	{
		$main_path = $this->main;
		
		// If LVM add core root dir with fix vg name
		if ($this->isLvm()) {
			$vgname = '/dev/' . fix_vgname($main_path->corerootdir) . '/';
			$main_path->maindisk = $vgname . $main_path->maindiskname;
			$main_path->swapdisk = $vgname . $main_path->swapdiskname;
		} else { // If not put root dir
			$main_path->rootdir  = $main_path->corerootdir . '/' . $main_path->nname . '/';
			$main_path->maindisk = $main_path->rootdir     . '/' . $main_path->maindiskname;
			$main_path->swapdisk = $main_path->rootdir     . '/' . $main_path->swapdiskname;
		}
	
		$main_path->configrootdir = '__path_home_dir/xen/' . $main_path->nname . '/';
	}

	/**
	* @todo UNDOCUMENTED
	*
	* @author Anonymous <anonymous@lxcenter.org>
	* @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
	*
	* @return void
	*/
	public function doSyncToSystemPre()
	{
		$main = $this->main;
		
		if ($main->checkIfOffensive()) {
			dprint('Offensive checking...' . PHP_EOL);
			
			$virtual_machine_name = $main->nname;
			
			$main->checkVPSLock($virtual_machine_name);
		}
		
		$this->initXenVars();
	}

	/**
	 * @todo UNDOCUMENTED
	 * 
	 * @see lxDriverClass::dosyncToSystemPost()
	 * 
	 * @author Anonymous <anonymous@lxcenter.org>
	 * @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
	 *
	 * @return void
	 */
	public function dosyncToSystemPost()
	{
		$main = $this->main;
		$database_action = isset($main->dbaction) ? $main->dbaction : NULL;
		
		/** @see Lxclass */
		$custom_execution = isset($main->__var_custom_exec) ? $main->__var_custom_exec : NULL;
		
		if ($database_action === 'update' && $custom_execution) {
			lxshell_direct($custom_execution); /* @todo it seems a bad habit to custom calls. Check this on a future */
		}
	}

	/**
	 * Checks if a resource is no limited.
	 * 
	 * This is a wrapper method for deprecate the global
	 * function is_unlimited on lxlib.php
	 * 
	 * @see $this->isUnlimited() lxlib.php
	 * 
	 * @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
	 * 
	 * @access private
	 * @param string $resource The name resource property to check
	 * @return boolean True if $resource is 'unlimited' or 'na' string
	 */
	private function isUnlimited($resource)
	{
		return is_unlimited($resource);
	}
	
	/**
	 * Get the free disk space on a Xen virtual machine.
	 * 
	 * Checks if it is LVM based.
	 * 
	 * @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
	 * 
	 * @access private
	 * @return integer Free disk space on MB (no bytes included via backend)
	 */
	private function getFreeDiskSpace()
	{
		$main = $this->main;
		$root_path = isset($main->corerootdir) ? $main->corerootdir : NULL;
		
		// Init 0 bytes as fallback value
		$free_disk_space = 0;
		
		if ($this->isLVM()) {
			$free_disk_space = vg_diskfree($root_path);
		} else {
			$free_disk_space = lxfile_disk_free_space($root_path);
		}
		
		return $free_disk_space;
	}
	
	/**
	 * Create a root path folder.
	 * 
	 * It only create for non LVM based Xen Virtual machines.
	 * 
	 * @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
	 * 
	 * @access private
	 * @return void
	 */
	private function createRootPath()
	{
		$main = $this->main;
		
		$root_path = isset($main->rootdir) ? $main->rootdir : NULL;
		
		if (!$this->isLvm()) {
			lxfile_mkdir($root_path);
		}
	}
	
	public function dbactionAdd()
	{
		global $gbl, $sgbl, $login, $ghtml; 
	
		self::checkIfXenOK();
	
		$ret = lxshell_return('xm', '--help');
	
		if ($ret == 127) {
			throw new lxException('no_xen_at_all');
		}
	
		$main = $this->main;
		$privilege = isset($main->priv) ? $main->priv : NULL;
		
		$diskusage = $privilege->disk_usage;
		
		// If unlimited put 3 GB, if not get use the normal disk usage (@todo checks if it's a bug)
		if ($this->isUnlimited($privilege->disk_usage)) {
			$diskusage = 3 * 1024; // Put 3 GB (@todo: no byte counted, is bad done on resource backend instead)
		}
	
		if ($main->isWindows() && $diskusage < 2 * 1024) {
			//throw new lxException("windows_needs_more_than_2GB");
		}
	
		$freediskspace = $this->getFreeDiskSpace();
	
		if (($freediskspace - $diskusage) < 20) {
			throw new lxException('not_enough_space');
		}
	
		$user_name            = $main->username;
		$password             = $main->password;
		$virtual_machine_name = $main->nname;
		
		if ($main->dbaction === 'syncadd') {
			$vps_username_created = vps::create_user($user_name, $password, $virtual_machine_name, self::XEN_CONSOLE_BINARY);
			return NULL;
		}
	
		if (self::getStatus($virtual_machine_name, self::XEN_HOME) !== 'deleted') {
			throw new lxException('a_virtual_machine_with_the_same_id_exists');
		}
	
		// Check if the template begins with "windows-lxblank"
		if ($main->isBlankWindows()) {
			if (!lxfile_exists('/home/wincd.img')) {
				throw new lxException('windows_installation_image_missing');
			}
		}
	
		/*
		if (!lxfile_exists("__path_program_home/xen/template/{$main->ostemplate}.tar.gz")) {
			throw new lxException("could_not_find_the_osimage", '', $main->ostemplate);
		}
		*/
	
		$vps_username_created = vps::create_user($user_name, $password, $virtual_machine_name, self::XEN_CONSOLE_BINARY);
	
		$this->createRootPath();
	
		lxfile_mkdir($main->configrootdir);
		$this->setMemoryUsage();
		$this->setCpuUsage();
		$this->setSwapUsage();
		$this->setDiskUsage();
		if ($sgbl->isDebug()) {
			$this->doRealCreate();
		} else {
			callObjectInBackground($this, 'doRealCreate');
		}
	
		$result = array('__syncv_username' => $vps_username_created);
		
		return $result;
	}

	public function doRealCreate()
	{
	
		global $gbl, $sgbl, $login, $ghtml; 
		$nname = $this->main->nname;
		lx_core_lock("$nname.create");
	
		$this->createRootPath();
	
	
		lxfile_mkdir($this->main->configrootdir);
	
		$this->setDhCP();
	
		if ($this->main->isWindows()) {
			$templatefile = "__path_program_home/xen/template/{$this->main->ostemplate}";
		} else {
			$templatefile = "__path_program_home/xen/template/{$this->main->ostemplate}.tar.gz";
		}
	
		$this->main->getOsTemplateFromMaster($templatefile);
	
		if (!lxfile_real($templatefile)) {
			log_error("could not create vm. Could not download $templatefile");
			lfile_put_contents("__path_program_root/tmp/$nname.createfailed", "Could not download $templatefile");
			exit;
		}
	
		$size = 0;
		if ($this->main->isWindows()) {
			$size = lxfile_size($templatefile);
			$size = $size / (1024 * 1024);
		}
	
		try {
			$this->createConfig();
			$this->setMemoryUsage();
			$this->setCpuUsage();
			$this->setSwapUsage();
			$this->createSwap();
			$this->createDisk($size);
		} catch (Exception $e) {
			log_error("could not create vm. Error was {$e->getMessage()}");
			lfile_put_contents("__path_program_root/tmp/$nname.createfailed", "{$e->getMessage()}");
			exit;
		}
	
	
	
		if (!$this->main->isWindows()) {
			$mountpoint = $this->mount_this_guy();
			lxshell_return("tar", "-C", $mountpoint, "--numeric-owner", "-xpzf", $templatefile);
			$this->setInternalParam($mountpoint);
			$this->copyKernelModules();
			lxfile_cp("../file/sysfile/xen/fstab", "$mountpoint/etc/fstab");
			lunlink("$mountpoint/etc/mtab");
		} else if (!$this->main->isBlankWindows()) {
			$templatefile = expand_real_root($templatefile);
			lxshell_return("parted", "-s", $this->main->maindisk, "mklabel", "msdos");
			$this->runParted();
			$partition = $this->getPartition();
			lxshell_return("ntfsfix", $partition);
			//lxshell_return("dd", "if=$templatefile", "of={$this->main->maindisk}");
			lxshell_return("ntfsclone", "--restore-image", "--force", "-O", $partition, $templatefile);
			$this->kpart_remove();
			$this->expandPartitionToImage();
			//lxshell_return("ntfsclone", "-O", $loop, $templatefile);
			//lo_remove($loop);
		}
	
		$this->main->status = 'On';
	
		try {
			$this->toggleStatus();
		} catch (Exception $e) {
		}
	
		$this->postCreate();
	}

	public function postCreate()
	{
		if ($this->main->__var_custom_exec) {
			lxshell_direct($this->main->__var_custom_exec);
		}
	}

	public function runParted()
	{
		lxshell_return("parted", "-s", $this->main->maindisk, "--", "unit", "s", "rm", "1");
		lxshell_return("parted", "-s", $this->main->maindisk, "--", "unit", "s", "mkpart", "primary", "ntfs", "63", "-1");
		lxshell_return("parted", "-s", $this->main->maindisk, "set", "1", "boot", "on");
	}

	public function expandPartitionToImage()
	{
		/*
		$out = lxshell_output("parted", $this->main->maindisk, "unit", "s", "print", "free");
		$list = explode("\n", $out);
		foreach($list as $l) {
			if (char_search_beg($l, "Disk")) {
				$s = explode(":", $l);
				$s = trim($s[1]);
				$s = strtil($s, "s");
				break;
			}
		}
	*/
		$this->runParted();
		$partition = $this->getPartition();
		lxshell_return("ntfsfix", $partition);
		//lxshell_expect("ntfsresize", "ntfsresize -f $partition");
		lxshell_return("ntfsresize", "-ff", $partition);
		$this->kpart_remove();
	}

	public function kpart_remove()
	{
		$disk = $this->main->maindisk;
		$root = $this->main->corerootdir;
		$lv = basename($disk);
		$root = fix_vgname($root);
		$path = "/dev/mapper/$root-$lv";
		lxshell_return("kpartx", "-d", $path);
	}

	public function copyKernelModules()
	{
		$mountpoint = $this->mount_this_guy();
		$kernev = trim(`uname -r`);
	
	
		if (!lxfile_exists("$mountpoint/lib/modules/$kernev")) {
			lxfile_cp_rec("/lib/modules/$kernev", "$mountpoint/lib/modules/$kernev");
		}
		if (char_search_end($kernev, "-xen")) {
			$nkernev = strtil($kernev, "-xen");
			if (!lxfile_exists("$mountpoint/lib/modules/$nkernev")) {
				lxfile_cp_rec("/lib/modules/$kernev", "$mountpoint/lib/modules/$nkernev");
			}
		}
		if (char_search_beg($this->main->ostemplate, "centos-")) {
			if (lxfile_exists("$mountpoint/lib/tls")) {
				lxfile_rm_rec("$mountpoint/lib/tls.disabled");
				lxfile_mv_rec("$mountpoint/lib/tls", "$mountpoint/lib/tls.disabled");
			}
		}
	}

	public function createDisk($size = 0)
	{
		if ($this->isUnlimited($this->main->priv->disk_usage)) {
			$diskusage = 3 * 1024;
		} else {
			$diskusage = $this->main->priv->disk_usage ;
		}
	
		if (lxfile_exists($this->main->maindisk)) {
			return;
		}
	
		if ($size && $this->main->isWindows()) {
			//$diskusage = $size;
		}
	
		$freediskspace = $this->getFreeDiskSpace();
	
		if (($freediskspace - $diskusage) < 20) {
			throw new lxException("not_enough_space");
		}
	
		if ($this->isLVM()) {
			lvm_create($this->main->corerootdir, $this->main->maindiskname, $diskusage);
		} else {
			lxfile_mkdir($this->main->rootdir);
			lxshell_return("dd", "if=/dev/zero", "of={$this->main->maindisk}", "bs=1M", "conv=notrunc", "count=1", "seek=$diskusage");
		}
	
		if (!$this->main->isWindows()) {
			lxshell_return("mkfs.ext3", "-F", $this->main->maindisk);
		}
	}

	public static function createVpsObject($servername, $input)
	{
		$name = "{$input['name']}.vm";
		$vpsobject = new Vps(null, $servername, $name);
		$vpsobject->parent_clname = createParentName('client', 'admin');
		$vpsobject->priv = new priv(null, null, $vpsobject->nname);
		$vpsobject->priv->__parent_o = $vpsobject;
		$vpsobject->used = new used(null, null, $vpsobject->nname);
		$vpsobject->used->__parent_o = $vpsobject;
		$vpsobject->vpsipaddress_a = array();
		$vpsobject->vpsid = '-';
		$vpsobject->password = crypt($name);
		$vpsobject->cpstatus = 'on';
		$vpsobject->status = 'on';
		$vpsobject->ttype = 'xen';
		$vpsobject->iid = $name;
		$vpsobject->ddate = time();
	
		if ($input['type'] === 'file') {
			$vpsobject->corerootdir = $input['location'];
		} else {
			$vpsobject->corerootdir = "lvm:{$input['location']}";
		}
	
		$vpsobject->maindiskname = $input['maindiskname'];
		$vpsobject->swapdiskname = $input['swapdiskname'];
	
		if ($input['type'] === 'file') {
			$vpsobject->maindisk = "{$vpsobject->corerootdir}/{$vpsobject->maindiskname}";
			$vpsobject->swapdisk = "{$vpsobject->corerootdir}/{$vpsobject->swapdiskname}";
		} else {
			$vgname = $vpsobject->corerootdir;
			$vgname = fix_vgname($vgname);
			$vpsobject->maindisk = "/dev/$vgname/{$vpsobject->maindiskname}";
			$vpsobject->swapdisk = "/dev/$vgname/{$vpsobject->swapdiskname}";
		}
	
		if (isset($input['gateway'])) {
			$vpsobject->networkgateway = $input['gateway'];
		}
	
		if (isset($input['netmask'])) {
			$vpsobject->networknetmask = $input['netmask'];
		}
	
	
		$vpsobject->priv->realmem_usage = $input['memory'];
		$vpsobject->priv->disk_usage = lvm_disksize($vpsobject->maindisk);
		$vpsobject->priv->swap_usage = lvm_disksize($vpsobject->swapdisk);
		$vpsobject->priv->backup_flag = 'on';
		$vpsobject->ostemplate = 'unknown';
	
		if (isset($input['ipaddress'])) {
			self::importIpaddress($vpsobject, $input['ipaddress']);
		}
	
		return $vpsobject;
	
	}

	public static function importIpaddress($vpsobject, $val)
	{
		$list = explode(" ", $val);
		foreach($list as $l) {
			$ipadd = new vmipaddress_a(null, $vpsobject->syncserver, $l);
			$vpsobject->vmipaddress_a[$ipadd->nname] = $ipadd;
		}
	}

	public function getRealMemory()
	{
		if ($this->isUnlimited($this->main->priv->realmem_usage)) {
			$memory = 512;
		} else {
			$memory = $this->main->priv->realmem_usage;
		}
		return $memory;
	}

	public function getVifString()
	{
		if (!$this->isUnlimited($this->main->priv->uplink_usage) && ($this->main->priv->uplink_usage > 0)) {
			$ratestring = "rate = {$this->main->priv->uplink_usage}KB/s,";
		} else {
			$ratestring = null;
		}
		if (trim(lxshell_output("uname", "-r")) === "2.6.16.33-xen0") {
			$vifnamestring = null;
		} else {
			$vifnamestring = "vifname=vif{$this->main->vifname},";
		}
	
		$ipstring = null;
		if ($this->main->vmipaddress_a) {
			$ilist = get_namelist_from_objectlist($this->main->vmipaddress_a);
			$ips = implode(" ", $ilist);
			$ipstring = "ip=$ips,";
		}
	
		$mac = $this->main->macaddress;
		if (!char_search_beg($mac, "aa:00")) { $mac = "aa:00:$mac"; }
		if (strlen($mac) === 14) { $mac = "$mac:01"; }
		$bridgestring = null;
		if ($this->main->networkbridge && $this->main->networkbridge !== '--automatic--') {
			$bridgestring = ",bridge={$this->main->networkbridge}";
		}
		$string = "vif        = ['$ipstring $vifnamestring $ratestring mac=$mac $bridgestring']\n";
		return $string;
	}

	public function addVcpu()
	{
		if ($this->isUnlimited($this->main->priv->ncpu_usage)) {
			$cpunum = os_getCpuNum();
		} else {
			$cpunum = $this->main->priv->ncpu_usage;
		}
	
		if ($cpunum > 0) {
			return  "vcpus = $cpunum\n";
		}
		return null;
	}

	public function createWindowsConfig()
	{
		$memory = $this->getRealMemory();
		if (trim(lxshell_output("uname", "-r")) === "2.6.16.33-xen0") {
			$vifnamestring = null;
		} else {
			$vifnamestring = "vifname=vif{$this->main->vifname},";
		}
		if (!$this->isUnlimited($this->main->priv->uplink_usage) && ($this->main->priv->uplink_usage > 0)) {
			$ratestring = "rate = {$this->main->priv->uplink_usage}KB/s,";
		} else {
			$ratestring = null;
		}
	
		$mac = $this->main->macaddress;
		if (!char_search_beg($mac, "aa:00")) { $mac = "aa:00:$mac"; }
		$count = count($this->main->vmipaddress_a);
		// Big bug workaround. the first vif seems to be ignored. Need to be fixed later.
		$vifnamestring = "vifname=vif{$this->main->vifname},";
		//$vif[] = "'type=ioemu, $vifnamestring $ratestring mac=$mac:00'";
		for ($i = 1; $i <= $count; $i++) {
			$hex = get_double_hex($i);
			$h = base_convert($i, 10, 36);
			$bridgestring = null;
			if ($this->main->networkbridge && $this->main->networkbridge !== '--automatic--') {
				$bridgestring = ",bridge={$this->main->networkbridge}";
			}
			$vifnamestring = "vifname=vif{$this->main->vifname}$h,";
			$vif[] = "'type=ioemu, $vifnamestring $ratestring mac=$mac:$hex $bridgestring'";
		}
		$vif = implode(", ", $vif);
		$vif = "vif = [ $vif ]\n";
	
			
	
		$string = null;
		$string .= "import os, re\n";
		$string .= "arch = os.uname()[4]\n";
		$string .= "if re.search('64', arch):\n";
		$string .= "    arch_libdir = 'lib64'\n";
		$string .= "else:\n";
		$string .= "    arch_libdir = 'lib'\n";
		$string .= "name = '{$this->main->nname}'\n";
	
		$string .= "kernel = '/usr/lib/xen/boot/hvmloader'\n";
		$string .= "builder='hvm'\n";
		if ($this->main->isBlankWindows()) {
			$string .= "boot='d'\n";
		} else {
			$string .= "boot='c'\n";
		}
		$string .= "memory = $memory\n";
		$string .= $vif;
		$string .= "device_model = '/usr/' + arch_libdir + '/xen/bin/qemu-dm'\n";
		$string .= "vnc=1\n";
		$string .= "sdl=0\n";
	
		$string .= $this->addVcpu();
	
		$string .= "vnclisten='0.0.0.0'\n";
		$string .= "vncpasswd='{$this->main->realpass}'\n";
		if ($this->main->isBlankWindows()) {
			$string .= "disk = [ 'file:/home/wincd.img,hdc:cdrom,r', 'phy:{$this->main->maindisk},ioemu:hda,w']\n";
			$string .= "acpi=1\n";
		} else {
			$string .= "disk = [ 'phy:{$this->main->maindisk},ioemu:hda,w']\n";
			$string .= "acpi=1\n";
		}
		$string .= "vncunused=0\n";
		$string .= "vncdisplay={$this->main->vncdisplay}\n";
	
		if ($this->main->text_xen_config) {
			$string .= "{$this->main->text_xen_config}\n";
		}
	
	
		lxfile_mkdir($this->main->configrootdir);
		lfile_put_contents("{$this->main->configrootdir}/{$this->main->nname}.cfg", $string);
	}

	public function createConfig()
	{
		global $gbl, $sgbl, $login, $ghtml; 
	
		if ($this->main->isOn('nosaveconfig_flag')) {
			return;
		}
	
		if ($this->main->isWindows()) {
			$this->createWindowsConfig();
			return;
		}
	
		$memory = $this->getRealMemory();
	
		if ($this->isLVM()) { $loc = "phy"; } 
		else { $loc = "file"; }
	
		$string  = null;
	
		$sk = "/boot/hypervm-xen-vmlinuz-{$this->main->nname}";
	
		if (lxfile_exists($sk)) {
			$kern = $sk;
		} else {
			$kern = "/boot/hypervm-xen-vmlinuz";
		}
		$string .= "kernel     = '$kern'\n";
	
	
		$customram = "/boot/hypervm-xen-initrd-{$this->main->nname}.img";
	
		if (lxfile_exists($customram)) {
			$string .= "ramdisk    = '$customram'\n";
		} else if (lxfile_exists('/boot/hypervm-xen-initrd.img')) {
			$string .= "ramdisk    = '/boot/hypervm-xen-initrd.img'\n";
		}
	
		if ($this->isUnlimited($this->main->priv->cpu_usage)) {
			$cpu = "100" * os_getCpuNum();;
		} else {
			$cpu = $this->main->priv->cpu_usage;
		}
	
		if ($this->isUnlimited($this->main->priv->cpuunit_usage)) {
			$cpuunit = "1000";
		} else {
			$cpuunit = $this->main->priv->cpuunit_usage;
		}
	
		if (!is_numeric($cpuunit)) { $cpuunit = '1000'; }
		if (!is_numeric($cpu)) { $cpu = "100" * os_getCpuNum(); }
	
		$string .= "memory     = $memory\n";
		//$string .= "cpu_cap     = $cpu\n";
		$string .= "cpu_weight     = $cpuunit\n";
		$string .= "name       = '{$this->main->nname}'\n";
		$string .= $this->getVifString();
		$string .= "vnc        = 0\n";
	
		$string .= $this->addVcpu();
	
		$string .= "vncviewer  = 0\n";
		$string .= "serial     = 'pty'\n";
		$string .= "disk       = ['$loc:{$this->main->maindisk},sda1,w', '$loc:{$this->main->swapdisk},sda2,w']\n";
		$string .= "root = '/dev/sda1 ro'\n";
		
		//Add pygrub configuration if template name contains pygrub
		$pygrub_record = explode('-', $this->main->ostemplate);
		if (stripos($pygrub_record[3], 'pygrub') !== FALSE) {
			$string .= "kernel = '';\nroot = '';\nbootloader = '/usr/bin/pygrub'\n";
		}
	
		if ($this->main->text_xen_config) {
			$string .= "{$this->main->text_xen_config}\n";
		}
	
		lxfile_mkdir($this->main->configrootdir);
		lfile_put_contents("{$this->main->configrootdir}/{$this->main->nname}.cfg", $string);
	
	
	}

	public function getValueFromFile($file)
	{
		$vfile = "{$this->main->configrootdir}/$file";
		if (!lxfile_exists($vfile)) {
			return ;
		}
		$v = lfile_get_contents($vfile);
		lunlink($vfile);
		$v = trim($v);
		return $v;
	}

	public function resizeRootImage()
	{
	
		$v = $this->getValueFromFile("disk.value");
		if (!$v) { return; }
	
		$this->stop();
	
		$this->umountThis();
	
		if ($this->isLVM()) {
			lvm_extend($this->main->maindisk, $v);
			$disk = $this->main->maindisk;
		} else {
			lxshell_return("dd", "if=/dev/zero", "of={$this->main->maindisk}", "bs=1M", "conv=notrunc", "count=1", "seek=$v");
			$disk = $this->main->maindisk;
			//$disk = $this->get_free_loop();
			//$ret = lxshell_return("losetup", $disk, $this->main->maindisk);
		}
	
	
		if ($this->main->isWindows()) {
			$this->expandPartitionToImage();
		} else {
			lxshell_return("e2fsck", "-f", "-y", $disk);
			lxshell_return("resize2fs", $disk);
		}
		if (!$this->isLVM()) { // @todo $this->createRootPath(); ?
			//lo_remove($disk);
		}
	
	}

	public function getPartition()
	{
		return get_partition($this->main->maindisk, $this->main->corerootdir);
	}
	
	public function get_free_loop()
	{
		return get_free_loop();
	}

	public function isLvm()
	{
		if(isset($this->main->corerootdir)) {
			return char_search_beg($this->main->corerootdir, 'lvm:');
		}
		else {
			return FALSE;
		}
	}

	public function createSwap()
	{
		global $gbl, $sgbl, $login, $ghtml; 
		global $global_shell_error, $global_shell_ret;
	
		if ($this->main->isWindows()) {
			return;
		}
	
		$v = $this->getValueFromFile("swap.value");
	
		if (!$v) { return ; }
	
		if ($this->isLVM()) {
			lvm_remove($this->main->swapdisk);
			$ret = lvm_create($this->main->corerootdir, $this->main->swapdiskname, $v);
			if ($ret) {
				throw new lxException("failed_to_create_swap", '', $global_shell_error);
			}
		} else {
			lunlink($this->main->swapdisk);
			lxshell_return("dd", "if=/dev/zero", "of={$this->main->swapdisk}", "bs=1M", "count=1", "seek=$v");
		}
	
		lxshell_return("mkswap", $this->main->swapdisk);
	}

	public function setvif()
	{
		$filelist = lfile_trim("__path_program_etc/xeninterface.list");
		$list = $this->main->getViflist();
		foreach($list as $l) {
			$filelist = array_push_unique($filelist, $l);
		}
		dprintr($filelist);
		lfile_put_contents("__path_program_etc/xeninterface.list", implode("\n", $filelist));
	}

	public function deletevif()
	{
		$filelist = lfile_trim("__path_program_etc/xeninterface.list");
		$list = $this->main->getViflist();
		foreach($list as $l) {
			$filelist = array_remove($filelist, $l);
		}
		dprintr($filelist);
		if ($filelist) {
			lfile_put_contents("__path_program_etc/xeninterface.list", implode("\n", $filelist));
		}
	}

	public function dbactionDelete()
	{
		global $gbl, $sgbl, $login, $ghtml; 
		$this->deletevif();
		$this->hardstop();
		$this->umountThis();
	
		if ($this->isLVM()) {
			if ($this->main->isWindows()) {
				lxshell_return("parted", "-s", $this->main->maindisk, "--", "unit", "s", "rm", "1");
				lxshell_return("parted", "-s", $this->main->maindisk, "--", "mklabel", "msdos");
			}
			lvm_remove($this->main->maindisk);
			if (!$this->main->isWindows()) {
				lvm_remove($this->main->swapdisk);
			}
		} else {
			lxfile_rm_rec($this->main->rootdir);
		}
	
		@ lunlink("__path_program_root/tmp/{$this->main->nname}.createfailed");
		lxfile_rm_rec($this->main->configrootdir);
		lxshell_return("userdel", "-r", $this->main->username);
		lunlink("/etc/xen/auto/{$this->main->nname}.cfg");
	}

	public function toggleStatus()
	{
		global $global_shell_out, $global_shell_error, $global_shell_ret;
	
		if ($this->main->isOn('status')) {
			$ret = $this->start();
			if ($ret) {
				throw new lxException("could_not_start_vps", '', str_replace("\n", ": ", $global_shell_error));
			}
			$ret = lxfile_symlink("{$this->main->configrootdir}/{$this->main->nname}.cfg", "/etc/xen/auto");
		} else {
			$ret = $this->stop();
			lunlink("/etc/xen/auto/{$this->main->nname}.cfg");
		}
	
		if($ret)
			log_message($ret);
	}

	public function setRootPassword()
	{
	
	}

	public function mount_this_guy()
	{
		$this->stop();
	
		if ($this->main->isWindows()) {
			return;
			throw new lxException("trying_to_mount_windows_image", '', '');
		}
	
		$mountpoint = "{$this->main->configrootdir}/mnt";
		if ($this->isMounted()) {
			return $mountpoint;
		}
	
		lxfile_mkdir($mountpoint);
	
	
		$loop = $this->main->maindisk;
		lxshell_return("e2fsck", "-y", $loop);
	
		if ($this->isLVM()) {
			$ret = lxshell_return("mount", $loop, $mountpoint);
		} else {
			$ret = lxshell_return("mount", "-o", "loop", $loop, $mountpoint);
		}
	
		if ($ret) {
			throw new lxException("could_not_mount_the_root_image");
		}
		return $mountpoint;
	}

	public function takeSnapshot()
	{
		lxshell_return("modprobe", "dm-snapshot");
		$tmp = "{$this->main->configrootdir}/snapshot_mount";
		lxfile_mkdir($tmp);
		$tmp = expand_real_root($tmp);
		$size = lvm_disksize($this->main->maindisk);
		$size = $size/3;
		$size = round($size);
		$vgname = $this->main->corerootdir;
		$vgname = fix_vgname($vgname);
	
	
		$sfpath = "/dev/$vgname/{$this->main->nname}_snapshot";
		$out = exec_with_all_closed_output("lvdisplay -c $sfpath");
	
		if (csa($out, ":")) {
			lxshell_return("umount", $sfpath);
			lvm_remove($sfpath);
		}
	
	
		$out = exec_with_all_closed_output("lvdisplay -c $sfpath");
	
		if (csa($out, ":")) {
			throw new lxException("old_snapshot_exists_and_cant_remove");
		}
	
		$ret = lxshell_return("lvcreate", "-L{$size}M", "-s",  "-n", "{$this->main->nname}_snapshot", $this->main->maindisk);
	
		if ($ret) {
			throw new lxException("could_not_create_snapshot_lack_of_space");
		}
	
		if (!$this->main->isWindows()) {
			lxshell_return("e2fsck", "-f", "-y", $sfpath);
			lxshell_return("mount", "-o", "ro", $sfpath, $tmp);
		} else {
			$tmp = $sfpath;
		}
	
		return $tmp;
	}

	public function changeLocation()
	{
		if ($this->main->newlocation === $this->main->corerootdir) {
			throw new lxException("old_new_location_same");
		}
	
		$this->stop();
		$this->umountThis();
		$this->setMemoryUsage();
		$this->setCpuUsage();
		$this->setDiskUsage();
		$this->__oldlocation = $this->main->corerootdir;
		$this->main->corerootdir = $this->main->newlocation;
		$name = strtil($this->main->nname, ".vm");
		$this->main->maindiskname = "{$name}_rootimg";
		$this->main->swapdiskname = "{$name}_vmswap";
		$this->initXenVars();
		$this->createDisk();
		$this->setSwapUsage();
		$this->createSwap();
		//$this->mount_this_guy();
	
		if (char_search_beg($this->__oldlocation, "lvm:")) {
			$vgname = fix_vgname($this->__oldlocation);
			$oldimage = "/dev/$vgname/{$this->main->maindiskname}";
		} else {
			$oldimage = "{$this->__oldlocation}/{$this->main->nname}/root.img";
		}
	
		$ret = lxshell_return("dd", "if=$oldimage", "of={$this->main->maindisk}");
		if ($ret) {
			throw new lxException("could_not_clone");
		}
	
	
		// Don't do this at all. The saved space is not going to be very important for the short period.
		//lunlink("$this->__oldlocation/{$this->main->nname}/root.img");
		/*
		if (char_search_beg($this->__oldlocation, "lvm:")) {
			$vg = fix_vgname($this->__oldlocation);
			lvm_remove("/dev/$vg/{$this->main->swapdiskname}");
		} else {
			lunlink("$this->__oldlocation/{$this->main->nname}/vm.swap");
		}
		*/
		$this->start();
	
		$ret = array("__syncv_corerootdir" => $this->main->newlocation, "__syncv_maindiskname" => $this->main->maindiskname, "__syncv_swapdiskname" => $this->main->swapdiskname);
		return $ret;
	
	}

	public function saveXen()
	{
		if (self::getStatus($this->main->nname, self::XEN_HOME) !== 'on') {
			return null;
		}
		$tmp = lx_tmp_file("{$this->main->nname}_ram");
		lxshell_return("xm", "save", $this->main->nname, $tmp);
		return $tmp;
	}

	public function restoreXen($file)
	{
		if (!$file) {
			return;
		}
		lxshell_return("xm", "restore", $file);
	}

	public function do_backup()
	{
		global $gbl, $sgbl, $login, $ghtml; 
	
		$tmpbasedir = $this->main->__var_tmp_base_dir;
	
		if ($this->isLVM()) {
			//$file = $this->saveXen();
	
			if ($this->main->isOn('__var_bc_backupextra_stopvpsflag')) {
				$this->stop();
			}
	
			try {
				$mountpoint = $this->takeSnapshot();
			} catch (Exception $e) {
				//$this->restoreXen($file);
				$this->start();
				throw $e;
			}
	
	
			if ($this->main->isOn('__var_bc_backupextra_stopvpsflag')) {
				$this->start();
			}
	
			$this->main->__snapshotmount = $mountpoint;
			$this->main->__save_variable['__snapshotmount'] = $mountpoint;
	
		} else {
			$this->stop();
			$mountpoint = $this->mount_this_guy();
		}
	
		if (!$this->main->isWindows()) {
			$mountpoint = expand_real_root($mountpoint);
			$list = lscandir_without_dot($mountpoint);
			$list = array_remove($list, "proc");
			if (count($list) < 6) {
				throw new lxException("not_enough_directories_in_vps_root,_possibly_wrong_location", '', '');
			}
	
		} else {
			$tmpdir = createTempDir($tmpbasedir, "lx_{$this->main->nname}_backup");
			$vgname = fix_vgname($this->main->corerootdir);
			$snapshot = "/dev/$vgname/{$this->main->nname}_snapshot";
			$partition = get_partition($snapshot, $this->main->corerootdir);
			lxshell_return("ntfsfix", $partition);
			$ret = lxshell_return("ntfsclone", "--force", "--save-image", "-O", "$tmpdir/backup.img", $partition);
			if ($ret) { 
				kpart_remove("/dev/mapper/$vgname-{$this->main->nname}_snapshot");
				lvm_remove($snapshot);
				throw new lxException("could_not_clone");
			}
			kpart_remove("/dev/mapper/$vgname-{$this->main->nname}_snapshot");
			$list = array("backup.img");
			$mountpoint = $tmpdir;
			$this->main->__windows_tmpdir = $tmpdir;
			lvm_remove($snapshot);
		}
			
		return array($mountpoint, $list);
	}

	public function do_backup_cleanup($bc)
	{
		// I had commented out the starting of the vps after backup. I don't know why. Why is this not done.. The vps should be started after the backup is done.
		$mountpoint = "{$this->main->configrootdir}/mnt";
	
		if ($this->main->isWindows()) {
			lxfile_rm("{$this->main->__windows_tmpdir}/backup.img");
			lxfile_rm($this->main->__windows_tmpdir);
			return;
		}
		if ($this->isLVM()) {
			lxshell_return("umount", $this->main->__snapshotmount);
			lxfile_rm($this->main->__snapshotmount);
			$vglocation = fix_vgname($this->main->corerootdir);
			$snapshotlvm = "/dev/$vglocation/{$this->main->nname}_snapshot";
			lvm_remove($snapshotlvm);
		} else {
			$this->start();
		}
	}

	public function do_restore($docd)
	{
		global $gbl, $sgbl, $login, $ghtml;
	
		$this->hardstop();
		$this->createDisk();
	
		$tmpbasedir = $this->main->__var_tmp_base_dir;
	
		if ($this->checkForSnapshot()) {
			lvm_remove($this->getSnapshotName());
			if ($this->checkForSnapshot()) {
				throw new lxException("snapshot_for_this_exists_and_coudnt_remove");
			}
		}
	
		if (!$this->main->isWindows()) {
			$mountpoint = $this->mount_this_guy();
			lxshell_unzip_numeric_with_throw($mountpoint, $docd);
			//lxshell_return("tar", "-C", "$mountpoint/dev", "-xzf", "__path_program_root/file/vps-dev.tgz");
	
			if ($this->main->__old_driver !== 'xen') {
				log_restore("Restoring {$this->main->nname} from a different driver {$this->main->__old_driver} to xen");
	
				/*
				if (!lxfile_exists("__path_program_home/xen/template/{$this->main->ostemplate}.tar.gz")) {
					throw new lxException("migrating_from_{$this->main->__old_driver}_needs_osImage");
			}
			*/
				//lxshell_return("tar", "-C", $mountpoint, "-xzf", "__path_program_home/xen/template/{$this->main->ostemplate}.tar.gz", "etc/rc.d", "sbin", "etc/hotplug.d", "etc/dev.d", "etc/udev", "lib", "usr", "bin", "etc/inittab", "etc/sysconfig");
				//lxshell_return("tar", "-C", $mountpoint, "-xzf", "__path_program_home/xen/template/{$this->main->ostemplate}.tar.gz", "etc/rc.d", "sbin", "etc/hotplug.d", "etc/dev.d", "etc/udev", "lib", "usr", "bin", "etc/inittab");
				lxfile_cp("../file/sysfile/xen/fstab", "$mountpoint/etc/fstab");
				lxfile_cp("__path_program_root/file/sysfile/xen/inittab", "$mountpoint/etc/inittab");
				lunlink("$mountpoint/etc/mtab");
				lunlink("$mountpoint/etc/init.d/vzquota");
				$this->copyKernelModules();
			}
	
			lxfile_mkdir("$mountpoint/proc");
			$this->createConfig();
			$this->setMemoryUsage();
			$this->setCpuUsage();
			$this->setSwapUsage();
		} else {
			$tmpdir = createTempDir($tmpbasedir, "lx_{$this->main->nname}_backup");
			lxshell_unzip_with_throw($tmpdir, $docd);
			$partition = $this->getPartition();
			lxshell_return("ntfsclone", "--restore-image", "--force", "-O", $partition, "$tmpdir/backup.img");
			lxfile_tmp_rm_rec("$tmpdir");
			$this->kpart_remove();
		}
	
		$this->main->status = 'on';
	
		try {
			$this->toggleStatus();
		} catch (Exception $e) {
		}
	
		$this->start();
	
		// Saving state doesn't seem to be an option. The thing is, it is the file system itself that's left in an inconsistent state, and there's little we can do about it.
	
		/*
		$statefile = "$mountpoint/__hypervm_xensavestate";
		if (lxfile_exists($statefile)) {
			$tmp = lx_tmp_file("/tmp", "xen_ram");
			lxfile_mv($statefile, $tmp);
			$this->umountThis();
			$this->restoreXen($tmp);
			lunlink($tmp);
		} else {
			$this->start();
		}
	*/
	}

	public function setCpuUsage()
	{
		if ($this->isUnlimited($this->main->priv->cpu_usage)) {
			$cpu = "100" * os_getCpuNum();;
		} else {
			$cpu = $this->main->priv->cpu_usage;
		}
		lxshell_return("xm", "sched-credit", "-d", $this->main->nname, "-c", $cpu);
	}

	public function setMemoryUsage()
	{
		if ($this->isUnlimited($this->main->priv->realmem_usage)) {
			$memory = 512;
		} else {
			$memory = $this->main->priv->realmem_usage;
		}
	
		$this->createConfig();
		lxshell_return("xm", "mem-set", $this->main->nname, $memory);
		lfile_put_contents("{$this->main->configrootdir}/memory.value", $memory);
	}

	public function setSwapUsage()
	{
	
		if ($this->main->isWindows()) {
			return;
		}
	
		if ($this->isUnlimited($this->main->priv->swap_usage)) {
			$memory = 512;
		} else {
			$memory = $this->main->priv->swap_usage;
		}
	
		lfile_put_contents("{$this->main->configrootdir}/swap.value", $memory);
	}

	public function setDiskUsage()
	{
		if ($this->isUnlimited($this->main->priv->disk_usage)) {
			$diskusage = 3 * 1024;
		} else {
			$diskusage = $this->main->priv->disk_usage ;
		}
	
		lfile_put_contents("{$this->main->configrootdir}/disk.value", $diskusage);
	}

	public function reboot()
	{
		global $global_shell_out, $global_shell_error, $global_shell_ret;
		$this->stop();
		$ret = $this->start();
	
		if ($ret) {
			throw new lxException("could_not_start_vps", '', str_replace("\n", ": ", $global_shell_error));
		}
	}

	public function rebuild()
	{
		if (!$this->main->isOn('rebuild_confirm_f')) {
			throw new lxException("need_confirm_rebuild", 'rebuild_confirm_f');
		}
	
	
		if ($this->main->isWindows()) {
			$templatefile = "__path_program_home/xen/template/{$this->main->ostemplate}";
		} else {
			$templatefile = "__path_program_home/xen/template/{$this->main->ostemplate}.tar.gz";
		}
	
		if(!lxfile_nonzero($templatefile)) {
			$this->main->getOsTemplateFromMaster($templatefile);
		}
	
		if (!lxfile_nonzero($templatefile)) {
			throw new lxException("no_template_and_could_not_download", 'rebuild_confirm_f');
		}
	
		$this->stop();
	
		if ($this->main->isNotWindows()) {
			$mountpoint = $this->mount_this_guy();
			if ($this->main->isOn('rebuild_backup_f')) {
				lxfile_mkdir("/home/hypervm/vps/{$this->main->nname}/__backup/");
				$date = date('Y-m-d-') . time();
				$dir = "/home/hypervm/vps/{$this->main->nname}/__backup/rebuild-backup.$date";
				lxfile_cp_rec($mountpoint, $dir);
			}
		}
	
		$this->umountThis();
	
		if ($this->main->isNotWindows()) {
			if ($this->isLvm()) {
				lxshell_return("mkfs.ext3", "-F", $this->main->maindisk);
			} else {
				lxfile_rm_rec($this->main->maindisk);
				$this->createDisk();
			}
	
			$mountpoint = $this->mount_this_guy();
			$ret = lxshell_return("tar", "-C", $mountpoint, '--numeric-owner', "-xpzf", $templatefile);
	
			if ($ret) {
				throw new lxException("rebuild_failed_could_not_untar");
			}
		} else {
			$templatefile = expand_real_root($templatefile);
			lxshell_return("parted", "-s", $this->main->maindisk, "mklabel", "msdos");
			$this->runParted();
			$partition = $this->getPartition();
			//lxshell_return("dd", "if=$templatefile", "of={$this->main->maindisk}");
			lxshell_return("ntfsclone", "--restore-image", "-O", $partition, $templatefile);
			$this->kpart_remove();
			$this->expandPartitionToImage();
		}
	
	
		$this->start();
	}

	public function installkloxo()
	{
		$this->rebuild();
	}

	public function recoverVps()
	{
		if (!$this->main->isOn('recover_confirm_f')) {
			throw new lxException("need_confirm_recover", 'recover_confirm_f');
		}
		$this->stop();
		$mountpoint = $this->mount_this_guy();
		$this->main->coreRecoverVps($mountpoint);
		$this->start();
	}

	public function setInformation()
	{
		//lxshell_return("vzctl", "set", $this->main->vpsid, "--hostname", $this->main->hostname);
	}

	public function createTemplate()
	{
		$stem = explode("-", $this->main->ostemplate);
		if ($this->main->isWindows()) {
			$name = "{$stem[0]}-";
		} else {
			$name = "{$stem[0]}-{$stem[1]}-{$stem[2]}-";
		}
	
	
		$templatename = "$name{$this->main->newostemplate_name_f}";
		if ($this->main->isWindows()) {
			$tempfpath = "__path_program_home/xen/template/$templatename.img";
		} else {
			$tempfpath = "__path_program_home/xen/template/$templatename.tar.gz";
		}
	
	
		$this->stop();
	
		if ($this->main->isWindows()) {
			$partition = $this->getPartition();
			lxshell_return("ntfsfix", $partition);
			lxshell_return("ntfsclone", "--save-image", "--force", "-O", $tempfpath, $partition);
			$this->kpart_remove();
		} else {
			$list = lscandir_without_dot("{$this->main->configrootdir}/mnt");
			$ret = lxshell_return("tar", "-C", "{$this->main->configrootdir}/mnt", '--numeric-owner', "-czf", $tempfpath, $list);
		}
		$this->start();
	
		$filepass = cp_fileserv($tempfpath);
		$ret = array("__syncv___ostemplate_filepass" => $filepass, "__syncv___ostemplate_filename" => basename($tempfpath));
		return $ret;
	
	}

	public function hardstop()
	{
		if (self::getStatus($this->main->nname, self::XEN_HOME) !== 'on') {
			//$this->mount_this_guy();
			return;
		}
	
		lxshell_return("xm", "shutdown", $this->main->nname);
	
		$count = 0;
		while (self::getStatus($this->main->nname, self::XEN_HOME) === 'on') {
			$count++;
			sleep(5);
			if ($count === 3) {
				lxshell_return("xm", "destroy", $this->main->nname);
				break;
			}
		}
	
		while (self::getStatus($this->main->nname, self::XEN_HOME) === 'on') {
			sleep(5);
		}
	
		usleep(100 * 1000);
		sleep(10);
	}

	public function stop()
	{
		if (self::getStatus($this->main->nname, self::XEN_HOME) !== 'on') {
			//$this->mount_this_guy();
			return;
		}
	
		lxshell_return("xm", "shutdown", $this->main->nname);
	
		sleep(40);
	
		if (self::getStatus($this->main->nname, self::XEN_HOME) === 'on') {
			lxshell_return("xm", "destroy", $this->main->nname);
		}
	
	
		sleep(3);
	
		if (self::getStatus($this->main->nname, self::XEN_HOME) === 'on') {
			throw new lxException("could_not_stop_vps");
		}
	
		$this->mount_this_guy();
	}

	public function isMounted()
	{
		$mountpoint = "{$this->main->configrootdir}/mnt";
		$mountpoint = expand_real_root($mountpoint);
		$cont = lfile_get_contents("/proc/mounts");
		if (csa($cont, $mountpoint)) {
			return true;
		}
		dprint("$mountpoint is not in /proc/mounts\n");
		return false;
	}

	public function umountThis()
	{
		$mountpoint = "{$this->main->configrootdir}/mnt";
		lxshell_return("sync");
		$count = 0;
		while (true) {
			$count++;
			if ($count > 10) {
				throw new lxException("cannot_unmount_after_10_attempts");
			}
			if (!$this->isMounted()) {
				break;
			}
	
			$ret = lxshell_return("umount", $mountpoint);
			if ($ret) {
				//lxshell_return("umount", "-l", $mountpoint);
				throw new lxException("umounting_file_system_failed");
			}
		}
	}

	public function checkForSnapshot()
	{
		if ($this->isLvm()) {
			if (lxfile_exists($this->getSnapshotName())) {
				log_log("critical_snapshot", "Found snapshot for {$this->main->nname} removing...");
				return true;
			}
		}
	
		return false;
	}

	public function getSnapshotName()
	{
		$vgname = fix_vgname($this->main->corerootdir);
		$snap = "/dev/$vgname/{$this->main->nname}_snapshot";
		return $snap;
	}

	public function start() 
	{
	
		if (self::getStatus($this->main->nname, self::XEN_HOME) === 'on') {
			return;
		}
	
		$this->createConfig();
		$this->createSwap();
		$this->setvif();
		$this->resizeRootImage();
	
		if ($this->checkForSnapshot()) {
			//lvm_remove($this->getSnapshotName());
		}
	
		if (!$this->main->isWindows()) {
			$mountpoint = $this->mount_this_guy();
			$this->setInternalParam($mountpoint);
			$this->copyKernelModules();
			$this->umountThis();
		}
	
		return lxshell_return("xm", "create", "{$this->main->configrootdir}/{$this->main->nname}.cfg"); 
	}

	public function setInternalParam($mountpoint)
	{
		$name = $this->main->ostemplate;
	
		if ($this->main->isWindows()) { return; } 
	
		if (!$mountpoint) { return; }
	
		if ($name === 'unknown') { return; }
	
	
	
		$name = strtolower($name);
		$mountpoint = expand_real_root($mountpoint);
		$result = $this->getScriptS($name);
		dprint("Distro Name $name, Scripts: \n");
		dprintr($result);
	
		$init = strtilfirst($name, "-");
	
		dprint("File is  $init.inittab\n");
		if (lxfile_exists("../file/sysfile/inittab/$init.inittab")) {
			dprint("Copying $init.inittab\n");
			$content = lfile_get_contents("../file/sysfile/inittab/$init.inittab");
			if ($this->main->text_inittab) {
				$content .= "\n{$this->main->text_inittab}";
			}
			lfile_put_contents("$mountpoint/etc/inittab", $content);
		}
	
		$iplist = get_namelist_from_objectlist($this->main->vmipaddress_a);
		if ($this->main->mainipaddress) {
			$main_ip = $this->main->mainipaddress;
			$iplist = array_remove($iplist, $main_ip);
		} else {
			$main_ip = array_shift($iplist);
		}
	
		if ($this->main->networknetmask) {
			$main_netmask = $this->main->networknetmask;
		} else {
			$main_netmask = "255.255.255.0";
		}
	
		$iplist = implode(" ", $iplist);
	
		$ipadd = $result['ADD_IP'];
		$sethostname = $result['SET_HOSTNAME'];
		$setuserpass = $result['SET_USERPASS'];
		$ipdel = $result['DEL_IP'];
	
	        if ($this->main->networkgateway) {
	                $gw = $this->main->networkgateway;
	        } else {
	                $gw = os_get_network_gateway();
	        }
	
	        $gwn = strtil($gw, '.') . '.0';
	
	        $hostname = $this->main->hostname;
	        if (!$hostname) { $hostname = os_get_hostname(); }
	
		if ($result['STARTUP_SCRIPT'] != 'systemd'){
	            $name = createTempDir("$mountpoint/tmp", 'xen-scripts');
		    lxfile_cp_rec("__path_program_root/bin/xen-dists/scripts/functions", $name);
	            lxfile_cp_rec("__path_program_root/bin/xen-dists/scripts/$ipadd", $name);
		    lxfile_cp_rec("__path_program_root/bin/xen-dists/scripts/$sethostname", $name);
	            lxfile_cp_rec("__path_program_root/bin/xen-dists/scripts/$setuserpass", $name);
	            lxfile_cp_rec("__path_program_root/bin/xen-dists/scripts/$ipdel", $name);
	
		    $basepath = strfrom($name, $mountpoint);
		    lfile_put_contents("$name/tmpfile.sh", "source /$basepath/functions\nsource /$basepath/$ipdel\n");
		    $delipstring = "IPDELALL=yes chroot $mountpoint bash /$basepath/tmpfile.sh";
	
	            log_shell($delipstring);
	            log_shell(system($delipstring,$ret1) . ":return $ret1");
	
		    putenv("VE_STATE=stopped");
		    lfile_put_contents("$name/tmpfile.sh", "source /$basepath/functions\n source /$basepath/$ipadd\n");
		    $string = "IPDELALL=yes MAIN_NETMASK=$main_netmask MAIN_IP_ADDRESS=$main_ip IP_ADDR=\"$iplist\" NETWORK_GATEWAY=$gw NETWORK_GATEWAY_NET=$gwn chroot $mountpoint bash /$basepath/tmpfile.sh";
	
		    log_shell($string);
		    log_shell(system($string, $ret1) . ":return $ret1");
	
		    lfile_put_contents("$name/tmpfile.sh", "source /$basepath/functions\n source /$basepath/$sethostname\n");
		    $string = "HOSTNM=$hostname chroot $mountpoint bash /$basepath/tmpfile.sh";
		    log_shell($string);
		    log_shell(system($string,$ret1).":return $ret1");
	
		    if (($this->main->subaction === 'rebuild') || ($this->main->dbaction === 'add') || ($this->main->isOn('__var_rootpassword_changed') && $this->main->rootpassword)) {
			$rootpass = "root:{$this->main->rootpassword}";
			lfile_put_contents("$name/tmpfile.sh", "source /$basepath/functions\n source /$basepath/$setuserpass\n");
			$string = "USERPW=$rootpass chroot $mountpoint bash /$basepath/tmpfile.sh";
			log_shell($string);
			log_shell(system($string));
		    }
			
			lxfile_rm_rec($name);
		}
		else if ($result['STARTUP_SCRIPT'] == 'systemd'){
			$script_dir = createTempDir("$mountpoint", "hypervm-runonce");
			lxfile_cp_rec("__path_program_root/bin/xen-dists/scripts/functions", $script_dir);
			lxfile_cp_rec("__path_program_root/bin/xen-dists/scripts/$ipadd", $script_dir);
			lxfile_cp_rec("__path_program_root/bin/xen-dists/scripts/$sethostname", $script_dir);
	        lxfile_cp_rec("__path_program_root/bin/xen-dists/scripts/$setuserpass", $script_dir);
			lxfile_cp_rec("__path_program_root/bin/xen-dists/scripts/$ipdel", $script_dir);
			$basepath = strfrom($script_dir, $mountpoint);
			$startupdir = 'lib/systemd/system';
			$startupscript = 'fedora-startup.service';
	
			$setrootpass = '';
			if (($this->main->subaction === 'rebuild') || ($this->main->dbaction === 'add') || ($this->main->isOn('__var_rootpassword_changed') && $this->main->rootpassword)) {
				$rootpass = "root:{$this->main->rootpassword}";
				$setrootpass = " & USERPW=$rootpass source $basepath/$setuserpass";
			}
	
			$run_once_script = "#!/bin/bash\n" .
				"source $basepath/functions\n" .
				'(' .
				"IPDELALL=yes source $basepath/$ipdel" .
				" & IPDELALL=yes VE_STATE=stopped MAIN_NETMASK=$main_netmask MAIN_IP_ADDRESS=$main_ip IP_ADDR=\"$iplist\" NETWORK_GATEWAY=$gw NETWORK_GATEWAY_NET=$gwn source $basepath/$ipadd" .
				" & HOSTNM=$hostname source $basepath/$sethostname" .
				"$setrootpass)\n" .
				"service fedora-startup disable\nrm -f /$startupdir/$startupscript\nrm -rf $basepath";
			lfile_put_contents("$script_dir/hypervm-runonce.sh", $run_once_script);
	
			lxfile_cp_rec("__path_program_root/bin/xen-dists/scripts/$startupscript", "$mountpoint/$startupdir");
			lfile_put_contents("$mountpoint/$startupdir/$startupscript", 
				lfile_get_contents("$mountpoint/$startupdir/$startupscript") .
				"ExecStart=$basepath/hypervm-runonce.sh\n");
			system("ln -s /lib/systemd/system/fedora-startup.service $mountpoint/etc/systemd/system/multi-user.target.wants/fedora-startup.service");
			system("chmod 755 $script_dir/hypervm-runonce.sh");
		}
	
		if ($this->main->nameserver) {
			$nlist = explode(" ", $this->main->nameserver);
			$nstring = null;
			foreach($nlist as $l) {
				$nstring .= "nameserver $l\n";
			}
			lfile_put_contents("$mountpoint/etc/resolv.conf", $nstring);
		}
	
		if ($this->main->timezone) {
			lxfile_rm("$mountpoint/etc/localtime");
	
			$cmdstring = "ln -sf ../usr/share/zoneinfo/{$this->main->timezone} $mountpoint/etc/localtime";
			log_log("localtime", $cmdstring);
			do_exec_system('__system__', "/", $cmdstring, $out, $err, $ret, null);
			//lxfile_cp("/usr/share/zoneinfo/{$this->main->timezone}", "$mountpoint/etc/localtime");
		}
	
		lunlink("$mountpoint/etc/sysconfig/network-scripts/ifcfg-venet0");
		lunlink("$mountpoint/etc/sysconfig/network-scripts/ifcfg-venet0:0");
	
		$this->main->doKloxoInit($mountpoint);
	}

	public function getScriptS($name)
	{
		$v = $name;
		while (true) {
			$v = strtil($v, "-");
			if (!$v) {
				$v = 'default';
				break;
			}
			dprint("Checking for conf $v\n");
			if (lxfile_exists("__path_program_root/bin/xen-dists/$v.conf")) {
				break;
			}
	
			if (!csa($v, "-")) {
				$v = "default";
				break;
			}
	
		}
		$file = "__path_program_root/bin/xen-dists/$v.conf";
		$list = lfile_trim($file);
	
		foreach($list as $l) {
			if (char_search_beg($l, "#")) {
				continue;
			}
	
			if (csa($l, "=")) {
				$v = explode("=", $l);
				$result[$v[0]] = $v[1];
			}
		}
		return $result;
	}

	public function changeUserPassword()
	{
		$pass = $this->main->password;
		lxshell_return("usermod", "-p", $pass, $this->main->username);
	}

	public function dbactionUpdate($subaction)
	{
	
		global $gbl, $sgbl, $login, $ghtml; 
	
	
	
		switch($subaction) {
			case "changelocation":
				return $this->changelocation();
				break;
	
			case "rebuild":
				$this->rebuild();
				break;
	
			case "recovervps":
				$this->recovervps();
				break;
	
	
			case "mount":
				$this->mount_this_guy();
				break;
	
			case "createuser":
				return $this->main->syncCreateUser();
				break;
	
	
			case "full_update":
				$this->setDiskUsage();
				$this->setMemoryUsage();
				$this->setCpuUsage();
				$this->setSwapUsage();
				$this->toggleStatus();
				break;
	
	
			case "password":
				$this->changeUserPassword();
				break;
	
			case "createtemplate":
				return $this->createTemplate();
				break;
	
			case "disable":
			case "enable":
			case "toggle_status":
				$this->toggleStatus();
				break;
	
	
			case "change_disk_usage":
				$this->setDiskUsage();
				break;
	
			case "change_realmem_usage":
				$this->setMemoryUsage();
				break;
	
	
			case "change_swap_usage":
				$this->setSwapUsage();
				break;
	
			case "change_process_usage":
				//$this->setProcessUsage();
				break;
				
			case "rootpassword":
				$this->setRootPassword();
				break;
	
			case "installkloxo":
				$this->installKloxo();
				break;
	
			case "network":
			case "information":
				$this->setInformation();
				$this->setDhCP();
				break;
	
			case "add_vmipaddress_a":
				$this->setDhCP();
				break;
	
			case "delete_vmipaddress_a":
				$this->setDhCP();
				break;
	
			case "boot":
				$this->start();
				break;
	
			case "poweroff":
				$this->stop();
				$this->mount_this_guy();
				break;
	
	
			case "reboot":
				$this->reboot();
				break;
	
			case "change_cpu_usage":
				$this->setCpuUsage();
				break;
	
			case "hardpoweroff":
				$this->hardstop();
				break;
	
	
			case "createconfig":
				$this->createConfig();
				$ret = lxfile_symlink("{$this->main->configrootdir}/{$this->main->nname}.cfg", "/etc/xen/auto");
				break;
	
			case "graph_traffic":
				return rrd_graph_vps("traffic", "xen-{$this->main->vifname}.rrd", $this->main->rrdtime);
				break;
	
			case "graph_cpuusage":
				return rrd_graph_vps("cpu", "{$this->main->nname}.rrd", $this->main->rrdtime);
				break;
	
	
		}
	}

	public function setDhCP()
	{
		if (!$this->main->isWindows()) {
			return;
		}
	
		$this->main->iplist = get_namelist_from_objectlist($this->main->vmipaddress_a);
		$res = merge_array_object_not_deleted($this->main->__var_win_iplist, $this->main);
		dhcp__dhcpd::createDhcpConfFile($res);
	}

	public function setProcessUsage()
	{
	}

	public static function getCompleteStatus($list)
	{
		foreach($list as $l) {
			$virtual_machine_name = $l['nname'];
			$root_dir             = self::XEN_HOME;
			
			$r['status'] = self::getStatus($virtual_machine_name, $root_dir);
			
			$disk_name  = $l['diskname'];
			$is_windows = $l['winflag'];
			$root_path  = $l['corerootdir'];
			
			$disk = self::getDiskUsage($disk_name, $is_windows, $root_path);
			
			$r['ldiskusage_f'] = $disk['used'];
			$res[$l['nname']] = $r;
		}
		
		return $res;
	}
}