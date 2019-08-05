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
	* 	case 'get-order-notes':
	* 		Request Sales Order Notes from Dplus
	* 		Response: Creates qnote table records
	*		DBNAME=$dplusdb
	*		LQNOTE=SORD
	*		KEY1=$ordn
	*		KEY2=$linenbr
	*		break;
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
			$shipname = $input->$requestmethod->text('shipname');
			$shipaddress = $input->$requestmethod->text('shipaddress');
			$shipaddress2 = $input->$requestmethod->text('shipaddress2');
			$shipcity = $input->$requestmethod->text('shipcity');
			$shipstate = $input->$requestmethod->text('shipstate');
			$shipzip = $input->$requestmethod->text('shipzip');
			$editorder = OrdrhedQuery::create()->findOneBySessionidOrder(session_id(), $ordn);
			$editorder->set('shipname', $shipname);
			$editorder->set('shipaddress', $shipaddress);
			$editorder->set('shipaddress2', $shipaddress2);
			$editorder->set('shipcity', $shipcity);
			$editorder->set('shipstate', $shipstate);
			$editorder->set('shipzip', $shipzip);
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
		write_dplusfile($data, $filename);
		$http = new WireHttp();
		$http->get("127.0.0.1/cgi-bin/".$config->cgis['default']."?fname=$filename");
	}

	if (!empty($session->get('loc')) && !$config->ajax) {
		$session->redirect($session->loc, $http301 = false);
	}
