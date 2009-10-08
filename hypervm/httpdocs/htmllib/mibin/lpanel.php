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

chdir("../../");
include_once "htmllib/lib/displayinclude.php";


lpanel_main();

function lpanel_main()
{
	global $gbl, $login, $ghtml;

	initProgram();
	init_language();
	print_meta_lan();

	$gbl->__navigmenu = null;
	$gbl->__navig = null;

	$skincolor = $login->getSkinColor();

	// This should be called only in display.php, and not anywhere else. It doesn't matter anyway, since both lpanel.php, AND header.php never allows any modification to be carried out. Also, the display.php automatically deletes the login info, so if you click on any link on the header or the lpanel, you will automatically logged out.
	//check_if_disabled_and_exit();

	$imgbordermain = "{$login->getSkinDir()}/top_line_medium.gif";
	if ($gbl->isOn('show_help')) {
		$background = "{$login->getSkinDir()}/top_line_dark.gif";
		$border = null;
	} else {
		$background = null;
	}

	$ghtml->print_include_jscript('left_panel');
	print("<body topmargin=0 leftmargin=0 style='background-color:#fafafa'>");

	//$ghtml->lpanel_beginning();
	try {
		//$ghtml->xp_panel($login);
		//print_ext_tree($login);
		$ghtml->tab_vheight();
	} catch (exception $e) {
		print("The Resource List could not gathered....{$e->getMessage()}<br> \n");
	}


}



function print_ext_tree($object)
{
	global $gbl, $sgbl, $login, $ghtml;

	?>


<script>
	Ext.onReady(function(){
    // shorthand
    var Tree = Ext.tree;
    
    var tree = new Tree.TreePanel('tree-div', {
        animate:true, 
        loader: new Tree.TreeLoader({
            //dataUrl:'get-nodes.php'
            dataUrl:'/ajax.php?frm_action=tree'
        }),
        enableDD:true,
        containerScroll: true
    });

    // set the root node
    var root = new Tree.AsyncTreeNode({
        text: '<?=$object->getId()?>',
        draggable:false,
        id:'/'
    });
    tree.setRootNode(root);

    // render the tree
    tree.render();
    root.expand();
});
</script>
	<?php

}

