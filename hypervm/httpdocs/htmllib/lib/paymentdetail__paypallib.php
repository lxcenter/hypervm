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


class paymentdetail__paypal extends Lxdriverclass {

	static function createPaymentDetail($list)
	{
		$ret['amount'] = $list['mc_gross'];
		$ret['info'] =  $list['payer_email'];
		$ret['transactionid'] = $list['txn_id'];


		$id = $list['item_name'];
		if (!csb($id, "lx_")) {
			log_log("paypal_billing", "Not lx_, skipping... $id\n");
			return;
		}

		$cllist = explode("_", $id);

		$ret['month'] = $cllist[1];
		array_shift($cllist);
		array_shift($cllist);
		$ret['client'] = implode("_", $cllist);

		if (!$ret['client']) {
			log_log("paypal_billing", "No client for transactionid {$ret['transactionid']}.. Exiting...\n");
			return;
		}

		$ret['ddate'] = time();

		return $ret;
	}



}

