<?php
	// Figure out page request method, then grab needed inputs
	$requestmethod = $input->requestMethod('POST') ? 'post' : 'get';
	$action = $input->$requestmethod->text('action');
	$dplusdb = $modules->get('DplusOnlineDatabase')->db_name;

	// Set up filename and sessionID in case this was made through cURL
	$filename  = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();
	$sessionID = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();

	$vendorID = strtoupper($input->$requestmethod->text('vendorID'));

	/**
	* ITEM REDIRECT
	*
	*
	* switch ($action) {
	* 	case 'vi-purchase-orders':
	* 		Request   VI Purchase Orders JSON file
	* 		Response: Creates VI Purchase Orders JSON file
	*		DBNAME=$dplusdb
	*		VIPURCHORDR => n2zz767p
	*		VENDID=$vendorID
	*		SHIPID=$shipfromID **NOTE: OPTIONAL
	*		break;
	*	case 'vi-open-invoices':
	* 		Request:   VI OPEN INVOICES JSON file
	* 		Response: Creates VI OPEN INVOICES JSON file
	*		DBNAME=$dplusdb
	*		VIOPENINV =>n2zz765p
	*		VENDID=$vendorID
	*		break;
	*	*case 'vi-purchase-history'
	*		Request:   VI Purchase History JSON file
	*		Response: Creates VI Purchase History JSON file
	* 		DBNAME=$dplusdb
	*		VIOPENINV n2zz765p
	*		VENDID=$vendorID
	*		SHIPID=$shipfromID
	*		DATE=$data
	* 		break;
	*	case 'vi-unreleased'
	*		- Loads the Vendor Unreleased Purchase Orders
	* 		DBNAME=$config->dplusdbname
	*		VIUNRELEASED n2zz772p
	*		VENDID=$vendorID
	*		SHIPID=
	* 		break;
	* }
	**/

	switch ($action) {
		case 'vi-purchase-orders':
			$data = array("DBNAME=$dplusdb", 'VIPURCHORDR', "VENDID=$vendorID");

			if ($input->$request->shipfromID) {
				$shipfromID = $input->$request->text('shipfromID');
				$data[] = "SHIPID=$shipfromID";
			}

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = new Purl\Url($pages->get('pw_template=vi-purchase-orders')->url);
				$url->query->set('vendorID', $vendorID);

				if ($input->$request->shipfromID) {
					$url->query->set('shipfromID', $shipfromID);
				}
				$session->loc = $url->getUrl();
			}
			break;
		case 'vi-open-invoices':
			$data = array("DBNAME=$dplusdb", 'VIOPENINV', "VENDID=$vendorID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = new Purl\Url($pages->get('pw_template=vi-purchase-orders')->url);
				$url->query->set('vendorID', $vendorID);
				$session->loc = $url->getUrl();
			}
			break;
		case 'vi-purchase-history':
			$data = array("DBNAME=$dplusdb", 'VIPURCHHIST', "VENDID=$vendorID");

			if ($input->$request->shipfromID) {
				$shipfromID = $input->$request->text('shipfromID');
				$data[] = "SHIPID=$shipfromID";
			}
			$date = date('Ymd', strtotime($input->$requestmethod->text('date')));
			$data[] = "DATE=$date";

			if ($input->$requestmethod->page) {
				$url = new Purl\Url($input->$requestmethod->text('page'));
				$url->query->set('date', $date);
				$session->loc = $url->getUrl();
			} else {
				$url = new Purl\Url($pages->get('pw_template=vi-purchase-history')->url);
				$url->query->set('vendorID', $vendorID);

				if ($input->$request->shipfromID) {
					$url->query->set('shipfromID', $shipfromID);
				}
				$url->query->set('date', $date);
				$session->loc = $url->getUrl();
			}
			break;
		case 'vi-unreleased':
			$data = array("DBNAME=$dplusdb", 'VIUNRELEASED', "VENDID=$vendorID");

			if ($input->$request->shipfromID) {
				$shipfromID = $input->$request->text('shipfromID');
				$data[] = "SHIPID=$shipfromID";
			}

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = new Purl\Url($pages->get('pw_template=vi-unreleased')->url);
				$url->query->set('vendorID', $vendorID);

				if ($input->$request->shipfromID) {
					$url->query->set('shipfromID', $shipfromID);
				}
				$session->loc = $url->getUrl();
			}
			break;
		case 'vi-uninvoiced':
			$data = array("DBNAME=$dplusdb", 'VIUNINVOICED', "VENDID=$vendorID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = new Purl\Url($pages->get('pw_template=vi-purchase-orders')->url);
				$url->query->set('vendorID', $vendorID);
				$session->loc = $url->getUrl();
			}
			break;
		case 'vi-24monthsummary':
			$data = array("DBNAME=$dplusdb", 'VIMONTHSUM', "VENDID=$vendorID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = new Purl\Url($pages->get('pw_template=vi-24monthsummary')->url);
				$url->query->set('vendorID', $vendorID);
				$session->loc = $url->getUrl();
			}
			break;
		case 'vi-costing':
			$itemID = $input->get->text('itemID');
			$data = array("DBNAME=$dplusdb", 'VICOST', "VENDID=$vendorID", "ITEMID=$itemID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = new Purl\Url($pages->get('pw_template=vi-costing')->url);
				$url->query->set('vendorID', $vendorID);
				$url->query->set('itemID', $itemID);
				$session->loc = $url->getUrl();
			}
			break;
		case 'vi-sub':
			$itemID = $input->get->text('itemID');
			$data = array("DBNAME=$dplusdb", 'VICOST', "VENDID=$vendorID", "ITEMID=$itemID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = new Purl\Url($pages->get('pw_template=vi-sub')->url);
				$url->query->set('vendorID', $vendorID);
				$url->query->set('itemID', $itemID);
				$session->loc = $url->getUrl();
			}
			break;
		case 'vi-payments':
			$data = array("DBNAME=$dplusdb", 'VIPAYMENT', "VENDID=$vendorID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = new Purl\Url($pages->get('pw_template=vi-payments')->url);
				$url->query->set('vendorID', $vendorID);
				$session->loc = $url->getUrl();
			}
			break;
		case 'vi-notes':
			$data = array("DBNAME=$dplusdb", 'VINOTES', "VENDID=$vendorID");

			if ($input->$request->shipfromID) {
				$shipfromID = $input->$request->text('shipfromID');
				$data[] = "SHIPID=$shipfromID";
			}

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = new Purl\Url($pages->get('pw_template=vi-notes')->url);
				$url->query->set('vendorID', $vendorID);

				if ($input->$request->shipfromID) {
					$url->query->set('shipfromID', $shipfromID);
				}
				$session->loc = $url->getUrl();
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
