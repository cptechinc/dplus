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
	* 	case 'get-quote-edit':
	*		- Loads Quote for editing
	*		DBNAME=$config->dplusdbname
	*		EDITQUOTE
	*		QUOTENO=$qnbr
	*		break;
	* }
	**/

	switch ($action) {
		case 'get-quote-edit':
			$qnbr = $input->$requestmethod->text('qnbr');
			$data = array("DBNAME=$dplusdb", 'EDITQUOTE', "QUOTENO=$qnbr");
			$session->loc = $pages->get('pw_template=quote-edit')->url."?qnbr=$qnbr";
			break;
		case 'edit-order';
			$qnbr = $input->$requestmethod->text('qnbr');
			$editquote = QuothedQuery::create()->findOneBySessionidQuote(session_id(), $qnbr);
			$editquote->setShipto_name($input->$requestmethod->text('shipto_name'));
			$editquote->setShipto_address($input->$requestmethod->text('shipto_address'));
			$editquote->setShipto_address2($input->$requestmethod->text('shipto_address2'));
			$editquote->setShipto_city($input->$requestmethod->text('shipto_city'));
			$editquote->setShipto_state($input->$requestmethod->text('shipto_state'));
			$editquote->setShipto_zip($input->$requestmethod->text('shipto_zip'));
			$editquote->setContact($input->$requestmethod->text('contact'));
			$editquote->setPhone($input->$requestmethod->text('phone'));
			$editquote->setExtension($input->$requestmethod->text('phone_ext'));
			$editquote->setFax($input->$requestmethod->text('fax'));
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
	}

	if (!empty($data)) {
		write_dplusfile($data, $filename);
		$http = new WireHttp();
		$http->get("127.0.0.1/cgi-bin/".$config->cgis['default']."?fname=$filename");
	}

	if (!empty($session->get('loc')) && !$config->ajax) {
		$session->redirect($session->loc, $http301 = false);
	}
