<?php
	// Figure out page request method, then grab needed inputs
	$requestmethod = $input->requestMethod('POST') ? 'post' : 'get';

	$action = $input->$requestmethod->text('action');
	$dplusdb = $modules->get('DplusOnlineDatabase')->db_name;

	// Set up filename and sessionID in case this was made through cURL
	$filename  = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();
	$sessionID = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();


	/**
	* SALES ORDERS REDIRECT
	*
	*
	* switch ($action) {
	*	case 'get-order-edit':
	*		Request Sales Order Details for Edit
	* 		Response: Creates Ordrhed, Ordrdet records, updates logperm with lock
	*		DBNAME=$dplusdb
	*		ORDRDET=$ordn
	*		CUSTID=$custID
	*		LOCK
	*		break;
	*	case 'edit-order':
	*		Request Sales Order Header Changes to be saved
	* 		Response: Updates Ordrhed, so_header records
	*		DBNAME=$dplusdb
	*		SALESHEAD
	*		ORDERNO=$ordn
	*		CUSTID=$editorder->custid
	*		** UNLOCK
	*		break;
	*	case 'edit-new-order':
	* 		Request: Sales Order Details for Edit for brand new order
	* 		Response: Updates Ordrdet, so_detail records
	*		DBNAME=$dplusdb
	*		ORDRDET=$ordn
	*		CUSTID=$custID
	*		LOCK
	* 		break;
	*	case 'quick-update-line':
	*		Request Sales Order Detail Line to be updated
	* 		Response: Updates Ordrdet, so_detail records
	*		DBNAME=$dplusdb
	*		SALEDE
	*		ORDERNO=$ordn
	*		LINENO=$linenbr
	*		CUSTID=$custID
	*		break;
	*	case 'remove-line':
	*		Request Sales Order Detail Line to be updated
	* 		Response: Updates Ordrdet, so_detail records
	*		DBNAME=$dplusdb
	*		SALEDE
	*		ORDERNO=$ordn
	*		LINENO=$linenbr
	*		QTY=0
	*		CUSTID=$custID
	*		break;
	*	case 'add-item':
	* 		Request: Add Item to Order
	* 		Response: Updates Ordrdet, so_detail records
	* 		DBNAME=$dplusdb
	* 		SALEDET
	*		ORDERNO=$ordn
	*		ITEMID=$itemID
	*		QTY=$qty
	* 		break;
	*	case 'unlock-order':
	* 		Request Sales Order Unlock
	* 		Response: updates dplus locks, logperm
	*		DBNAME=$dplusdb
	*		UNLOCK
	*		ORDERNO=$ordn
	*		break;
	* }
	**/

	switch ($action) {
		case 'get-order-edit':
			$ordn = $input->get->text('ordn');
			$custID = SalesOrderQuery::create()->get_custid($ordn);
			$data = array("DBNAME=$dplusdb", "ORDRDET=$ordn", "CUSTID=$custID", "LOCK");
			$session->loc = $pages->get('pw_template=sales-order-edit')->url."?ordn=$ordn";
			break;
		case 'edit-new-order':
			$ordn = $user->get_lockedID();
			$custID = SalesOrderQuery::create()->get_custid($ordn);
			$data = array("DBNAME=$dplusdb", "ORDRDET=$ordn", "CUSTID=$custID", "LOCK");
			$session->loc = $pages->get('pw_template=sales-order-edit')->url."?ordn=$ordn";
			break;
		case 'edit-order';
			$ordn = $input->$requestmethod->text('ordn');
			$editorder = OrdrhedQuery::create()->findOneBySessionidOrder(session_id(), $ordn);
			$editorder->setShipname($input->$requestmethod->text('shipto_name'));
			$editorder->setShipaddress($input->$requestmethod->text('shipto_address'));
			$editorder->setShipaddress2($input->$requestmethod->text('shipto_address2'));
			$editorder->setShipcity($input->$requestmethod->text('shipto_city'));
			$editorder->setShipstate($input->$requestmethod->text('shipto_state'));
			$editorder->setShipzip($input->$requestmethod->text('shipto_zip'));
			$editorder->setContact($input->$requestmethod->text('contact'));
			$editorder->setPhone($input->$requestmethod->text('phone'));
			$editorder->setExtension($input->$requestmethod->text('phone_ext'));
			$editorder->setFax($input->$requestmethod->text('fax'));
			$editorder->setEmail($input->$requestmethod->text('email'));
			$editorder->setCustpo($input->$requestmethod->text('custpo'));
			$editorder->setReleasenbr($input->$requestmethod->text('releasenumber'));
			$editorder->setShipviacd($input->$requestmethod->text('shipvia'));
			$editorder->setRqstDate($input->$requestmethod->text('date_requested'));
			$editorder->setShipcom($input->$requestmethod->text('shipcomplete'));
			$editorder->setTermcode($input->$requestmethod->text('termscode'));
			$editorder->save();
			$data = array("DBNAME=$dplusdb", 'SALESHEAD', "ORDERNO=$ordn", "CUSTID=$editorder->custid");

			if ($input->$requestmethod->exit) {
				$session->loc = $pages->get('template=dplus-menu')->child('template=redir')->url."?action=unlock-order&ordn=$ordn";
				$data[] = 'UNLOCK';
			} else {
				$session->loc = $pages->get('pw_template=sales-order-edit')->url."?ordn=$ordn";
			}
			break;
		case 'quick-update-line':
			$ordn = $input->$requestmethod->text('ordn');
			$linenbr = $input->$requestmethod->int('linenbr');
			$qty = $input->$requestmethod->int('qty');
			$price = $input->$requestmethod->float('price');
			$custID = SalesOrderQuery::create()->get_custid($ordn);
			$editline = OrdrdetQuery::create()->findOneBySessionidOrder(session_id(), $ordn, $linenbr);
			$editline->setQty($qty);
			$editline->setPrice($price);
			$editline->save();
			$data = array("DBNAME=$dplusdb", 'SALEDET', "ORDERNO=$ordn", "LINENO=$linenbr", "CUSTID=$custID");
			$session->loc = $pages->get('pw_template=sales-order-edit')->url."?ordn=$ordn";
			break;
		case 'add-item':
			$ordn   = $input->$requestmethod->text('ordn');
			$itemID = $input->$requestmethod->text('itemID');
			$qty    = $input->$requestmethod->int('qty');
			$custID = SalesOrderQuery::create()->get_custid($ordn);
			$data = array("DBNAME=$dplusdb", 'SALEDET', "ORDERNO=$ordn", "ITEMID=$itemID", "QTY=$qty", "CUSTID=$custID");
			$session->loc = $pages->get('pw_template=sales-order-edit')->url."?ordn=$ordn";
			break;
		case 'add-popular-items':
			$ordn = $input->$requestmethod->text('ordn');
			$data = array("DBNAME=$dplusdb", 'ORDERADDMULTIPLE', "ORDERNO=$ordn");
			$qtys = $input->$requestmethod->array('qty');
			$itemIDs = $input->$requestmethod->array('itemID');

			for ($i = 0; $i < sizeof($qtys); $i++) {
				if (!empty($qtys[$i])) {
					$itemID = str_pad($itemIDs[$i], 30, ' ');
					$qty = $qtys[$i];
					$data[] = "ITEMID={$itemID}QTY=$qty";
				}
			}

			if ($input->$requestmethod->page) {
				$url = new Purl\Url($input->$requestmethod->text('page'));
				$session->loc = $url->getUrl();
			} else {
				$session->loc = $pages->get('pw_template=sales-order-edit')->url."?ordn=$ordn";
			}
			break;
		case 'remove-line':
			$ordn = $input->$requestmethod->text('ordn');
			$linenbr = $input->$requestmethod->int('linenbr');
			$custID = SalesOrderQuery::create()->get_custid($ordn);
			$editline = OrdrdetQuery::create()->findOneBySessionidOrder(session_id(), $ordn, $linenbr);
			$editline->setQty(0);
			$editline->save();
			$data = array("DBNAME=$dplusdb", 'SALEDET', "ORDERNO=$ordn", "LINENO=$linenbr", "QTY=0", "CUSTID=$custID");
			$session->loc = $pages->get('pw_template=sales-order-edit')->url."?ordn=$ordn";
			break;
		case 'unlock-order':
			$ordn = $input->$requestmethod->text('ordn');
			$data = array("DBNAME=$dplusdb", 'UNLOCK', "ORDERNO=$ordn");
			$session->loc = $pages->get('pw_template=sales-order-view')->url."?ordn=$ordn";
			break;
	}

	if (!empty($data)) {
		$requestor = $modules->get('DplusRequest');
		$requestor->write_dplusfile($data, $filename);
		$requestor->cgi_request($config->cgis['default'], $filename);
	}

	if (!empty($session->get('loc')) && !$config->ajax) {
		$session->redirect($session->loc, $http301 = false);
	}
