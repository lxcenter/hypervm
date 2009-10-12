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
class custombutton extends lxdb {

static $__desc = array("", "",  "custom_button");
static $__desc_nname = array("", "",  "name", "a=show");
static $__desc_description = array("", "",  "description", "a=show");
static $__desc_class = array("", "",  "class");
static $__desc_upload = array("F", "",  "upload");
static $__desc_image = array("F", "",  "image");
static $__desc_url = array("", "",  "url");
static $__acdesc_update_update = array("", "",  "update");


static function add($parent, $class, $param)
{
	//$param['nname'] = incrementVar($class, "nname", 1, 1);
	return $param;
}

function createShowUpdateform()
{
	$alist["update"] = null;
	return $alist;
}

static function createListNlist($parent, $view)
{
	$nlist['nname'] = '10%';
	$nlist['description'] = '100%';
	return $nlist;
}

function updateUpdate($param)
{
	lxfile_mkdir("img/custom");

	if ($_FILES['upload']['tmp_name']) {
		lxfile_rm("img/custom/{$this->nname}.gif");
		lxfile_mv($_FILES['upload']['tmp_name'], "img/custom/{$this->nname}.gif");
		lxfile_generic_chmod("img/custom/{$this->nname}.gif", "0755");
	}
	return $param;
}

function updateform($subaction, $param)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$list = array("client"); if ($sgbl->isHyperVm()) { $list = lx_array_merge(array($list, array("vps"))); }
	$vlist['class'] = array('s', $list);
	$vlist['description'] = null;
	$vlist['url'] = null;
	$ipt = "img/custom/{$this->nname}.gif";
	if (lxfile_exists($ipt)) {
		$vlist['image'] = array('I', array('width' => 20, 'height' => 20, 'value' => $ipt));
	} else {
		$vlist['image'] = array('M', "No Image");
	}
	$vlist['upload'] = array('F', null);
	return $vlist;
}

static function addform($parent, $class, $typetd = null)
{

	global $gbl, $sgbl, $login, $ghtml; 
	$list = array("client"); if ($sgbl->isHyperVm()) { $list = lx_array_merge(array($list, array("vps"))); }

	$vlist['nname'] = null;
	$vlist['description'] = null;
	$vlist['class'] = array('s', $list);
	$vlist['url'] = array('m', "http://");
	$ret['action'] = 'add';
	$ret['variable'] = $vlist;
	return $ret;
}



}

