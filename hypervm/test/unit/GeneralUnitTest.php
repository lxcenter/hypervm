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
*/

require 'BaseUnitTest.php';

/**
* GeneralUnitTest class file.
*
* Test the most general aspects.
*
* @copyright 2012, (c) LxCenter.
* @license AGPLv3 http://www.gnu.org/licenses/agpl-3.0.en.html
* @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
* @version v1.0 20120218 build
* @package Test 
*/
class GeneralUnitTest extends BaseUnitTest
{
	const BRAND_PATH = '/usr/local/lxlabs';
	const PRODUCT_PATH = '/usr/local/lxlabs/hypervm';
	
	/**
	 * Test the php version used.
	 * 
	 * @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
	 * 
	 * @return void
	 * @test
	 */
	public function phpVersion()
	{
		$version_status = version_compare('5.3', phpversion(), '<=');
		
		$this->assertTrue($version_status, 'HyperVM requires PHP 5.3 or above');
		//$this->markTestIncomplete('Until provide a rpm package from lxphp 5.3');
	}
	
	/**
	 * Test if the brand path is set.
	 * 
	 * @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
	 * 
	 * @return void
	 * @test
	 */
	public function brandPath()
	{
		$this->assertTrue(file_exists(self::BRAND_PATH), self::PRODUCT_PATH . ' does not exist' . 'does not exist');
	}
	
	/**
	* Test if the product path is set.
	*
	* @author Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
	*
	* @return void
	* @test
	*/
	public function productPath()
	{
		$this->assertTrue(file_exists(self::PRODUCT_PATH), self::PRODUCT_PATH . ' does not exist');
	}
}