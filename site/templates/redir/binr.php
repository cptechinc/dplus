<?php
	// Figure out page request method, then grab needed inputs
	$requestmethod = $input->requestMethod('POST') ? 'post' : 'get';
	$action = $input->$requestmethod->text('action');
	$dplusdb = $modules->get('DplusOnlineDatabase')->db_name;

	// Set up filename and sessionID in case this was made through cURL
	$filename  = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();
	$sessionID = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();

	/**
	* BINR REDIRECT
	* NOTE uses whseman.log
	*
	* switch ($action) {
	* 	case 'inventory-search':
	* 		Request Inventory on a seach query (itemid, lotserial)
	* 		Response: invsearch records
	*		DBNAME=$dplusdb
	*		INVSEARCH
	*		QUERY=$q
	*		break;
	*	case 'move-contents':
	*		Request Bin Contents to be Reassigned to another Bin
	*		Response: Whsesession Record has status of BINR
	*		DBNAME=$dplusdb
	*		MOVEBIN
	*		FROMBIN=$frombin
	*		TOBIN=$tobin
	*		break
	* }
	**/

	switch ($action) {
		case 'inventory-search':
			$q = strtoupper($input->$requestmethod->text('scan'));
			$data = array("DBNAME=$dplusdb", 'INVSEARCH', "QUERY=$q");
			$url = new Purl\Url($input->$requestmethod->text('page'));
			$url->query->set('scan', $q);
			$session->loc = $url->getUrl();
			break;
		case 'move-bin-contents';
			$frombin = strtoupper($input->$requestmethod->text('from-bin'));
			$tobin = strtoupper($input->$requestmethod->text('to-bin'));
			$data = array("DBNAME=$dplusdb", 'MOVEBIN', "FROMBIN=$frombin", "TOBIN=$tobin");
			$session->loc = $input->$requestmethod->text('page');
			$session->bincm = array('tobin' => $tobin, 'frombin' => $frombin);
			break;
	}

	if (!empty($data)) {
		write_dplusfile($data, $filename);
		$http = new WireHttp();
		$http->get("127.0.0.1/cgi-bin/".$config->cgis['warehouse']."?fname=$filename");
	}

	if (!empty($session->get('loc')) && !$config->ajax) {
		$session->redirect($session->loc, $http301 = false);
	}
