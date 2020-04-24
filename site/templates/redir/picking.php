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
	* NOTE USES the whseman.log
	*
	*
	* switch ($action) {
	* 	case 'initiate-whse':
	* 		Request Login into warehouse management
	* 		Response: Creates whsesession record
	*		DBNAME=$dplusdb
	*		LOGIN=$loginID
	*		break;
	*	case 'start-pick':
	*		Requests Picking Function to start
	*		Response: sets function to PICKING in whsesession record
	*		DBNAME=$dplusdb
	*		LOGIN=$loginID
	*		PICKING
	*		break;
	*	case 'start-pick-pack':
	*		Requests Pick Pack Function to start
	*		Response: sets function to PACKING in whsesession record
	*		  NOTE Unused
	*		DBNAME=$dplusdb
	*		LOGIN=$user->loginid
	*		PACKING
	*		break;
	*	case 'logout':
	*		Request Logout of Warehouse Management
	*		Response: Remove whsesession record, removes session associated warehouse records
	*		DBNAME=$dplusdb
	*		LOGOUT
	*		break;
	*	case 'start-order':
	*		Request Order to PICK / PICKPACK
	*		Response: creates wmpickhed wmpickdet records for Order
	*		DBNAME=$dplusdb
	*		STARTORDER
	*		ORDERNBR=$ordn
	*		break;
	*	case 'select-bin':
	*		Request Starting bin to be changed
	*		Response: orders wmpickdet, whsesession records
	*		DBNAME=$dplusdb
	*		SETBIN=$bin
	*		break;
	*	case 'next-bin':
	*		Request Next bin to be changed
	*		Response: updates, reorders wmpickdet, whsesession records
	*		DBNAME=$dplusdb
	*		NEXTBIN
	*		break;
	*	case 'add-pallet':
	*		Request next available pallet number for this Order
	*		Response: updates wmpickhed, whsesssion records
	*		DBNAME=$dplusdb
	*		NEWPALLET
	*		break;
	*	case 'set-pallet':
	*		Request to set pallet number to provided pallet
	*		Response: updates wmpickhed, wmpickdet, whsesssion records
	*		DBNAME=$dplusdb
	*		GOTOPALLET=$palletnbr
	*		break;
	*	case 'finish-item':
	*		Request Finish Line Picking, Sends picked Item qtys, applicable lotserials
	*		Response: updates wmpickdet, whsesssion records
	*		DBNAME=$dplusdb
	*		ACCEPTITEM
	*		ORDERNBR=$ordn
	*		LINENBR=$linenbr
	*		ITEMID=$itemID
	*		Switch ($whsesession->function)
	*			PICKING
	*				ITEMQTY=$totalpicked
	*			PACKING ** One Line for each pallet
	*				PALLETNBR=$palletnbr|QTY=$qty
	*			PICKUNGUIDED
	*				NOTE NORMAL ITEMS GET ONE LINE PER BIN
	*				     LOTTED / SERIALIZED GET ONLINE PER LOT / SERIAL NUMBER
	*				BIN=$bin|LOTSERIAL=$lotserial|QTY=$qty
	*		break;
	*	case 'skip-item':
	*		Request picking item to be skipped for now
	*		Response: updates wmpickdet, whsesssion records
	*		DBNAME=$dplusdb
	*		SKIPITEM
	*		ORDERNBR=$ordn
	*		LINENBR=$linenbr
	*		break;
	*	case 'finish-order':
	*		Request finish picking order
	*		Response: updates wmpickdet, whsesssion records
	*		DBNAME=$dplusdb
	*		COMPLETEORDER
	*		ORDERNBR=$ordn
	*		break;
	*	case 'exit-order':
	*		Request exit picking order
	*		Response: updates wmpickdet, whsesssion records, order locks
	*		DBNAME=$dplusdb
	*		STOPORDER
	*		ORDERNBR=$ordn
	*		break;
	*	case 'cancel-order':
	*		Request cancel picking order
	*		Response: updates wmpickdet, whsesssion records, order locks
	*		DBNAME=$dplusdb
	*		CANCELSTART
	*		ORDERNBR=$ordn
	*		break;
	*	case 'remove-order-locks':
	*		Request remove order locks
	*		Response: updates wmpickdet, whsesssion records, order locks
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
		case 'start-pick-unguided':
			$data = array("DBNAME=$dplusdb", 'PICKUNGUIDED');
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
		case 'remove-picked-item':
			$whsesession = WhsesessionQuery::create()->findOneBySessionid($sessionID);
			$recordnumber = $input->get->int('recordnumber');
			$whseitempick = WhseitempickQuery::create()->filterBySessionid($sessionID)->filterByOrdn($whsesession->ordernbr)->filterByRecordnumber($recordnumber)->findOne();
			$data = array("DBNAME=$dplusdb", 'PICKITEMREMOVE', "ORDERNBR=$whsesession->ordernbr", "LINENBR=$whseitempick->linenbr", "ITEMID=$whseitempick->itemid");
			$data[] = "BIN=$whseitempick->bin|LOTSERIAL=$whseitempick->lotserial|QTY=$whseitempick->qty";
			$session->loc = $input->$requestmethod->text('page');
			break;
		case 'finish-item':
			$whsesession = WhsesessionQuery::create()->findOneBySessionid($sessionID);
			$linenbr = $input->get->int('linenbr');
			$pickitem = PickSalesOrderDetailQuery::create()->filterBySessionidOrder($sessionID, $whsesession->ordernbr)->findOneByLinenbr($linenbr);
			$data = array("DBNAME=$dplusdb", 'ACCEPTITEM', "ORDERNBR=$whsesession->ordernbr");

			if ($whsesession->is_pickingunguided()) {
				$linenbr = $input->get->int('linenbr');
				$pickitem = PickSalesOrderDetailQuery::create()->filterBySessionidOrder($sessionID, $whsesession->ordernbr)->findOneByLinenbr($linenbr);
				$data = array("DBNAME=$dplusdb", 'ACCEPTITEM', "ORDERNBR=$whsesession->ordernbr", "LINENBR=$pickitem->linenbr", "ITEMID=$pickitem->itemnbr");
				$itemmaster_query = ItemMasterItemQuery::create();

				if ($itemmaster_query->is_item_serialized($pickitem->itemnbr) || $itemmaster_query->is_item_lotted($pickitem->itemnbr)) {
					$barcodes = $pickitem->get_userpickedtotalsbylotserial();

					foreach ($barcodes as $barcode) {
						$data[] = "BIN=$barcode->bin|LOTSERIAL=$barcode->lotserial|QTY=$barcode->qty";
					}
				} else {
					$barcodes = $pickitem->get_userpickedtotalsbybin();

					foreach ($barcodes as $barcodse) {
						$binID     = str_pad($barcode['bin'], 8, ' ');
						$lotserial = str_pad('', 20, ' ');
						$qty       = $barcode['qty'];
						$data[]    = "BIN=$binID|LOTSERIAL=$lotserial|QTY=$qty";
					}
				}

				if ($input->$requestmethod->page) {
					$url = new Purl\Url($input->$requestmethod->text('page'));
					$url->query->remove('linenbr');
					$input->$requestmethod->page = $url->getUrl();
				}
			}
			$session->loc = $input->$requestmethod->text('page');
			//WhseitempickQuery::create()->filterBySessionidOrderLinenbr(session_id(), $whsesession->ordn, $linenbr)->delete();
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
			$url->query->remove('linenbr');
			$session->loc = $url->getUrl();
			WhseitempickQuery::create()->filterBySessionidOrder(session_id(), $whsesession->ordn)->delete();
			break;
		case 'exit-order':
			$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
			$data = array("DBNAME=$dplusdb", 'STOPORDER', "ORDERNBR=$whsesession->ordn");
			$url = new Purl\Url($input->$requestmethod->text('page'));
			$url->query->remove('ordn');
			$url->query->remove('linenbr');
			$url->query->remove('scan');
			$session->loc = $url->getUrl();
			WhseitempickQuery::create()->filterBySessionidOrder(session_id(), $whsesession->ordn)->delete();
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
		case 'scan-pick-item':
			$q = strtoupper($input->$requestmethod->text('scan'));
			$data = array("DBNAME=$dplusdb", 'PICKSEARCH', "QUERY=$q");

			if ($input->$requestmethod->page) {
				$url = new Purl\Url($input->$requestmethod->text('page'));
			} else {
				$url = new Purl\Url($pages->get('pw_template=whse-picking'));
			}

			$url->query->set('scan', $q);
			$session->loc = $url->getUrl();
			break;
	}

	if (!empty($data)) {
		write_dplusfile($data, $filename);

		if (!$input->$requestmethod->debug) {
			$http = new WireHttp();
			$http->get("127.0.0.1/cgi-bin/".$config->cgis['warehouse']."?fname=$filename");
		}
	}

	if (!empty($session->get('loc')) && !$config->ajax) {
		$session->redirect($session->loc, $http301 = false);
	}
