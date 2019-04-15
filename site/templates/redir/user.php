<?php
	// Figure out page request method, then grab needed inputs
	$requestmethod = $input->requestMethod('POST') ? 'post' : 'get';
	$action = $input->$requestmethod->text('action');
	$dplusdb = $modules->get('DplusConnectDatabase')->db_name;

	switch ($action) {
		case 'login':
			if ($input->post->username) {
				$username = $input->$requestmethod->text('username');
				$password = $input->$requestmethod->text('password');
				$data = array("DBNAME=$dplusdb", 'LOGPERM', "LOGINID=$username", "PSWD=$password");
				$session->loggingin = true;
				$session->loc = $config->pages->index.'redir/';
			}
			break;
	}
