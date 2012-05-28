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
* BaseUnitTest class file.
*
* Create a solid base test for PHPUnit inherit.
*
* @copyright 2012, (c) LxCenter.
* @license AGPLv3 http://www.gnu.org/licenses/agpl-3.0.en.html
* @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
* @version v1.0 20120218 build
* @package Test 
*/
class BaseUnitTest extends PHPUnit_Framework_TestCase
{
	const DEBUG = FALSE;
	
	/**
	* @var array temporary variable for globals array
	*/
	protected $tmpGlobals;
	
	/**
	* @var array temporary variable for session array
	*/
	protected $tmpSession;
	
	/**
	 * Execute at the begin of test.
	 * 
	 * @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
	 * 
	 * @return void
	 */
	public function setUp() 
	{
		parent::setUp();
		
		//session_start();
		
		// Store globals and session
		//$this->tmpGlobals = $GLOBALS;
		//$this->tmpSession = $_SESSION;
	}
	
	/**
	 * Execute at the end of test.
	 * 
	 * @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
	 * 
	 * @return void
	 */
	public function tearDown()
	{
		parent::tearDown();
		
		// Recover globals and session
		//$GLOBALS  = $this->tmpGlobals;
		//$_SESSION = $this->tmpSession;
		
		//session_destroy();
	}
}