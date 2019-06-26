<?php
	// Figure out page request method, then grab needed inputs
	$requestmethod = $input->requestMethod('POST') ? 'post' : 'get';
	$action = $input->$requestmethod->text('action');
	$dplusdb = $modules->get('DplusOnlineDatabase')->db_name;

	// Set up filename and sessionID in case this was made through cURL
	$filename  = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();
	$sessionID = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();


	switch ($action) {
		case 'get-order-notes':
			$ordn = $input->get->text('ordn');
			$linenbr = $input->get->int('linenbr');
			$data = array("DBNAME=$dplusdb", "LQNOTE=SORD", "KEY1=$ordn", "KEY2=$linenbr");
			break;
		case 'get-order-edit':
			$ordn = $input->get->text('ordn');
			$custID = SalesOrderQuery::create()->get_custid($ordn);
			$data = array("DBNAME=$dplusdb", "ORDRDET=$ordn", "CUSTID=$custID", "LOCK");
			$session->loc = $pages->get('pw_template=sales-order-edit')->url."?ordn=$ordn";
			break;
		case 'edit-order';
			$ordn = $input->$requestmethod->text('ordn');
			$linenbr = $input->$requestmethod->int('linenbr');
			$qty = $input->$requestmethod->int('qty');
			$price = $input->$requestmethod->int('price');
			$shipto_name = $input->$requestmethod->int('shipto_name');
			$shipto_address = $input->$requestmethod->int('shipto_address');
			$shipto_address2 = $input->$requestmethod->int('shipto_address2');
			$shipto_city = $input->$requestmethod->int('shipto_city');
			$shipto_state = $input->$requestmethod->int('shipto_state');
			$shipto_zip = $input->$requestmethod->int('shipto_zip');
			$editorder = Ordrhed::create()->findOneBySessionidOrder(session_id(), $ordn);
			$editorder->set('ordernumber', $ordn);
			$editorder->set('linenbr', $linenbr);
			$editorder->set('qty_ordered', $qty);
			$editorder->set('price', $price);
			$editorder->set('shipto_name', $shipto_name);
			$editorder->set('shipto_address', $shipto_address);
			$editorder->set('shipto_address2', $shipto_address2);
			$editorder->set('shipto_city', $shipto_city);
			$editorder->set('shipto_state', $shipto_state);
			$editorder->set('shipto_zip', $shipto_zip);
			$editorder->save();
			$data = array("DBNAME=$dplusdb", 'SALESHEAD', "ORDERNO=$ordn", "CUSTID=$editorder->custid");
			$session->loc = $input->$requestmethod->text('page');
			break;
		case 'quick-update-line':
			$ordn = $input->$requestmethod->text('ordn');
			$linenbr = $input->$requestmethod->int('linenbr');
			$qty = $input->$requestmethod->int('qty');
			$price = $input->$requestmethod->int('price');
			$custID = SalesOrderQuery::create()->get_custid($ordn);
			// Ordrdet::create not recognized
			$editline = Ordrdet::create()->findOneBySessionidOrderLinenbr(session_id(), $ordn, $linenbr);
			$editline->set('ordernumber', $ordn);
			$editline->set('linenbr', $linenbr);
			$editline->set('qty_ordered', $qty);
			$editline->set('price', $price);
			$editline->save();
			$data = array("DBNAME=$dplusdb", 'SALEDET', "ORDERNO=$ordn", "LINENO=$linenbr", "CUSTID=$custID");
			$session->loc = $input->$requestmethod->text('page');
			break;
		case 'remove-line':
			$ordn = $input->$requestmethod->text('ordn');
			$linenbr = $input->$requestmethod->int('linenbr');
			$editline = Ordrdet::create()->findOneBySessionidOrderLinenbr(session_id(), $ordn, $linenbr);
			$editline->setQty(0);
			$editline->save();
			$data = array("DBNAME=$dplusdb", 'SALEDET', "ORDERNO=$ordn", "LINENO=$linenbr", "CUSTID=$custID");
			$session->loc = $input->$requestmethod->text('page');
			break;
		case 'unlock-order':
			$ordn = $input->$requestmethod->text('ordn');
			$data = array("DBNAME=$dplusdb", 'UNLOCK', "ORDERNO=$ordn");
			$session->loc = $pages->get('pw_template=sales-order-view');
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
