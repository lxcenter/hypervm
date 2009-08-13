<?php 

class ostemplate_xen extends  ostemplate {
}

class ostemplate extends lxclass {

static $__desc = array("", "",  "ostemplate");
static $__acdesc_show = array("", "",  "ostemplate");

static function initThisObjectRule($parent, $class, $name = null) { return $parent->nname ; }

function get() {}
function write() {}

function createShowPropertyList(&$alist)
{
	$alist['property'][] = 'a=show';
	$alist['property'][] = 'a=show&l[class]=ffile&l[nname]=/';
}

function getFfileFromVirtualList($name)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$parent = $this->getParentO();

	if ($this->isClass('ostemplate_xen')) {
		$path = "d";
	}

	$root = "$gen->rootdir/vps/$parent->ttype/$parent->nname/";

	$name = coreFfile::getRealpath($name);
	$name = "/$name";

	$ffile= new Ffile(null, $server, $root, $name, "root");
	$ffile->__parent_o = $this;
	$ffile->get();
	$ffile->browsebackup = 'on';
	return $ffile;
}
}
