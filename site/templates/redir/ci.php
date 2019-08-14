<?php
	// Figure out page request method, then grab needed inputs
	$requestmethod = $input->requestMethod('POST') ? 'post' : 'get';

	$action = $input->$requestmethod->text('action');
	$dplusdb = $modules->get('DplusOnlineDatabase')->db_name;

	// Set up filename and sessionID in case this was made through cURL
	$filename  = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();
	$sessionID = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();

	$custID =  $input->$requestmethod->text('custIDID');
	/**
	* SALES ORDERS REDIRECT
	*
	*
	* switch ($action) {
	* 	case 'ci-contacts':
	* 		Request CI Contacts JSON file
	* 		Response: Creates CI Contacts JSON file
	*		DBNAME=$dplusdb
	*		CICONTACT
	*		CUSTID=$custID
	*		SHIPID=$shipID
	*		break;
	*	case 'ci-sales-orders':
	* 		Request CI Sales Orders JSON file
	* 		Response: Creates CI Sales Orders JSON file
	*		DBNAME=$dplusdb
	*		CISALESORDR
	*		CUSTID=$custID
	*		SHIPID=$shipID
	*		SALESORDRNBR=
	*		ITEMID=
	*		break;
	* }
	**/

	switch ($action) {
		case 'ci-contacts':
			$shipID = $input->$requestmethod->text('shipID');
			$data = array("DBNAME=$dplusdb", 'CICONTACT', "CUSTID=$custID", "SHIPID=$shipID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = new Purl\Url($pages->get('pw_template=ci-contacts'));
				$url->query->set('custID', $custID);
				$session->loc = $url->getUrl();
			}
			break;
		case 'ci-sales-orders':
			$shipID = $input->$requestmethod->text('shipID');
			$data = array("DBNAME=$dplusdb", 'CISALESORDR', "CUSTID=$custID", "SHIPID=$shipID", "SALESORDRNBR= ", "ITEMID= ");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = new Purl\Url($pages->get('pw_template=ci-sales-orders'));
				$url->query->set('custID', $custID);
				$session->loc = $url->getUrl();
			}
			break;
		case 'ci-sales-history':
			$shipID = $input->$requestmethod->text('shipID');
			$data = array("DBNAME=$dplusdb", 'CISALESHIST', "CUSTID=$custID", "SHIPID=$shipID", "DATE=$startdate", "SALESORDRNBR= ", "ITEMID=$itemID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = new Purl\Url($pages->get('pw_template=ci-sales-history'));
				$url->query->set('custID', $custID);
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
