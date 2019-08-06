<?php
	// Figure out page request method, then grab needed inputs
	$requestmethod = $input->requestMethod('POST') ? 'post' : 'get';
	$action = $input->$requestmethod->text('action');
	$dplusdb = $modules->get('DplusOnlineDatabase')->db_name;

	// Set up filename and sessionID in case this was made through cURL
	$filename  = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();
	$sessionID = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();

	$http = new WireHttp();


	/**
	* User REDIRECT
	*
	*
	* switch ($action) {
	* 	case 'login':
	* 		Request Login Authentication for User
	* 		Response: logperm record
	*		DBNAME=$dplusdb
	*		LOGPERM
	*		LOGINID=$username
	*		PSWD=$password
	*		break;
	*	case 'logout':
	*		Request Logout
	*		Response: Removes logperm records and session associated records
	*		DBNAME=$dplusdb
	*		LOGOUT
	*		break
	* }
	**/

	switch ($action) {
		case 'login':
			if ($input->post->username) {
				$username = $input->$requestmethod->text('username');
				$password = $input->$requestmethod->text('password');
				$data = array("DBNAME=$dplusdb", 'LOGPERM', "LOGINID=$username", "PSWD=$password");
				$session->loggingin = true;
				$session->loc = $pages->get('/redir/')->url;
			}
			break;
		case 'logout':
			$data = array("DBNAME=$dplusdb", 'LOGOUT');
			$session->loc = $pages->get('template=login')->url;

			// LOGOUT WAREHOUSE SESSION
			if (WhsesessionQuery::create()->sessionExists(session_id())) {
				$url = $pages->get('template=warehouse-menu, dplus_function=wm')->child('template=redir')->url."?action=logout&sessionID=".session_id();
				$http->get("127.0.0.1".$url);
			}
			break;
	}

	if (!empty($data)) {
		write_dplusfile($data, $filename);
		$http->get("127.0.0.1/cgi-bin/".$config->cgis['default']."?fname=$filename");
	}

	if (!empty($session->get('loc')) && !$config->ajax) {
		header("Location: $session->loc");
	}
