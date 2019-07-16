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
		case 'ii-costing':
			$data = array("DBNAME=$dplusdb", 'IICOST', "ITEMID=$itemID");
			$session->loc = $input->$requestmethod->text('page');
			break;
		case 'ii-usage':
			$data = array("DBNAME=$dplusdb", 'IIUSAGE', "ITEMID=$itemID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = $pages->get('pw_template=ii-where-used')->httpUrl."?itemID=$itemID";
				$session->loc = $url;
			}
			break;
		case 'ii-activity':
			$data = array("DBNAME=$dplusdb", 'IIACTIVITY', "ITEMID=$itemID");
			$date = $input->$requestmethod->text('date');

			if (!empty($date)) {
				$date_ymd = date('Ymd', strtotime($date));
				$data[] = "DATE=$date_ymd";
			}
			if ($input->$requestmethod->page) {
				$url = new Purl\Url($input->$requestmethod->text('page'));
				$url->query->set('date', $date);
				$session->loc = $url->getUrl();
			} else {
				$url = $pages->get('pw_template=ii-activity')->httpUrl."?itemID=$itemID&date=$date";
				$session->loc = $url;
			}
			break;
		case 'ii-components':
			$config_ii = $modules->get('IiConfig');
			$data = array("DBNAME=$dplusdb");
			$qty = $input->$requestmethod->int('qty');

			if ($config_ii->option_components == 'kit') {
				$data[] = 'IIKIT';
			} elseif ($config_ii->option_components == 'bom') {
				$bomtype = $input->$requestmethod->text('bomtype');
				if ($bomtype == 'single') {
					$data[] = 'IIBOMSINGLE';
				} else {
					$data[] = 'IIBOMCONS';
				}
			} else {

			}

			$data[] = "ITEMID=$itemID";
			$data[] = "QTYNEEDED=$qty";

			if ($input->$requestmethod->page) {
				$url = new Purl\Url($input->$requestmethod->text('page'));
				$url->query->set('qty', $qty);
				if ($config_ii->option_components == 'bom') {
					$url->query->set('bomtype', $bomtype);
				}
				$session->loc = $url->getUrl();
			} else {
				$url = $pages->get('pw_template=ii-components')->httpUrl."?itemID=$itemID&qty=$qty";
				if ($config_ii->option_components == 'bom') {
					$url .= "&bomtype=$bomtype";
				}
				$session->loc = $url;
			}
			break;
		case 'ii-where-used':
			$data = array("DBNAME=$dplusdb", 'IIWHEREUSED', "ITEMID=$itemID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = $pages->get('pw_template=ii-where-used')->httpUrl."?itemID=$itemID";
				$session->loc = $url;
			}
			break;
		case 'ii-lotserial':
			$data = array("DBNAME=$dplusdb", 'IILOTSER', "ITEMID=$itemID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = $pages->get('pw_template=ii-lotserial')->httpUrl."?itemID=$itemID";
				$session->loc = $url;
			}
			break;
		case 'ii-general':
			$data = array("DBNAME=$dplusdb", 'IIGENERAL', "ITEMID=$itemID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = $pages->get('pw_template=ii-general')->httpUrl."?itemID=$itemID";
				$session->loc = $url;
			}
			break;
		case 'ii-usage':
			$data = array("DBNAME=$dplusdb", 'IIUSAGE', "ITEMID=$itemID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = $pages->get('pw_template=ii-general')->httpUrl."?itemID=$itemID";
				$session->loc = $url;
			}
			break;
		case 'ii-notes':
			$data = array("DBNAME=$dplusdb", 'IINOTES', "ITEMID=$itemID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = $pages->get('pw_template=ii-general')->httpUrl."?itemID=$itemID";
				$session->loc = $url;
			}
			break;
		case 'ii-misc':
			$data = array("DBNAME=$dplusdb", 'IIMISC', "ITEMID=$itemID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = $pages->get('pw_template=ii-general')->httpUrl."?itemID=$itemID";
				$session->loc = $url;
			}
			break;
		case 'ii-substitutes':
			$data = array("DBNAME=$dplusdb", 'IISUB', "ITEMID=$itemID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = $pages->get('pw_template=ii-substitutes')->httpUrl."?itemID=$itemID";
				$session->loc = $url;
			}
			break;
		case 'ii-documents':
			$desc = ItemMasterItemQuery::create()->select(ItemMasterItem::get_aliasproperty('desc'))->findOneByItemid($itemID);
			$data = array("DBNAME=$dplusdb", 'DOCVIEW', "FLD1CD=IT", "FLD1DATA=$itemID", "FLD1DESC=$desc");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = $pages->get('pw_template=ii-documents')->httpUrl."?itemID=$itemID";
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
