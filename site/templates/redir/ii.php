<?php
	// Figure out page request method, then grab needed inputs
	$requestmethod = $input->requestMethod('POST') ? 'post' : 'get';
	$action = $input->$requestmethod->text('action');
	$dplusdb = $modules->get('DplusOnlineDatabase')->db_name;

	// Set up filename and sessionID in case this was made through cURL
	$filename  = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();
	$sessionID = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();

	$itemID = strtoupper($input->$requestmethod->text('itemID'));

	/**
	* ITEM REDIRECT
	*
	*
	* switch ($action) {
	* 	case 'ii-item':
	* 		Request II Item JSON file
	* 		Response: Creates II Item JSON file
	*		DBNAME=$dplusdb
	*		IISELECT
	*		ITEMID=$itemID
	*		break;
	* }
	**/

	switch ($action) {
		case 'ii-item':
			$data = array("DBNAME=$dplusdb", 'IISELECT', "ITEMID=$itemID");

			if ($input->$requestmethod->page) {
				$url = new Purl\Url($input->$requestmethod->text('page'));
				$url->query->remove('q');
				$url->query->set('itemID', $itemID);
				$session->loc = $url->getUrl();
			} else {
				$url = $pages->get('pw_template=ii-item')->httpUrl."?itemID=$itemID";
				$session->loc = $url;
			}
			break;
		case 'item-search':
			$q = strtoupper($input->$requestmethod->text('q'));
			$custID = !empty($input->$requestmethod->custID) ? $input->$requestmethod->text('custID') : $config->defaultweb;
			$data = array("DBNAME=$dplusdb", "ITNOSRCH=$q", "CUSTID=$custID");
			break;
		case 'item-pricing-multiple':
			$itemIDs = $input->$requestmethod->array('itemID');
			$custID = !empty($input->$requestmethod->custID) ? $input->$requestmethod->text('custID') : $config->defaultweb;
			$data = array("DBNAME=$dplusdb", "ITMPRIMULT", "CUSTID=$custID");
			$itemIDs = $input->$requestmethod->array('itemID');

			foreach ($itemIDs as $itemID) {
				$data[] = "ITEMID=$itemID";
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
