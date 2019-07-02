<?php
	// Figure out page request method, then grab needed inputs
	$requestmethod = $input->requestMethod('POST') ? 'post' : 'get';
	$action = $input->$requestmethod->text('action');
	$dplusdb = $modules->get('DplusOnlineDatabase')->db_name;

	// Set up filename and sessionID in case this was made through cURL
	$filename  = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();
	$sessionID = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();

	$itemID = strtoupper($input->$requestmethod->text('itemID'));

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
		case 'ii-stock':
			$data = array("DBNAME=$dplusdb", 'IISTKBYWHSE', "ITEMID=$itemID");
			if ($input->$requestmethod->page) {
				$url = new Purl\Url($input->$requestmethod->text('page'));
				$url->query->remove('q');
				$url->query->set('itemID', $itemID);
				$session->loc = $url->getUrl();
			} else {
				$url = $pages->get('pw_template=ii-stock')->httpUrl."?itemID=$itemID";
				$session->loc = $url;
			}
			break;
		case 'ii-requirements':
			$whse = $input->$requestmethod->text('whseID');
			$view = $input->$requestmethod->text('view');
			//screen type would be REQ or AVL
			$data = array("DBNAME=$dplusdb", 'IIREQUIRE', "ITEMID=$itemID", "WHSE=$whse", "REQAVL=$view");

			if ($input->$requestmethod->page) {
				$url = new Purl\Url($input->$requestmethod->text('page'));
				$url->query->set('itemID', $itemID);

				if (isset($input->$requestmethod->whse)) {
					$url->query->set('whseID', $whse);
					$url->query->set('view', $view);
				}
				$session->loc = $url->getUrl();
			} else {
				$url = $pages->get('pw_template=ii-requirements')->httpUrl."?itemID=$itemID";
				$session->loc = $url;
			}
			break;
		case 'ii-pricing':
			$data = array("DBNAME=$dplusdb", 'IIPRICE', "ITEMID=$itemID");
			$custID = $input->$requestmethod->text('custID');
			$shipID = $input->$requestmethod->text('shipID');

			if (!empty($custID)) {
				$data['CUSTID'] = $custID;
				if (!empty($shipID)) {
					$data['SHIPID'] = $shipID;
				}
			}
			if ($input->$requestmethod->page) {
				$url = new Purl\Url($input->$requestmethod->text('page'));
				$url->query->set('itemID', $itemID);
				$session->loc = $url->getUrl();
			} else {
				$url = $pages->get('pw_template=ii-pricing')->httpUrl."?itemID=$itemID";
				$session->loc = $url;
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
