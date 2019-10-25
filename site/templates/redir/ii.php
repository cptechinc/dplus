<?php
	// Figure out page request method, then grab needed inputs
	$requestmethod = $input->requestMethod('POST') ? 'post' : 'get';
	$action = $input->$requestmethod->text('action');
	$dplusdb = $modules->get('DplusOnlineDatabase')->db_name;

	// Set up filename and sessionID in case this was made through cURL
	$filename  = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();
	$sessionID = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();

	$itemID = strtoupper($input->$requestmethod->text('itemID'));

	/**
	* ITEM REDIRECT
	*
	*
	* switch ($action) {
	* 	case 'ii-item':
	* 		Request II Item JSON file
	* 		Response: Creates II Item JSON file
	*		DBNAME=$dplusdb
	*		IISELECT
	*		ITEMID=$itemID
	*		break;
	*	case 'ii-stock':
	* 		Request II Stock JSON file
	* 		Response: Creates II Stock JSON file
	*		DBNAME=$dplusdb
	*		IISTKBYWHSE
	*		ITEMID=$itemID
	*		break;
	*	case 'ii-requirements':
	* 		Request II Requirements JSON file
	* 		Response: Creates II Requirements JSON file
	*		DBNAME=$dplusdb
	*		IIREQUIRE
	*		ITEMID=$itemID
	*		WHSE=$whse
	*		REQAVL=$view
	*		break;
	*	case 'ii-pricing':
	* 		Request II Pricing JSON file
	* 		Response: Creates II Pricing JSON file
	*		DBNAME=$dplusdb
	*		IIPRICE
	*		ITEMID=$itemID
	*		break;
	*	case 'ii-costing':
	* 		Request II Costing JSON file
	* 		Response: Creates II Costing JSON file
	*		DBNAME=$dplusdb
	*		IICOST
	*		ITEMID=$itemID
	*		break;
	*	case 'ii-usage':
	* 		Request II Usage JSON file
	* 		Response: Creates II Usage JSON file
	*		DBNAME=$dplusdb
	*		IIUSAGE
	*		ITEMID=$itemID
	*		break;
	*	case 'ii-activity':
	* 		Request II Activity JSON file
	* 		Response: Creates II Activity JSON file
	*		DBNAME=$dplusdb
	*		IIACTIVITY
	*		ITEMID=$itemID
	*		break;
	*	case 'ii-components':
	* 		Request II Components JSON file
	* 		Response: Creates II Components JSON file
	*		DBNAME=$dplusdb
	*		IIKIT
	*		IIBOMSINGLE
	*	    IIBOMCONS
	*		ITEMID=$itemID
	*		QTYNEEDED=$qty
	*		break;
	*	case 'ii-where-used':
	* 		Request II Where Used JSON file
	* 		Response: Creates II Where Used JSON file
	*		DBNAME=$dplusdb
	*		IIWHEREUSED
	*		ITEMID=$itemID
	*		break;
	*	case 'ii-lotserial':
	* 		Request II Lot / Serial JSON file
	* 		Response: Creates II Lot / Serial JSON file
	*		DBNAME=$dplusdb
	*		IILOTSER
	*		ITEMID=$itemID
	*		break;
	*	case 'ii-general':
	* 		Request II General JSON file
	* 		Response: Creates II General JSON file
	*		DBNAME=$dplusdb
	*		IIGENERAL
	*		ITEMID=$itemID
	*		break;
	*	case 'ii-notes':
	* 		Request II Notes JSON file
	* 		Response: Creates II Notes JSON file
	*		DBNAME=$dplusdb
	*		IINOTES
	*		ITEMID=$itemID
	*		break;
	*	case 'ii-misc':
	* 		Request II Misc JSON file
	* 		Response: Creates II Misc JSON file
	*		DBNAME=$dplusdb
	*		IIMISC
	*		ITEMID=$itemID
	*		break;
	*	case 'ii-substitutes':
	* 		Request II Substitutes JSON file
	* 		Response: Creates II Substitutes JSON file
	*		DBNAME=$dplusdb
	*		IISUB
	*		ITEMID=$itemID
	*		break;
	*	case 'ii-documents':
	* 		Request II Documents JSON file
	* 		Response: Creates II Documents JSON file
	*		DBNAME=$dplusdb
	*		DOCVIEW
	*		FLD1CD=IT
	*		FLD1DATA=$itemID
	*		FLD1DESC=$desc
	*		break;
	*	case 'ii-sales-orders':
	* 		Request II Sales Orders JSON file
	* 		Response: Creates II Sales Orders JSON file
	*		DBNAME=$dplusdb
	*		IISALESORDR
	*		ITEMID=$itemID
	*		break;
	*	case 'ii-sales-history':
	* 		Request II Sales History JSON file
	* 		Response: Creates II Sales History JSON file
	*		DBNAME=$dplusdb
	*		IISALESHIST
	*		ITEMID=$itemID
	*		break;
	* }
	**/

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
			$config_ii = $modules->get('ConfigsIi');
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
		case 'ii-kit':
			$config_ii = $modules->get('ConfigsIi');
			$data = array("DBNAME=$dplusdb");
			$qty = $input->$requestmethod->int('qty');

			$data[] = 'IIKIT';
			$data[] = "ITEMID=$itemID";
			$data[] = "QTYNEEDED=$qty";

			if ($input->$requestmethod->page) {
				$url = new Purl\Url($input->$requestmethod->text('page'));
				$url->query->set('qty', $qty);
				$session->loc = $url->getUrl();
			} else {
				$url = $pages->get('pw_template=ii-kit')->httpUrl."?itemID=$itemID&qty=$qty";
				$session->loc = $url;
			}
			break;
		case 'ii-bom':
			$config_ii = $modules->get('ConfigsIi');
			$data = array("DBNAME=$dplusdb");
			$qty = $input->$requestmethod->int('qty');

			$bomtype = $input->$requestmethod->text('bomtype');
			if ($bomtype == 'single') {
				$data[] = 'IIBOMSINGLE';
			} else {
				$data[] = 'IIBOMCONS';
			}

			$data[] = "ITEMID=$itemID";
			$data[] = "QTYNEEDED=$qty";

			if ($input->$requestmethod->page) {
				$url = new Purl\Url($input->$requestmethod->text('page'));
				$url->query->set('qty', $qty);
				$url->query->set('bomtype', $bomtype);
				$session->loc = $url->getUrl();
			} else {
				$url = $pages->get('pw_template=ii-bom')->httpUrl."?itemID=$itemID&qty=$qty";
				$url .= "&bomtype=$bomtype";
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
			// does not call ii-misc, ii-notes
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
		case 'ii-sales-orders':
			$data = array("DBNAME=$dplusdb", 'IISALESORDR', "ITEMID=$itemID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = $pages->get('pw_template=ii-sales-orders')->httpUrl."?itemID=$itemID";
				$session->loc = $url;
			}
			break;
		case 'ii-sales-history':
			$data = array("DBNAME=$dplusdb", 'IISALESHIST', "ITEMID=$itemID");

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
				$url = $pages->get('pw_template=ii-sales-history')->httpUrl."?itemID=$itemID&date=$date";
				$session->loc = $url;
			}
			break;
		case 'ii-quotes':
			$data = array("DBNAME=$dplusdb", 'IIQUOTE', "ITEMID=$itemID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = $pages->get('pw_template=ii-quotes')->httpUrl."?itemID=$itemID";
				$session->loc = $url;
			}
			break;
		case 'ii-purchase-orders':
			$data = array("DBNAME=$dplusdb", 'IIPURCHORDR', "ITEMID=$itemID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = $pages->get('pw_template=ii-purchase-orders')->httpUrl."?itemID=$itemID";
				$session->loc = $url;
			}
			break;
		case 'ii-purchase-history':
			$data = array("DBNAME=$dplusdb", 'IIPURCHHIST', "ITEMID=$itemID");

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
				$url = $pages->get('pw_template=ii-purchase-history')->httpUrl."?itemID=$itemID&date=$date";
				$session->loc = $url;
			}
			break;
		case 'item-search':
			$q = strtoupper($input->$requestmethod->text('q'));
			$custID = !empty($input->$requestmethod->custID) ? $input->$requestmethod->text('custID') : $config->defaultweb;
			$data = array("DBNAME=$dplusdb", "ITNOSRCH=$q", "CUSTID=$custID");
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
