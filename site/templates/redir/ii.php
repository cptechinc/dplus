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
		case 'item-pricing-multiple':
			$itemIDs = $input->$requestmethod->array('itemID');
			$custID = !empty($input->$requestmethod->custID) ? $input->$requestmethod->text('custID') : $config->defaultweb;
			$data = array("DBNAME=$dplusdb", "ITMPRIMULT", "CUSTID=$custID");
			$itemIDs = $input->$requestmethod->array('itemID');

			foreach ($itemIDs as $itemID) {
				$data[] = "ITEMID=$itemID";
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
