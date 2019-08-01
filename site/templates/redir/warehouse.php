<?php
	// Figure out page request method, then grab needed inputs
	$requestmethod = $input->requestMethod('POST') ? 'post' : 'get';
	$action = $input->$requestmethod->text('action');
	$dplusdb = $modules->get('DplusOnlineDatabase')->db_name;

	// Set up filename and sessionID in case this was made through cURL
	$filename  = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();
	$sessionID = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();

	/**
	* Warehouse REDIRECT
	* NOTE Uses whseman.log
	*
	* switch ($action) {
	* 	case 'login':
	* 		Request Warehouse Login for User
	* 		Response: whsesession record
	*		DBNAME=$dplusdb
	*		LOGPERM
	*		LOGINID=$username
	*		PSWD=$password
	*		break;
	*	case 'logout':
	*		Request Logout
	*		Response: Removes whsesession records and session associated records
	*		DBNAME=$dplusdb
	*		LOGOUT
	*		break
	* }
	**/

	switch ($action) {
		case 'login':
			$q = LogpermQuery::create();
			$logperm = $q->findOneBySessionid($sessionID);
			$data = array("DBNAME=$dplusdb", "LOGIN=$logperm->loginid");
			break;
		case 'logout':
			$data = array("DBNAME=$dplusdb", 'LOGOUT');
			break;
	}

	if (!empty($data)) {
		write_dplusfile($data, $filename);
		$http = new WireHttp();
		$http->get("127.0.0.1/cgi-bin/".$config->cgis['warehouse']."?fname=$filename");
	}

	if (!empty($session->get('loc')) && !$config->ajax) {
		header("Location: $session->loc");
	}
