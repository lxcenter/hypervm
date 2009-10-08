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

class NotFlag_b extends Lxaclass {


	static $__desc = array("", "",  "notification");
	static $__desc_nname  =  array("", "",  "event_name",);
	static $__desc_ticketadd_flag  =  array("f", "",  "notify_me_when_ticket_is_added" );
	static $__desc_ticketadd_flag_v_on  =  array("f", "",  "notify_me_when_ticket_is_added" );

	static $__desc_ticketchange_flag  =  array("f", "",  "notify_me_when_ticket_changes");
	static $__desc_ticketchange_flag_v_on  =  array("f", "",  "notify_me_when_ticket_changes");
	static $__desc_addstuff_flag  =  array("f", "",  "notify_when_accounts_are_added_to_my_account");
	static $__desc_addstuff_flag_v_on  =  array("f", "",  "notify_when_accounts_are_added_to_my_account");
	static $__desc_deletestuff_flag  =  array("f", "",  "notify_when_accounts_are_deleted_from_my_account");
	static $__desc_deletestuff_flag_v_on  =  array("f", "",  "notify_when_accounts_are_deleted_from_my_account");
}

class Notification  extends LxspecialClass {

	static $__ttype = "permanent";
	static $__desc = array("", "",  "notification");
	static $__desc_class_list = array("", "",  "notify_me_when_these_are_added_anywhere");

	//Core

	//Dataa
	static $__desc_nname  =  array("", "",  "event_name",);
	static $__desc_notflag_b  =  array("", "",  "description", 'a=updateform');

	static $__desc_text_newaccountmessage  =  array("t", "",  "welcome_message_for_new_accounts");
	static $__desc_text_newsubject  =  array("", "",  "welcome_message_subject");
	static $__desc_fromaddress  =  array("", "",  "welcome_message_from");

	static $__acdesc_update_update =  array("", "",  "configure_notification");



	function postUpdate()
	{
		$this->class_list = explode(",", $this->class_list);
	}


	function updateform($subaction, $param)
	{

		global $gbl, $sgbl, $login, $ghtml;

		$parent = $this->getParentO();
		$vlist['notflag_b_s_ticketadd_flag'] = null;
		$vlist['notflag_b_s_ticketchange_flag'] = null;

		if (!$parent->moreNotification()) {
			return $vlist;
		}

		$vlist['notflag_b_s_addstuff_flag'] = null;
		$vlist['notflag_b_s_deletestuff_flag'] = null;

		if ($sgbl->isKloxo()) {
			if (!$parent->isLte('reseller')) {
				return $vlist;
			}
		} else {
			if (!$parent->isLte('customer')) {
				return $vlist;
			}
		}

		if ($parent->isAdmin() && $sgbl->isKloxo()) {
			$vlist['class_list'] = array('U', array('domain', 'client', 'mailaccount'));
		}

		$vlist['fromaddress'] = null;
		$vlist['text_newsubject'] = null;
		$vlist['text_newaccountmessage'] = null;
		return $vlist;
	}



	static function perPage() { return 500; }
	function isSelect() { return false; }
	function isSync() { return false; }


	function isRightParent()
	{
		return ($this->nname === $this->getParentO()->getClName());
	}

	static function initThisObjectRule($parent, $class, $name = null)
	{
		return $parent->getClName();
	}

	function createShowUpdateform()
	{
		$vform['update'] = null;
		return $vform;
	}

	function changeDetails($r1,$r2,$r3)
	{
		$this->toadmin  = $r1;
		$this->toclient = $r2;
		$this->toduser  = $r3;
		$this->dbaction = "update";
	}



}



