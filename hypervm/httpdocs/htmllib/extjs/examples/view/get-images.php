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
$dir = "images/thumbs/";
$images = array();
$d = dir($dir);
while($name = $d->read()){
    if(!preg_match('/\.(jpg|gif|png)$/', $name)) continue;
    $size = filesize($dir.$name);
    $lastmod = filemtime($dir.$name)*1000;
    $images[] = array('name'=>$name, 'size'=>$size, 
			'lastmod'=>$lastmod, 'url'=>$dir.$name);
}
$d->close();
$o = array('images'=>$images);
echo json_encode($o);
?>