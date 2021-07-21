<?php
	// Figure out page request method, then grab needed inputs
	$requestmethod = $input->requestMethod('POST') ? 'post' : 'get';

	$action = $input->$requestmethod->text('action');
	$dplusdb = $modules->get('DplusOnlineDatabase')->db_name;

	// Set up filename and sessionID in case this was made through cURL
	$filename  = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();
	$sessionID = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();


	/**
	* QUOTE REDIRECT
	*
	*
	* switch ($action) {
	*	case 'edit-new-quote':
	*		- Loads Quote for editing
	*		DBNAME=$dplusdb
	*		EDITQUOTE
	*		QUOTENO=$qnbr
	*		break;
	* }
	**/

	switch ($action) {
		case 'edit-new-quote':
			$qnbr = $user->get_lockedID();
			$data = array("DBNAME=$dplusdb", 'EDITQUOTE', "QUOTENO=$qnbr");
			$session->loc = $pages->get('pw_template=quote-edit')->url."?qnbr=$qnbr";
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
