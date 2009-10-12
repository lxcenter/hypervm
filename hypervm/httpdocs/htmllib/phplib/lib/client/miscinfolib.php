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

class Miscinfo_b extends Lxaclass {

    static $__desc_realname = array("", "",  "real_name"    );
    static $__desc_paddress = array("", "",  "personal_address"    );
    static $__desc_padd_city = array("", "",  "city"    );
    static $__desc_padd_country = array( "", "",  "Country", "Country of the Client"   );
    static $__desc_ptelephone = array("", "",  "telephone_no"   );
    static $__desc_caddress = array("", "",  "company_address"   );
    static $__desc_cadd_country = array("", "",  "country" );
    static $__desc_cadd_city = array("", "",  "city"    );
    static $__desc_ctelephone = array("", "",  "telephone"   );
	static $__desc_cfax = array("", "",  "fax"   );
	static $__desc_text_comment = array("t", "",  "comments"   );
}

class sp_Miscinfo extends LxSpecialClass {

static $__desc  = array("","",  "miscinfo"); 
static $__desc_nname = array("", "",  "name"  );
static $__desc_miscinfo_b =  array("", "",  "personal_info_of_client");
static $__acdesc_update_miscinfo =  array("","",  "details"); 



function updateform($subaction, $param)
{

	$vlist['nname']= array('M', $this->getSpecialname());
	$vlist['miscinfo_b_s_realname']= "";
	$vlist['miscinfo_b_s_paddress']= "";
	$vlist['miscinfo_b_s_padd_city']= "";
	$vlist['miscinfo_b_s_padd_country']= "";
	$vlist['miscinfo_b_s_ptelephone']= "";
	$vlist['miscinfo_b_s_caddress']= "";
	$vlist['miscinfo_b_s_ctelephone']= "";
	$vlist['miscinfo_b_s_cadd_city']= "";
	$vlist['miscinfo_b_s_cadd_country']= "";
	$vlist['miscinfo_b_s_cfax']= "";
	return $vlist;
}

}


