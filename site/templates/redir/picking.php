<?php
	// Figure out page request method, then grab needed inputs
	$requestmethod = $input->requestMethod('POST') ? 'post' : 'get';
	$action = $input->$requestmethod->text('action');
	$dplusdb = $modules->get('DplusOnlineDatabase')->db_name;

	// Set up filename and sessionID in case this was made through cURL
	$filename  = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();
	$sessionID = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();

	/**
	* PICKING ORDERS REDIRECT
	* USES the whseman.log
	*
	*
	*
	*
	* switch ($action) {
	* 	case 'initiate-whse':
	* 		- Logs into warehouse management creates whsesession record
	*		DBNAME=$dplusdb
	*		LOGIN=$loginID
	*		break;
	*	case 'start-pick':
	*		- Starts PICKING function for session, updates whsesession record
	*		DBNAME=$dplusdb
	*		LOGIN=$loginID
	*		PICKING
	*		break;
	*	case 'start-pick-pack':
	*		- Starts PICK PACK function for session, updates whsesession record
	*		  NOTE Park / U2 Only for now
	*		DBNAME=$dplusdb
	*		LOGIN=$user->loginid
	*		PACKING
	*		break;
	*	case 'logout':
	*		- Logs out of warehouse management clears whsesession record
	*		DBNAME=$dplusdb
	*		LOGOUT
	*		break;
	*	case 'start-order':
	*		- Requests the Order Number to start PICKING / PACKING
	*		DBNAME=$dplusdb
	*		STARTORDER
	*		ORDERNBR=$ordn
	*		break;
	*	case 'select-bin':
	*		- Sets the Starting Bin
	*		DBNAME=$dplusdb
	*		SETBIN=$bin
	*		break;
	*	case 'next-bin':
	*		- Requests the Next Bin to be assigned
	*		DBNAME=$dplusdb
	*		NEXTBIN
	*		break;
	*	case 'add-pallet':
	*		- Requests another Pallet number to be assigned to tthis Order #
	*		DBNAME=$dplusdb
	*		NEWPALLET
	*		break;
	*	case 'set-pallet':
	*		- Requests the pallet number to set to X
	*		DBNAME=$dplusdb
	*		GOTOPALLET=$palletnbr
	*		break;
	*	case 'finish-item':
	*		- Request to finish Item picking
	*		DBNAME=$dplusdb
	*		ACCEPTITEM
	*		ORDERNBR=$ordn
	*		LINENBR=$linenbr
	*		ITEMID=$itemID
	*		ITEMQTY=$totalpicked
	*		break;
	*	case 'accept-item':
	*		- Request to finish Item pick-packing
	*		DBNAME=$dplusdb
	*		ACCEPTITEM
	*		ORDERNBR=$ordn
	*		LINENBR=$linenbr
	*		ITEMID=$itemID
	*		PALLETNBR=$pallet|QTY=$qty  // NOTE 1 LINE FOR EACH PALLET
	*		break;
	*	case 'skip-item':
	*		- Request to skip this item
	*		DBNAME=$dplusdb
	*		SKIPITEM
	*		ORDERNBR=$ordn
	*		LINENBR=$linenbr
	*		break;
	*	case 'finish-order':
	*		// Finish the order
	*		DBNAME=$dplusdb
	*		COMPLETEORDER
	*		ORDERNBR=$ordn
	*		break;
	*	case 'exit-order':
	*		// Leave the order
	*		DBNAME=$dplusdb
	*		STOPORDER
	*		ORDERNBR=$ordn
	*		break;
	*	case 'cancel-order':
	*		// Cancel the order Pick
	*		DBNAME=$dplusdb
	*		CANCELSTART
	*		ORDERNBR=$ordn
	*		break;
	*	case 'remove-order-locks':
	*		// Removes Order Pick Locks
	*		DBNAME=$dplusdb
	*		REFRESHPD
	*		ORDERNBR=$ordn
	*		break;
	* }
	**/

	switch ($action) {
		case 'initiate-whse':
			$login = get_loginrecord($sessionID);
			$loginID = $login['loginid'];
			$data = array("DBNAME=$dplusdb", "LOGIN=$loginID");
			break;
		case 'start-pick':
			$data = array("DBNAME=$dplusdb", 'PICKING');
			break;
		case 'start-pick-pack':
			$data = array("DBNAME=$dplusdb", 'PACKING');
			break;
		case 'logout':
			$data = array("DBNAME=$dplusdb", 'LOGOUT');
			$session->loc = $config->pages->salesorderpicking;
			break;
		case 'start-order':
			$ordn = $input->$requestmethod->text('ordn');
			$url = new Purl\Url($input->$requestmethod->text('page'));
			$data = array("DBNAME=$dplusdb", 'STARTORDER', "ORDERNBR=$ordn");
			$url->query->set('ordn', $ordn);
			$session->loc = $url->getUrl();
			break;
		case 'select-bin':
			$bin = strtoupper($input->$requestmethod->text('binID'));
			$data = array("DBNAME=$dplusdb", "SETBIN=$bin");
			$session->loc = $input->$requestmethod->text('page');
			break;
		case 'next-bin':
			$data = array("DBNAME=$dplusdb", 'NEXTBIN');
			$session->loc = $input->$requestmethod->text('page');
			break;
		case 'add-pallet':
			$data = array("DBNAME=$dplusdb", 'NEWPALLET');
			$session->loc = $input->$requestmethod->text('page');
			break;
		case 'set-pallet':
			$palletnbr = $input->$requestmethod->text('palletnbr');
			$data = array("DBNAME=$dplusdb", "GOTOPALLET=$palletnbr");
			$session->loc = $input->$requestmethod->text('page');
			break;
		case 'finish-item':
			$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
			$item = PickSalesOrderDetailQuery::create()->findOneBySessionid(session_id());
			$data = array("DBNAME=$dplusdb", 'ACCEPTITEM', "ORDERNBR=$item->ordernbr ", "LINENBR=$item->linenbr", "ITEMID=$item->itemnbr");

			if ($whsesession->is_picking()) {
				$totalpicked = $item->get_userpickedtotal();
				$data[] = "ITEMQTY=$totalpicked";
			} elseif ($whsesession->is_pickingpacking()) {
				$pallet_totals = $item->get_userpickedtotalsbypallet();

				foreach ($pallet_totals as $pallet) {
					$palletnbr = str_pad($pallet['palletnbr'], 4, ' ');
					$qty = str_pad($pallet['qty'], 10, ' ');
					$data[] = "PALLETNBR=$palletnbr|QTY=$qty";
				}
			}
			$session->loc = $input->$requestmethod->text('page');
			break;
		case 'skip-item':
			$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
			$pickitem = PickSalesOrderDetailQuery::create()->findOneBySessionid(session_id());
			$data = array("DBNAME=$dplusdb", 'SKIPITEM', "ORDERNBR=$pickitem->ordn", "LINENBR=$pickitem->linenbr");
			$session->loc = $input->$requestmethod->text('page');
			break;
		case 'finish-order':
			$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
			$data = array("DBNAME=$dplusdb", 'COMPLETEORDER', "ORDERNBR=$whsesession->ordn");
			$url = new Purl\Url($input->$requestmethod->text('page'));
			$url->query->remove('ordn');
			$session->loc = $url->getUrl();
			break;
		case 'exit-order':
			$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
			$data = array("DBNAME=$dplusdb", 'STOPORDER', "ORDERNBR=$whsesession->ordn");
			$url = new Purl\Url($input->$requestmethod->text('page'));
			$url->query->remove('ordn');
			$session->loc = $url->getUrl();
			break;
		case 'cancel-order':
			$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
			$data = array("DBNAME=$dplusdb", 'CANCELSTART', "ORDERNBR=$whsesession->ordn");
			$session->loc = $input->$requestmethod->text('page');
			break;
		case 'remove-order-locks':
			$ordn = $input->$requestmethod->text('ordn');
			$page = $input->$requestmethod->text('page');
			$data = array("DBNAME=$dplusdb", 'REFRESHPD', "ORDERNBR=$ordn");

			if (!empty($page)) {
				$session->loc = $page;
			} else {
				$session->loc = $config->pages->salesorderpicking;
			}
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
