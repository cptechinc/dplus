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
		case 'order-quote':
			$qnbr = $input->$requestmethod->text('qnbr');
			$data = array("DBNAME=$dplusdb", 'QUOTETOORDER', "QUOTENO=$qnbr", "LINENO=ALL");
			$session->loc = $pages->get('template=dplus-menu,name=mso')->child('template=redir')->url."?action=edit-new-order";
			break;
		case 'add-popular-items':
			$qnbr = $input->$requestmethod->text('qnbr');
			$data = array("DBNAME=$dplusdb", 'QUOTEADDMULTIPLE', "QUOTENO=$qnbr");
			$qtys = $input->$requestmethod->array('qty');
			$itemIDs = $input->$requestmethod->array('itemID');

			for ($i = 0; $i < sizeof($qtys); $i++) {
				if (!empty($qtys[$i])) {
					$itemID = str_pad($itemIDs[$i], 30, ' ');
					$qty = $qtys[$i];
					$data[] = "ITEMID={$itemID}QTY=$qty";
				}
			}

			if ($input->$requestmethod->page) {
				$url = new Purl\Url($input->$requestmethod->text('page'));
				$session->loc = $url->getUrl();
			} else {
				$session->loc = $pages->get('pw_template=quote-edit')->url."?ordn=$ordn";
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
