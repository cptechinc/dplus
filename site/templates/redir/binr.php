<?php
	// Figure out page request method, then grab needed inputs
	$requestmethod = $input->requestMethod('POST') ? 'post' : 'get';
	$action = $input->$requestmethod->text('action');
	$dplusdb = $modules->get('DplusOnlineDatabase')->db_name;

	// Set up filename and sessionID in case this was made through cURL
	$filename  = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();
	$sessionID = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();

	/**
	* BINR REDIRECT
	* NOTE uses whseman.log
	*
	* switch ($action) {
	* 	case 'inventory-search':
	* 		Request Inventory on a seach query (itemid, lotserial)
	* 		Response: invsearch records
	*		DBNAME=$dplusdb
	*		INVSEARCH
	*		QUERY=$q
	*		break;
	*	case 'search-item-bins':
	*		Request Bins that Item is in
	*		Response: Bininfo Records with Bins that contain this item
	*		DBNAME=$dplusdb
	*		BININFO
	*		ITEMID=$itemID
	*		break;
	*	case 'bin-reassign':
	*		Request Item Bin Reassignment
	*		Response: Whsesession Record has status of BINR
	*		DBNAME=$dplusdb
	*		BINR
	*		ITEMID=$itemID
	*		** SERIALNBR=$serialnbr | LOTNBR=$lotnbr
	*		QTY=$qty
	*		FROMBIN=$frombin
	*		TOBIN=$tobin
	*		break;
	*	case 'move-contents':
	*		Request Bin Contents to be Reassigned to another Bin
	*		Response: Whsesession Record has status of BINR
	*		DBNAME=$dplusdb
	*		MOVEBIN
	*		FROMBIN=$frombin
	*		TOBIN=$tobin
	*		break
	* }
	**/

	switch ($action) {
		case 'inventory-search':
			$q = strtoupper($input->$requestmethod->text('scan'));
			$data = array("DBNAME=$dplusdb", 'INVSEARCH', "QUERY=$q");
			$url = new Purl\Url($input->$requestmethod->text('page'));
			$url->query->set('scan', $q);
			$session->loc = $url->getUrl();
			break;
		case 'search-item-bins':
			$itemID = $input->$requestmethod->text('itemID');
			$binID = $input->$requestmethod->text('binID');
			$data = array("DBNAME=$dplusdb", 'BININFO', "ITEMID=$itemID");
			$returnurl = new Purl\Url($input->$requestmethod->text('page'));
			$returnurl->query->remove('scan');

			if ($input->$requestmethod->serialnbr || $input->$requestmethod->lotnbr) {
				if ($input->$requestmethod->serialnbr) {
					$lotserial = $input->$requestmethod->text('serialnbr');
					$returnurl->query->set('serialnbr', $lotserial);
				} else {
					$lotserial = $input->$requestmethod->text('lotnbr');
					$returnurl->query->set('lotnbr', $lotserial);
				}
				$data[] = "LOTSERIAL=$lotserial";
			} else {
				$returnurl->query->set('itemID', $itemID);
			}

			if (!empty($binID)) {
				$returnurl->query->set('binID', $binID);
			}
			$session->loc = $returnurl->getUrl();
			break;
		case 'bin-reassign':
			$itemID = $input->$requestmethod->text('itemID');
			$frombin = $input->$requestmethod->text('from-bin');
			$qty = $input->$requestmethod->text('qty');
			$tobin = $input->$requestmethod->text('to-bin');
			$data = array("DBNAME=$dplusdb", 'BINR', "ITEMID=$itemID");

			if ($input->$requestmethod->serialnbr) {
				$serialnbr = $input->$requestmethod->text('serialnbr');
				$data[] = "SERIALNBR=$serialnbr";
			}
			if ($input->$requestmethod->lotnbr) {
				$lotnbr = $input->$requestmethod->text('lotnbr');
				$data[] = "LOTNBR=$lotnbr";
			}
			$data[] = "QTY=$qty";
			$data[] = "FROMBIN=$frombin";
			$data[] = "TOBIN=$tobin";
			$url = new Purl\Url($input->$requestmethod->text('page'));

			if ($url->query->has('tobin')) {
				$url->query->set('tobin', $tobin);
			} elseif ($url->query->has('frombin')) {
				$url->query->set('frombin', $frombin);
			}
			$session->loc = $input->$requestmethod->text('page');
			$session->binr = array('frombin' => $frombin, 'tobin' => $tobin);
			break;
		case 'move-bin-contents';
			$frombin = $input->$requestmethod->text('from-bin');
			$tobin = $input->$requestmethod->text('to-bin');
			$data = array("DBNAME=$dplusdb", 'MOVEBIN', "FROMBIN=$frombin", "TOBIN=$tobin");
			$session->loc = $input->$requestmethod->text('page');
			$session->bincm = array('tobin' => $tobin, 'frombin' => $frombin);
			break;
	}

	if (!empty($data)) {
		write_dplusfile($data, $filename);
		$http = new WireHttp();
		$http->get("127.0.0.1/cgi-bin/".$config->cgis['warehouse']."?fname=$filename");
	}

	if (!empty($session->get('loc')) && !$config->ajax) {
		$session->redirect($session->loc, $http301 = false);
	}
