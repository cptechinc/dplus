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
			$data = array("DBNAME=$dplusdb", "ORDRDET=$ordn", "CUSTID=$custID");
			break;
		case 'edit-order';
			$ordn = $input->$requestmethod->text('ordn');
			$editorder = Ordrhed::create()->findOneBySessionidOrder(session_id(), $ordn);

			// NOW SET EACH property using $editorder->set($column/$alias, $value) or $editorder->set{Columnname}($value)
			$editorder->save();
			$data = array("DBNAME=$dplusdb", 'SALESHEAD', "ORDERNO=$ordn", "CUSTID=$editorder->custid");
			$session->loc = $input->$requestmethod->text('page');
			break;
		case 'quick-update-line':
			$ordn = $input->$requestmethod->text('ordn');
			$linenbr = $input->$requestmethod->int('linenbr');
			$custID = SalesOrderQuery::create()->get_custid($ordn);
			$editline = Ordrdet::create()->findOneBySessionidOrderLinenbr(session_id(), $ordn, $linenbr);

			// NOW SET EACH property using $editline->set($column/$alias, $value), or $editline->set{Columnname}($value)
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
