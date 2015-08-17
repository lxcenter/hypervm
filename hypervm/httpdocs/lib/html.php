<?php

include_once "htmllib/htmllib.php" ;

class Html extends Htmllib {

function __construct()
{
	parent::__construct();

}

function login_lpanel($object)
{
	$this->do_login_lpanel("", $object);
}

function ifRemote()
{
	if ($gbl->__var_remote == "yes") {
		return true;
	}
	return false;
}



function mymenus($header)
{
	global $gbl, $login, $ghtml; 

	//if (!$header) { return; }
?>
<script>

    //menu objects


function Menu(label,msize) {
//    this.version = "020903 [Menu; menu.js]";

    this.type = "Menu";
    this.fontSize = 12;
    this.fontWeight = "normal";
    this.fontFamily = "Arial, Verdana";
    this.fontColor = "#003366";
    this.fontColorHilite = "#000000";
    this.bgColor = "#555555";
    this.size=msize;
    this.menuBorder = 1;
    this.menuItemBorder = 1;
    this.menuItemBgColor = "#aaaaaa";
    this.menuLiteBgColor = "#ffffff";
    this.menuBorderBgColor = "#777777";
	this.menuHiliteBgColor = "#e6eaed";
    this.menuContainerBgColor = "#dbeefd";
 

    //this.childMenuIcon = "/img/bullet.gif";
    //this.childMenuIconHilite = "/img/bullet.gif";
    this.items = new Array();
    this.actions = new Array();
    this.colors = new Array();
    this.mouseovers = new Array();
    this.mouseouts = new Array();
    this.childMenus = new Array();

    this.addMenuItem = addMenuItem;
    this.addMenuSeparator = addMenuSeparator;
    this.writeMenus = writeMenus;
    this.showMenu = showMenu;
    this.onMenuItemOver = onMenuItemOver;
    this.onMenuItemOut = onMenuItemOut;
    this.onMenuItemDown = onMenuItemDown;
    this.onMenuItemAction = onMenuItemAction;
	//this.enableHideOnMouseOut = true;
    this.hideMenu = hideMenu;
    this.hideChildMenu = hideChildMenu;
    this.mouseTracker = mouseTracker;
    this.setMouseTracker = setMouseTracker;
	//window.delayWriteMenus = true;

    if (!window.menus) window.menus = new Array();
    this.label = label || "menuLabel" + window.menus.length;
    window.menus[this.label] = this;
    window.menus[window.menus.length] = this;
    if (!window.activeMenus) window.activeMenus = new Array();
    if (!window.menuContainers) window.menuContainers = new Array();
    if (!window.mDrag) {
        window.mDrag    = new Object();
        mDrag.startMenuDrag = startMenuDrag;
        mDrag.doMenuDrag    = doMenuDrag;
        this.setMouseTracker();
    }
    if (window.MenuAPI) MenuAPI(this);
}



function loadMenus () {
	var frame1 = "top.mainframe.window.location=";

	<?php 


	$alist = $login->createShowAlist();
	$this->print_menulist("home", $alist, null, 'slist');

	/*
	$menul = $login->getMenuList();
	if ($menul) foreach($menul as $m) {
		$alist = exec_class_method($m, "createListAlist", $login, $m);
		$this->print_menulist($m, $alist, null, 'llist');
	}
*/

	if ($login->isAdmin()) {
		$pserver = $login->getFromList("pserver", "localhost");
		$alist = $pserver->createShowAlist();

		$frm_o_o[0]['class'] = 'pserver';
		$frm_o_o[0]['nname'] = 'localhost';

		$this->print_menulist('system', $alist, $frm_o_o, 'slist');
	}



	?> 
	window.help = new Menu("help",100);
	help.addMenuItem("Help","window.open(gl_helpUrl, 'Help')","0","helparea","0");
    help.addMenuItem("Forum","window.open('http://forum.lxcenter.org')","0","helparea","0");
	<?php 
   
	?> 
	}
	<?php 

	print("</script>");

} 




function printSelectObjectTable($name_list, $parent, $class, $blist = array(), $display = null)
{
	global $gbl, $sgbl, $login, $ghtml; 

	print_time("$class.objecttable");

    
	if ($this->frm_accountselect !== null) {
		$sellist = explode(',', $this->frm_accountselect);
	} else {
		$sellist = null;
	}

	$classdesc = $this->get_class_description($class, $display);
	$unique_name = trim($parent->nname) . trim($class) . trim($display) . trim($classdesc[2]);
 
	$unique_name = fix_nname_to_be_variable($unique_name);

	$filtername = $parent->getFilterVariableForThis($class);
	$fil = $login->getHPFilter();
	$sortdir = null;
	$sortby = null;
	if (isset($fil[$filtername]['sortby'])) {
		$sortby = $fil[$filtername]['sortby'];
	}
	if (isset($fil[$filtername]['sortdir'])) {
		$sortdir = $fil[$filtername]['sortdir'];
	}

	$pagesize = '99999';

	$iconpath = get_image_path() . "/button";

	$nlcount = count($name_list) + 1;
	$imgheadleft  = $login->getSkinDir() . "/top_lt.gif" ;
	$imgheadleft2  = $login->getSkinDir() . "/top_lt.gif" ;
	$imgheadright = $login->getSkinDir() . "/top_rt.gif" ;
	$imgheadbg    = $login->getSkinDir() . "/top_bg.gif" ; 
	$imgbtnbg  = $login->getSkinDir() . "/btn_bg.gif" ; 
	$imgtablerowhead  = $login->getSkinDir() . "/tablerow_head.gif" ; 
	$imgtablerowheadselect  = $login->getSkinDir() . "/top_line_medium.gif" ; 
	$imgbtncrv  = $login->getSkinDir() . "/btn_crv.gif" ; 
	$imgtopline  = $login->getSkinDir() . "/top_line.gif" ;


	$classdesc = $this->get_class_description($class);

	$unique_name = trim($parent->nname) . trim($class) . trim($classdesc[2]);
 
	$unique_name = fix_nname_to_be_variable($unique_name);

	//dprint("-- ".$unique_name. " --", 2);

?>
<br>
      <script> var ckcount<?php echo $unique_name; ?> ; </script>
<?php
	$tsortby = $sortby;
	if (!$sortby) {
		$tsortby = exec_class_method($class, "defaultSort");
	}
	if (!$sortdir) {
		$sortdir = exec_class_method($class, "defaultSortDir");
	}

	//print_time("objecttable");
	$obj_list = $parent->getVirtualList($class, $total_num, $tsortby, $sortdir);

	 
	//print_time("objecttable", 'objecttable');
	if (!$sellist)  {
		//$total_num = $this->display_count($obj_list, $display) ;
	}
	?>

	<table width=100%> <tr> <td align=center>
	<table cellspacing=2 cellpadding=2 width=97% align=center>
	<tr><td class=rowpoint></td><td colspan= <?php echo $nlcount; ?>>
	<table cellpadding=0 cellspacing=0 border=0 width=100%>
	<tr><td valign=bottom ></td>
	<td>
	<?php 
	if (isset($ghtml->__http_vars['frm_hpfilter'][$filtername]['pagenum'])) {
		$cgi_pagenum = $ghtml->__http_vars['frm_hpfilter'][$filtername]['pagenum'];
	} else {
		$cgi_pagenum = 1;
	}

	if (!$sellist) {
		$this->print_next_previous($parent, $class, "top", $cgi_pagenum, $total_num, $pagesize);
	}

	 
   	?>	
	</td>
	<td align=right valign=bottom >


	<?php 
	if (!$sellist) {
		?> 
			<table cellpadding=0 cellspacing=0 border=0 height=27 >

			<tr><td><img src="<?php echo $imgheadleft; ?>"></td><td nowrap valign=middle background="<?php echo $imgheadbg; ?>"><b><font color="#ffffff"><?php echo get_plural($classdesc[2])?> under <?php echo $parent->display("nname") ?> </b> <?php echo $this->print_machine($parent) ?> <b>  (<?php echo $total_num ?>)</b></font></td><td><img src="<?php echo $imgheadright; ?>"></td></tr>
			</table>			
			</td>
			</tr>


			<tr><td colspan=3><table cellpadding=0 cellspacing=0 border=0 width=100% height=35 background="<?php echo $imgbtnbg; ?>">
			<tr><td><img src="<?php echo $imgbtncrv; ?>"></td><td width=80% align=left > <table width=100% cellpadding=0 cellspacing=0 border=0><tr><td valign=bottom><?php $this->print_list_submit($class, $blist, $unique_name); ?></td></tr></table></td><td width=15% align=right><b><font color="#ffffff"><?php $this->print_search($parent, $class); ?></font></b></td></tr>
			</table>
			</td></tr>
	</td></tr><tr><td height=2 colspan=2></td></tr></table>

		<?php 
	} else {

		$descr = $this->getActionDescr($_SERVER['PHP_SELF'], $this->__http_vars, $class, $var, $identity);
		?> 
<table cellpadding=0 cellspacing=0 border=0 width=100%><tr><td width=70% valign=bottom><table cellpadding=0 cellspacing=0 border=0 width=100%><tr><td width=100% height=2 background="<?php echo $imgtopline; ?>"></td></tr></table></td><td align=right><table cellpadding=0 cellspacing=0 border=0 width=100% ><tr><td><img src="<?php echo $imgheadleft; ?>"></td><td nowrap width=100% background="<?php echo $imgheadbg; ?>" ><b><font color="#ffffff">  Confirm <?php echo $descr[1] ?>:  </b><?php echo get_plural($classdesc[2])?> from <?php echo $parent->display("nname"); ?></font></td><td><img src="<?php echo $imgheadright; ?>"></td></tr></table></td></tr></table>

	</td></tr><tr><td height=0 colspan=2></td></tr></table>


		<?php 
	}
	?> 

<!--	</td></tr><tr><td height=2 colspan=2></td></tr></table> -->
	<tr><td bgcolor="#ffffff"></td> 
 <?php 

        
		 
	$imguparrow   = get_general_image_path() . "/button/uparrow.gif" ;
	$imgdownarrow = get_general_image_path() . "/button/downarrow.gif" ;




	foreach($name_list as $name => $width) {

		$desc = "__desc_{$name}" ;

		$descr[$name] = get_classvar_description($class, $desc);

		if (!$descr[$name]) {
			print("Cannot access static variable $class::$desc");
			exit(0);
		}

		if (csa($descr[$name][2], ':')) {
			$_tlist = explode(':', $descr[$name][2]);
			$descr[$name][2] = $_tlist[0];
		}

		foreach($descr[$name] as &$d) {
			if ($this->is_special_url($d)) {
				continue;
			}
			if (strstr($d, "%v") !== false) {
				$d = str_replace("[%v]", $classdesc[2], $d);
			}
		}




		if ($width === "100%")
			$wrapstr = "wrap";
		else
			$wrapstr = "nowrap";
 
		if ($sortby && $sortby === $name) {
			$wrapstr .= " background=$imgtablerowheadselect";
			print("<td width=$width $wrapstr ><table cellpadding=0 cellspacing=0 border=0> <tr> <td rowspan=2 $wrapstr>");
		} else {
			$wrapstr .= " background=$imgtablerowhead";
			print("<td width=$width $wrapstr class=col>");
		}


			?> 
		<b><?php $this->print_sortby($parent, $class, $unique_name, $name, $descr[$name])?> </b></font>

		<?php 


		$imgarrow = ($sortdir === "desc")? $imgdownarrow: $imguparrow;



		if ($sortby && $sortby === $name)
			print("</td> <td width=15><img src=".$imgarrow." ></td><td ></td></tr></table>");

			?> 

 </td>

 <?php 
	}
		
	$count = 0;
	$rowcount = 0;

	?> 
	<td width=10 background=<?php echo $imgtablerowhead ?> >   <form name="formselectall<?php echo $unique_name; ?>" value=hello> <input type=checkbox name="selectall<?php echo $unique_name; ?>" value=on <?php if ($sellist) echo "checked disabled" ;  ?> onclick="javascript:calljselectall<?php echo $unique_name; ?> ()"></form> </td>
	<?php 

		print("</tr> ");

					


		print_time('loop');

	$n = 1;
	if ($obj_list) foreach($obj_list as $okey => $obj) {


		$checked = "";


		// Fix This.
		if ($sellist) {
			$checked = "checked disabled";
			if (!array_search_bool($obj->nname, $sellist)) 
				continue;
		}


        $imgpointer = get_general_image_path() . "/button/pointer.gif" ;
        $imgblank = get_general_image_path() . "/button/blank.gif" ;



		?>


		<tr id=tr<?php echo $unique_name.$rowcount; ?> class=tablerow<?php echo $count; ?> onmouseover=" swapImage('imgpoint<?php echo $rowcount; ?>','','<?php echo $imgpointer; ?>',1);" onmouseout="swapImgRestore();">
		<td id=td<?php echo $unique_name.$rowcount; ?> width=5 class=rowpoint><img name=imgpoint<?php echo $rowcount; ?> src="<?php echo $imgblank; ?>"></td>
		<?php   
		$colcount = 1;
		foreach($name_list as $name => $width) {
			$this->printObjectElement($parent, $class, $classdesc, $obj, $name, $width, $descr, $colcount . "_" . $rowcount);
			$colcount++;
		}

		$basename = basename($obj->nname);
		$selectshowbase = $this->frm_selectshowbase;
		$ret = strfrom($parent->nname, $selectshowbase);
		print("	<td width=10 >");
		print("<a class=button href=\"javascript:callSetSelectFolder('/$ret/$basename')\">");
		print(" Select </a>");
		print("</tr> ");
		if($count===0) $count=1; else $count=0; 
		$rowcount++;


		if (!$sellist) {
			if ($n === ($pagesize * $cgi_pagenum)) {
				break;
			}
		}

		$n++;

	}

	print_time('loop', "loop$n");

	print("<tr><td></td><td colspan=$nlcount>");
	if (!$rowcount) {
		if ($ghtml->frm_searchstring) {
			?> 
			<table width=95%> <tr align=center> <td width=100%> <b>  No Matches Found  </b> </td> </tr> </table> 
			<?php 
		} else {
			?> 
			<table width=95%> <tr align=center> <td width=100%> <b>  No <?php echo get_plural($classdesc[2]) ?>  under <?php echo $parent->nname ?>   </b> </td> </tr> </table> 
			<?php 
		}
	}
	print("</td></tr>");
	print("<tr><td class=rowpoint></td><td colspan=".$nlcount." >
	<table cellpadding=0 cellspacing=0 border=0 width=100%>
	<tr height=1 style='background:url($imgtopline)'><td></td></tr>
	<tr><td>");

?>
<script>ckcount<?php echo $unique_name;?> = <?php echo $rowcount.";  ";?>
function calljselectall<?php echo $unique_name; ?>(){
	jselectall(document.formselectall<?php echo $unique_name; ?>.selectall<?php echo $unique_name; ?>,ckcount<?php echo $unique_name; ?>,'<?php echo $unique_name;?>')
}
</script>


<?php




	print("<table> <tr> <td >");

		Print("<a class=button href=\"javascript:window.close()\"> Cancel </a> &nbsp; &nbsp;  ");
	print("</td> <td width=30> &nbsp; </td> <td >");
	print("</td> </tr> </table> ");


print("</td></tr></table></tr></table></td> </tr> </table>");
//else {
//
//		$this->print_list_submit($blist);
//	}



	//print_time("$class.objecttable", "$class.objecttable");
/// Important. This is to make sure that the session saving etc doesn't take place. We just need a plain and clean window without any saving. If this happens the current url gets added to the sessiona and redirection will screw up.
exit;
}



}
