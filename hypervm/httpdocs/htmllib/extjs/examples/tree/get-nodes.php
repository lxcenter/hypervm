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

<?
// from php manual page

chdir("/usr/local/lxlabs/kloxo/httpdocs/");
include_once "htmllib/lib/include.php"; 

log_ajax($_REQUEST);

$dir = isset($_REQUEST['lib'])&&$_REQUEST['lib'] == 'yui' ? '../../../' : '../../';
$node = isset($_REQUEST['node'])?$_REQUEST['node']:"";
if(strpos($node, '..') !== false){
    die('Nice try buddy.');
}
$nodes = array();
$d = dir($dir.$node);
while($f = $d->read()){
    if($f == '.' || $f == '..' || substr($f, 0, 1) == '.')continue;
    $lastmod = date('M j, Y, g:i a',filemtime($dir.$node.'/'.$f));
    if(is_dir($dir.$node.'/'.$f)){
        $qtip = 'Type: Folder<br />Last Modified: '.$lastmod;
        $nodes[] = array('text'=>$f, 'id'=>$node.'/'.$f/*, 'qtip'=>$qtip*/, 'cls'=>'folder');
    }else{
        $size = "100";
        $qtip = 'Type: JavaScript File<br />Last Modified: '.$lastmod.'<br />Size: '.$size;
        $nodes[] = array('text'=>$f, 'id'=>$node.'/'.$f, 'leaf'=>true/*, 'qtip'=>$qtip, 'qtipTitle'=>$f */, 'cls'=>'file');
    }
}
$d->close();
echo json_encode($nodes);
