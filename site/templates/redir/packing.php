<?php
	// Figure out page request method, then grab needed inputs
	$requestmethod = $input->requestMethod('POST') ? 'post' : 'get';
	$action = $input->$requestmethod->text('action');
	$dplusdb = $modules->get('DplusOnlineDatabase')->db_name;

	// Set up filename and sessionID in case this was made through cURL
	$filename  = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();
	$sessionID = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();

	switch ($action) {
		case 'start-pack':
			$data = array("DBNAME=$dplusdb", "STARTPACK");
			break;
		case 'get-pack-notes':
			$ordn = $input->$requestmethod->text('ordn');
			$data = array("DBNAME=$dplusdb", "LQNOTE=SORD", "KEY1=$ordn", "KEY2=0");
			break;
		case 'finish-pack':
			$ordn = $input->$requestmethod->text('ordn');
			$data = array("DBNAME=$dplusdb", "ORDERNBR=$ordn");
			// FOREACH DIFFERENT CARTON ITEM FOR LINE
			$data[] = "CARTON=$carton|LINENBR=$linenbr|ITEMID=$itemid|LOTSERIAL=$lotserial|QTY=$qty";
			break;
	}

	if (!empty($data)) {
		write_dplusfile($data, $filename);
		$http = new WireHttp();
		$http->get("127.0.0.1/cgi-bin/".$config->cgis['warehouse']."?fname=$filename");
	}

	if (!empty($session->get('loc')) && !$config->ajax) {
		$session->redirect($session->loc);
	}
