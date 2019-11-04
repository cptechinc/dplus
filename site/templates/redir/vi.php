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
	* 		Request VI Purchase Orders JSON file
	* 		Response: Creates VI Purchase Orders JSON file
	*		DBNAME=$dplusdb
	*		VIPURCHORDR => n2zz767p
	*		VENDID=$vendorID
	*		SHIPID=$shipfromID **NOTE: OPTIONAL
	*		break;
	*	case 'vi-open-invoices':
	* 		Request VI OPEN INVOICES JSON file
	* 		Response: Creates VI OPEN INVOICES JSON file
	*		DBNAME=$dplusdb
	*		VIOPENINV =>n2zz765p
	*		VENDID=$vendorID
	*		break;
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
	}

	if (!empty($data)) {
		write_dplusfile($data, $filename);
		$http = new WireHttp();
		$http->get("127.0.0.1/cgi-bin/".$config->cgis['default']."?fname=$filename");
	}

	if (!empty($session->get('loc')) && !$config->ajax) {
		$session->redirect($session->loc, $http301 = false);
	}
