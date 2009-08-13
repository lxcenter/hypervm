<?php 

class openvzostemplate extends lxclass {

static $__desc = array("PN", "",  "file");

function get() {}
function write() {}

function getFfileFromVirtualList($name)
{

	//$root = "/home/hypervm/xen/template/";
	$root = "/vz/template/cache/";
	$ffile= new Ffile(null, 'localhost', $root, $name, "root");
	$ffile->__parent_o = $this;
	$ffile->__var_extraid = "template";
	$ffile->ostemplate = 'on';
	$ffile->get();
	return $ffile;
}

static function initThisObjectRule($parent, $class, $name = null) { return null ; }
static function initThisObject($parent, $class, $name = null) 
{ 
	
	$ob = new openvzostemplate(null, null, $name);
	return $ob;

}

}
