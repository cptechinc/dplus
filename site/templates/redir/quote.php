<?php
	// Figure out page request method, then grab needed inputs
	$requestmethod = $input->requestMethod('POST') ? 'post' : 'get';

	$action = $input->$requestmethod->text('action');
	$dplusdb = $modules->get('DplusOnlineDatabase')->db_name;

	// Set up filename and sessionID in case this was made through cURL
	$filename  = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();
	$sessionID = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();


	/**
	* QUOTE REDIRECT
	*
	*
	* switch ($action) {
	* 	case 'get-quote-edit':
	*		- Loads Quote for editing
	*		DBNAME=$dplusdb
	*		EDITQUOTE
	*		QUOTENO=$qnbr
	*		break;
	*	case 'edit-new-quote':
	*		- Loads Quote for editing
	*		DBNAME=$dplusdb
	*		EDITQUOTE
	*		QUOTENO=$qnbr
	*		break;
	*	case 'edit-quote':
	*		- Saves changes to Quothed
	*		DBNAME=$dplusdb
	*		UPDATEQUOTEHEAD
	*		QUOTENO=$qnbr
	*		break;
	*	case 'quick-update-line':
	* 		- Updates Detail Line
	*		DBNAME=$dplusdb
	*		SALEDET
	*		ORDERNO=$ordn
	*		LINENO=$linenbr
	* 		break;
	* 	case 'quick-update-line':
	* 		- Updates Detail Line
	*		DBNAME=$dplusdb
	*		UPDATEQUOTEDETAIL
	*		QUOTENO=$qnbr
	*		LINENO=$linenbr
	* 		break;
	* 	case 'add-item':
	* 		- Adds an Item to the Sales Order
	* 		DBNAME=$dplusdb
	* 		UPDATEQUOTEDETAIL
	*		QUOTENO=$qnbr
	*		ITEMID=$itemID
	*		QTY=$qty
	* 		break;
	* 	case 'remove-line':
	*		- Removes Detail Line
	*		DBNAME=$dplusdb
	*		UPDATEQUOTEDETAIL
	*		QUOTENO=$qnbr
	*		LINENO=$linenbr
	*		QTY=0
	*		break;
	* }
	**/

	switch ($action) {
		case 'get-quote-edit':
			$qnbr = $input->$requestmethod->text('qnbr');
			$data = array("DBNAME=$dplusdb", 'EDITQUOTE', "QUOTENO=$qnbr");
			$session->loc = $pages->get('pw_template=quote-edit')->url."?qnbr=$qnbr";
			break;
		case 'edit-new-quote':
			$qnbr = $user->get_lockedID();
			$data = array("DBNAME=$dplusdb", 'EDITQUOTE', "QUOTENO=$qnbr");
			$session->loc = $pages->get('pw_template=quote-edit')->url."?qnbr=$qnbr";
			break;
		case 'edit-quote';
			$qnbr = $input->$requestmethod->text('qnbr');
			$editquote = QuothedQuery::create()->findOneBySessionidQuote(session_id(), $qnbr);
			$editquote->setShipto_name($input->$requestmethod->text('shipto_name'));
			$editquote->setShipto_address($input->$requestmethod->text('shipto_address'));
			$editquote->setShipto_address2($input->$requestmethod->text('shipto_address2'));
			$editquote->setShipto_city($input->$requestmethod->text('shipto_city'));
			$editquote->setShipto_state($input->$requestmethod->text('shipto_state'));
			$editquote->setShipto_zip($input->$requestmethod->text('shipto_zip'));
			$editquote->setContact($input->$requestmethod->text('contact'));
			$editquote->setPhone(str_replace('-', '', $input->$requestmethod->text('phone')));
			$editquote->setFaxnbr(str_replace('-', '', $input->$requestmethod->text('fax')));
			$editquote->setEmail($input->$requestmethod->text('email'));
			$editquote->setCustpo($input->$requestmethod->text('custpo'));
			$editquote->setShipviacd($input->$requestmethod->text('shipvia'));
			$editquote->setFob($input->$requestmethod->text('fob'));
			$editquote->setDelivery($input->$requestmethod->text('delivery'));
			$editquote->setCareof($input->$requestmethod->text('careof'));
			$editquote->setWarehouse($input->$requestmethod->text('warehouse'));
			$editquote->save();
			$data = array("DBNAME=$dplusdb", 'UPDATEQUOTEHEAD', "QUOTENO=$qnbr");

			if ($input->$requestmethod->exit) {
				$session->loc = $pages->get('template=dplus-menu,name=mqo')->child('template=redir')->url."?action=unlock-quote&qnbr=$qnbr";
			} else {
				$session->loc = $pages->get('pw_template=quote-edit')->url."?qnbr=$qnbr";
			}
			break;
		case 'unlock-quote':
			$qnbr = $input->$requestmethod->text('qnbr');
			$session->loc = $pages->get('pw_template=quote-view')->url."?qnbr=$qnbr";
			break;
		case 'order-quote':
			$qnbr = $input->$requestmethod->text('qnbr');
			$data = array("DBNAME=$dplusdb", 'QUOTETOORDER', "QUOTENO=$qnbr", "LINENO=ALL");
			$session->loc = $pages->get('template=dplus-menu,name=mso')->child('template=redir')->url."?action=edit-new-order";
			break;
		case 'quick-update-line':
			$qnbr = $input->$requestmethod->text('qnbr');
			$linenbr = $input->$requestmethod->int('linenbr');
			$custID = QuoteQuery::create()->select(Quote::get_aliasproperty('custid'))->filterByQuoteid($qnbr)->findOne();
			$detail = QuotdetQuery::create()->filterBySessionidQuote(session_id(), $qnbr)->filterByLinenbr($linenbr)->findOne();
			$detail->setQuotqty($input->$requestmethod->int('qty'));

			if ($input->$requestmethod->orderquote) {
				$qty = ($input->$requestmethod->checkorder) ? $input->$requestmethod->int('qty') : 0;
				$detail->setOrdrqty($qty);
			} else {
				$detail->setOrdrqty($input->$requestmethod->int('qty'));
			}

			$detail->setQuotprice($input->$requestmethod->text('price'));
			$detail->setOrdrprice($input->$requestmethod->text('price'));
			$detail->setRshipdate($input->$requestmethod->text('date_requested'));
			$detail->save();

			$data = array("DBNAME=$dplusdb", 'UPDATEQUOTEDETAIL', "QUOTENO=$qnbr", "LINENO=$linenbr", "CUSTID=$custID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$session->loc = $pages->get('pw_template=quote-edit')->url."?qnbr=$qnbr";
			}
			$session->editdetail = true;
			break;
		case 'add-item':
			$qnbr   = $input->$requestmethod->text('qnbr');
			$itemID = $input->$requestmethod->text('itemID');
			$qty    = $input->$requestmethod->int('qty');
			$data = array("DBNAME=$dplusdb", 'UPDATEQUOTEDETAIL', "QUOTENO=$qnbr", "ITEMID=$itemID", "QTY=$qty");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$session->loc = $pages->get('pw_template=quote-edit')->url."?qnbr=$qnbr";
			}
			break;
		case 'add-popular-items':
			$qnbr = $input->$requestmethod->text('qnbr');
			$data = array("DBNAME=$dplusdb", 'QUOTEADDMULTIPLE', "QUOTENO=$qnbr");
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
				$session->loc = $pages->get('pw_template=quote-edit')->url."?ordn=$ordn";
			}
			break;
		case 'remove-line':
			$qnbr = $input->$requestmethod->text('qnbr');
			$linenbr = $input->$requestmethod->int('linenbr');
			$custID = QuoteQuery::create()->select(Quote::get_aliasproperty('custid'))->filterByQuoteid($qnbr)->findOne();
			$detail = QuotdetQuery::create()->filterBySessionidQuote(session_id(), $qnbr)->filterByLinenbr($linenbr)->findOne();
			$detail->setQuotqty(0);
			$detail->setOrdrqty(0);
			$detail->save();
			$data = array("DBNAME=$dplusdb", 'UPDATEQUOTEDETAIL', "QUOTENO=$qnbr", "LINENO=$linenbr", "QTY=0", "CUSTID=$custID");
			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$session->loc = $pages->get('pw_template=quote-edit')->url."?qnbr=$qnbr";
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
