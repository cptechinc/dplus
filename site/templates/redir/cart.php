<?php
	// Figure out page request method, then grab needed inputs
	$requestmethod = $input->requestMethod('POST') ? 'post' : 'get';
	$action = $input->$requestmethod->text('action');
	$dplusdb = $modules->get('DplusOnlineDatabase')->db_name;

	// Set up filename and sessionID in case this was made through cURL
	$filename  = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();
	$sessionID = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();

	$itemID = strtoupper($input->$requestmethod->text('itemID'));
	$cart = $modules->get('Cart');


	/**
	* CART REDIRECT
	*
	*
	* switch ($action) {
	*	case 'add-item':
	*		Request: Add Item to Cart
	* 		Response: Adds Item to the Cart Details table
	*		DBNAME=$dplusdb
	*		CARTDET
	*		ITEMID=$itemID
	*		QTY=$qty
	*		CUSTID=$custID
	*		LOCK
	*		break;
	*	case 'quick-update-line':
	*		Request: Update Cart Detail Line
	* 		Response: Updates Cart Detail Line
	*		DBNAME=$dplusdb
	*		CARTDET
	*		LINENO=$linenbr
	*		CUSTID=$custID
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
		case 'add-item':
			$itemID = $input->$requestmethod->text('itemID');
			// TODO: BOTTLE CASE QTY FOR BELLBOY
			$qty = $input->get->int('qty');
			$custID = $cart->get_custid();
			$custID = !empty($custID) ? $custID : $config->defaultweb;
			$data = array("DBNAME=$dplusdb", 'CARTDET', "ITEMID=$itemID", "QTY=$qty", "CUSTID=$custID");

			if ($input->$requestmethod->page) {
				$url = new Purl\Url($input->$requestmethod->text('page'));
				$url->query->remove('q');
				$session->loc = $url->getUrl();
			} else {
				$session->loc = $pages->get('pw_template=cart')->httpUrl;
			}
			break;
		case 'quick-update-line':
			$linenbr = $input->$requestmethod->int('linenbr');
			$cartdetail = CartdetQuery::create()->filterBySessionidLinenbr($sessionID, $linenbr)->findOne();
			$custID = $cart->get_custid();
			$custID = !empty($custID) ? $custID : $config->defaultweb;
			// TODO: BOTTLE CASE QTY FOR BELLBOY
			$cartdetail->setQty($input->get->int('qty'));
			$cartdetail->setPrice($input->$requestmethod->text('price'));
			$cartdetail->setRshipdate($input->$requestmethod->text('rqstdate'));

			$cartdetail->save();
			$data = array("DBNAME=$dplusdb", 'CARTDET', "LINENO=$linenbr", "CUSTID=$custID");

			if ($input->$requestmethod->page) {
				$url = new Purl\Url($input->$requestmethod->text('page'));
				$session->loc = $url->getUrl();
			} else {
				$session->loc = $pages->get('pw_template=cart')->url;
			}
			break;
		case 'remove-line':
			$linenbr = $input->$requestmethod->int('linenbr');
			$custID = $cart->get_custid();
			$custID = !empty($custID) ? $custID : $config->defaultweb;
			$cartdetail = CartdetQuery::create()->filterBySessionidLinenbr($sessionID, $linenbr)->findOne();
			$cartdetail->setQty(0);
			$cartdetail->save();
			$data = array("DBNAME=$dplusdb", 'CARTDET', "LINENO=$linenbr", 'QTY=0', "CUSTID=$custID");

			if ($input->$requestmethod->page) {
				$url = new Purl\Url($input->$requestmethod->text('page'));
				$session->loc = $url->getUrl();
			} else {
				$session->loc = $pages->get('pw_template=cart')->url;
			}
			break;
		case 'empty-cart':
			$data = array("DBNAME=$dplusdb", 'EMPTYCART');

			if ($input->$requestmethod->page) {
				$url = new Purl\Url($input->$requestmethod->text('page'));
				$session->loc = $url->getUrl();
			} else {
				$session->loc = $pages->get('pw_template=cart')->url;
			}
			break;
		case 'add-popular-items':
			$custID = $cart->get_custid();
			$data = array("DBNAME=$dplusdb", 'CARTADDMULTIPLE', "CUSTID=$custID");
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
				$session->loc = $pages->get('pw_template=cart')->url;
			}
			break;
		case 'create-order':
			$data = array("DBNAME=$dplusdb", 'CREATESO');

			if ($input->$requestmethod->page) {
				$url = new Purl\Url($input->$requestmethod->text('page'));
				$session->loc = $url->getUrl();
			} else {
				$session->loc = $pages->get('template=redir,redir_file=sales-order')->url.'?action=edit-new-order';
			}
			break;
		case 'create-quote':
			$data = array("DBNAME=$dplusdb", 'CREATEQT');

			if ($input->$requestmethod->page) {
				$url = new Purl\Url($input->$requestmethod->text('page'));
				$session->loc = $url->getUrl();
			} else {
				$session->loc = $pages->get('template=redir,redir_file=quote')->url.'?action=edit-new-quote';
			}
			break;
	}

	if (!empty($data)) {
		write_dplusfile($data, $filename);
		$http = new WireHttp();
		$http->get("127.0.0.1/cgi-bin/".$config->cgis['default']."?fname=$filename");
	}

	if (!empty($session->get('loc')) && !$config->ajax) {
		$session->redirect($session->loc, $http301 = false);
	}
