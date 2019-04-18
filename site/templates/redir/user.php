<?php
	// Figure out page request method, then grab needed inputs
	$requestmethod = $input->requestMethod('POST') ? 'post' : 'get';
	$action = $input->$requestmethod->text('action');
	$dplusdb = $modules->get('DplusOnlineDatabase')->db_name;

	// Set up filename and sessionID in case this was made through cURL
	$filename  = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();
	$sessionID = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();

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

			// if (WhseSession::does_sessionexist(session_id())) {
			// 	$whsesession = WhseSession::load(session_id());
			// 	$whsesession->end_session();
			// }
			break;
	}

	if (!empty($data)) {
		write_dplusfile($data, $filename);
		$http = new WireHttp();
		$http->get("127.0.0.1/cgi-bin/".$config->cgis['default']."?fname=$filename");
	}

	if (!empty($session->get('loc')) && !$config->ajax) {
		header("Location: $session->loc");
	}
