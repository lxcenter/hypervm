<?php
/*
 *    HyperVM, Server Virtualization GUI for OpenVZ and Xen
 *
 *    Copyright (C) 2000-2009    LxLabs
 *    Copyright (C) 2009-2012    LxCenter
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
 */

function get_plural($word)
{


	if ($word[strlen($word) - 1] === 'e' || $word[strlen($word) - 1] === 'i' || $word[strlen($word) - 1] === '�' || $word[strlen($word) - 1] === '�') {
		$ret = "{$word}ler";
		return ucfirst($ret);
	} else if ($word[strlen($word) - 1] === 'a' || $word[strlen($word) - 1] === '�' || $word[strlen($word) - 1] === 'o' || $word[strlen($word) - 1] === 'u') {
		$ret = "{$word}lar";
		return ucfirst($ret);
	}

	$ret = "{$word}ler";
	return ucfirst($ret);
}


