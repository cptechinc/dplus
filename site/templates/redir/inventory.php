<?php
	// Figure out page request method, then grab needed inputs
	$requestmethod = $input->requestMethod('POST') ? 'post' : 'get';
	$action = $input->$requestmethod->text('action');
	$dplusdb = $modules->get('DplusOnlineDatabase')->db_name;

	// Set up filename and sessionID in case this was made through cURL
	$filename  = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();
	$sessionID = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();

	/**
	* Inventory Redirect
	* NOTE Uses whseman.log
	**/

	switch ($action) {
		case 'inventory-search':
			// Requests Inventory records for Query (itemid, lotnbr, lotserial)
			// Response: fills Invsearch table with records
			$q = strtoupper($input->$requestmethod->text('scan'));
			$binID = $input->$requestmethod->text('binID');
			$data = array("DBNAME=$dplusdb", 'INVSEARCH', "QUERY=$q");

			if ($input->$requestmethod->page) {
				$url = new Purl\Url($input->$requestmethod->text('page'));
				$url->query->set('scan', $q);
				$url->query->set('binID', $binID);
				$session->loc = $url->getUrl();
			}
			break;
		case 'bin-inquiry':
			// Requests Contents of bin
			// Response: fills Invsearch table with records
			$binID = $input->$requestmethod->text('binID');
			$data = array("DBNAME=$dplusdb", 'BININQUIRY', "BINID=$binID");

			if ($input->$requestmethod->page) {
				$page = $input->$requestmethod->text('page');
			} else {
				$page = $pages->get('/warehouse/inventory/bin-inquiry/')->url;
			}

			$url = new Purl\Url($page);
			$url->query->set('binID', $binID);
			$session->loc = $url->getUrl();
			break;
		case 'print-label-search':
			$scan = $input->$requestmethod->text('scan');
			$data = array("DBNAME=$dplusdb", 'PRINTLABELSEARCH', "QUERY=$scan");

			if ($input->get->page) {
				$url = new Purl\Url($input->$requestmethod->text('page'));
			} else {
				$url = new Purl\Url($pages->get('pw_template=whse-print-item-label')->url);
			}
			$url->query->set('scan', $scan);
			$session->loc = $url->getUrl();
			break;
		case 'print-labels-simple':
			$scan = $input->$requestmethod->text('scan');
			$query_phys = WhseitemphysicalcountQuery::create();
			$query_phys->filterBySessionid(session_id());
			$query_phys->filterByScan($scan);
			$item = $query_phys->findOne();
			$item->setProductionDate(date('Ymd', strtotime($input->$requestmethod->text('productiondate'))));
			$item->setQty($input->$requestmethod->text('weight'));
			$item->save();
			$labelcount = $input->$requestmethod->int('labelcount');
			$data = array("DBNAME=$dplusdb", 'PRINTLABELS', "NBRLABELS=$labelcount");

			if ($input->get->page) {
				$url = new Purl\Url($input->$requestmethod->text('page'));
			} else {
				$url = new Purl\Url($pages->get('pw_template=whse-print-item-label')->url);
			}
			$url->query->remove('scan');
			$session->loc = $url->getUrl();
			break;
		case 'init-label-print':
			// Requests label print defaults for Item
			// Response: return itemcartonlabel record to fill out
			$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
			$binID = $input->$requestmethod->text('binID');
			$itemID = $input->$requestmethod->text('itemID');
			$url = new Purl\Url($pages->get('/warehouse/inventory/print-item-label/')->httpUrl);
			$url->query->set('binID', $binID);

			if (!empty($input->$requestmethod->serialnbr) | !empty($input->$requestmethod->lotnbr)) {
				if ($input->$requestmethod->serialnbr) {
					$lotserial = $input->$requestmethod->text('serialnbr');
				} elseif ($input->$requestmethod->lotnbr) {
					$lotserial = $input->$requestmethod->text('lotnbr');
				}
				$item = InvsearchQuery::create()->get_lotserial(session_id(), $lotserial);
			} else {
				$item = InvsearchQuery::create()->findOneByItemid(session_id(), $itemID);
			}
			$data = array("DBNAME=$dplusdb", "ITEMCARTONINIT", "ITEMID=$itemID", "WHSE=$whsesession->whseid", "BIN=$binID");

			if ($item->is_lotted() || $item->is_serialized()) {
				$data[] = "LOTSER=$item->lotserial";
				$url->query->set($item->get_itemtypeproperty(), $item->lotserial);
			} else {
				$url->query->set('itemID', $itemID);
			}

			$session->loc  = $url->getUrl();
			break;
		case 'init-pick-item-label-print':
			// Requests label printing for Item based off an Order
			// Response: return itemcartonlabel record to fill out
			$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
			$ordn   = $input->$requestmethod->text('ordn');
			$itemID = $input->$requestmethod->text('itemID');
			$data = array("DBNAME=$dplusdb", "ORDERCARTONINIT", "ORDERNBR=$ordn", "ITEMID=$itemID");
			$session->loc = $input->$requestmethod->text('page');
			break;
		case 'print-thermal-label':
			// Requests label to be printed
			// Response: Takes itemcartonlabel and prints label
			$binID     = $input->$requestmethod->text('binID');
			$itemID    = $input->$requestmethod->text('itemID');
			$lotserial = $input->$requestmethod->text('lotserial');
			$whseID    = $input->$requestmethod->text('whseID');

			if (LabelPrintSessionQuery::create()->filterBySessionid(session_id())->count()) {
				$labelsession = LabelPrintSessionQuery::create()->findOneBySessionid(session_id());
			} else {
				$labelsession = new LabelPrintSession();
				$labelsession->setSessionid(session_id());
				$labelsession->setItemid($itemID);
				$labelsession->setBin($binID);
				$labelsession->setLotserial($lotserial);
				$labelsession->setWhse($whseID);
			}
			$labelsession->setLabelBox($input->$requestmethod->text('box-label'));
			$labelsession->setPrinterBox($input->$requestmethod->text('box-printer'));
			$labelsession->setQtyBox($input->$requestmethod->int('box-qty'));
			$labelsession->setNbrBoxLabels($input->$requestmethod->int('box-label-count'));

			$labelsession->setLabelMaster($input->$requestmethod->text('masterpack-label'));
			$labelsession->setPrinterMaster($input->$requestmethod->text('masterpack-printer'));
			$labelsession->setQtyMaster($input->$requestmethod->int('masterpack-qty'));
			$labelsession->setNbrMasterLabels($input->$requestmethod->int('masterpack-label-count'));
			$labelsession->save();

			$data = array("DBNAME=$dplusdb", "ITEMCARTONPRINT");
			$session->loc = $input->$requestmethod->text('page');
			break;
		case 'print-pick-item-thermal-label':
			// Requests label to be printed
			// Response: Takes itemcartonlabel and print out
			$ordn      = $input->$requestmethod->text('ordn');
			$itemID    = $input->$requestmethod->text('itemID');

			if (LabelPrintSessionQuery::create()->filterBySessionid(session_id())->count()) {
				$labelsession = LabelPrintSessionQuery::create()->findOneBySessionid(session_id());
			} else {
				$labelsession = new LabelPrintSession();
				$labelsession->setSessionid(session_id());
				$labelsession->setItemid($itemID);
			}

			$labelsession->setLabelBox($input->$requestmethod->text('box-label'));
			$labelsession->setPrinterBox($input->$requestmethod->text('box-printer'));
			$labelsession->setNbrBoxLabels($input->$requestmethod->int('box-label-count'));
			$labelsession->save();

			$data = array("DBNAME=$dplusdb", "ORDERCARTONPRINT", "ORDERNBR=$ordn", "ITEMID=$itemID");
			$session->loc = $input->$requestmethod->text('page');
			$session->printpicklabels = true;
			break;
		case 'physical-count-search':
			// Requests Item / Lot/Serial Details for Physical count
			// Response: Creates whseitemphysicalcount record(s)
			$q = strtoupper($input->$requestmethod->text('scan'));
			$binID = $input->$requestmethod->text('binID');
			$data = array("DBNAME=$dplusdb", 'PHYSITEMSEARCH', "QUERY=$q");

			if ($input->$requestmethod->page) {
				$url = new Purl\Url($input->$requestmethod->text('page'));
			} else {
				$url = new Purl\Url($pages->get('pw_template=whse-phsyical-count'));
			}

			$url->query->set('scan', $q);
			$session->loc = $url->getUrl();
			break;
		case 'physical-count-submit':
			// Requests Physical Count Details to be saved from the whseitemphysicalcount record
			// Response: Error or no record (completed)
			$scan = $input->$requestmethod->text('scan');
			$query_phys = WhseitemphysicalcountQuery::create();
			$query_phys->filterBySessionid(session_id());
			$query_phys->filterByScan($scan);

			$item = $query_phys->findOne();
			$item->setItemid($input->$requestmethod->text('itemID'));
			$item->setLotserial($input->$requestmethod->text('lotserial'));

			// Lot Serial Ref is Read-Only currently 8/23
			// $item->setLotserialref($input->$requestmethod->text('lotserialref'));
			$item->setBin($input->$requestmethod->text('binID'));
			$item->setQty($input->$requestmethod->text('qty'));
			$item->setProductiondate(date('Ymd', strtotime($input->$requestmethod->text('productiondate'))));
			$item->save();
			$session->bin = $input->$requestmethod->text('binID');

			$data = array("DBNAME=$dplusdb", 'PHYSITEMSAVE');

			if ($input->$requestmethod->page) {
				$url = new Purl\Url($input->$requestmethod->text('page'));
			} else {
				$url = new Purl\Url($pages->get('pw_template=whse-phsyical-count'));
			}
			$url->query->set('scan', $scan);
			$session->loc = $url->getUrl();
			break;
		case 'init-receive':
			// Requests PO to receive
			// Response: po_tran_det & po_tran_lot_det records are loaded with detail information
			$ponbr = $input->$requestmethod->text('ponbr');
			$data = array("DBNAME=$dplusdb", 'STARTRECEIVE', "PONBR=$ponbr");

			if ($input->$requestmethod->page) {
				$url = new Purl\Url($input->$requestmethod->text('page'));
				$url->query->set('ponbr', $ponbr);
			} else {
				$url = new Purl\Url($pages->get('pw_template=whse-receiving')->url);
				$url->query->set('ponbr', $ponbr);
			}
			$session->loc = $url->getUrl();
			break;
		case 'receiving-search':
			// Requests Item / Lot/Serial Details for Receiving
			// Response: Creates whseitemphysicalcount record(s)
			$ponbr = $input->$requestmethod->text('ponbr');
			$q = strtoupper($input->$requestmethod->text('scan'));
			$binID = $input->$requestmethod->text('binID');
			$data = array("DBNAME=$dplusdb", 'RECEIVINGSEARCH', "PONBR=$ponbr", "QUERY=$q", "BIN=$binID");

			if ($input->$requestmethod->page) {
				$url = new Purl\Url($input->$requestmethod->text('page'));
			} else {
				$url = new Purl\Url($pages->get('pw_template=whse-receiving'));
			}
			$url->query->set('ponbr', $ponbr);
			$url->query->set('scan', $q);
			$url->query->set('binID', $binID);

			$session->loc = $url->getUrl();
			break;
		case 'receiving-submit':
			// Request:  Save Received Item to PO DETAIL Line
			// Response: Updates po_tran_lot_det & po_tran_det records
			$scan = $input->$requestmethod->text('scan');
			$ponbr = $input->$requestmethod->text('ponbr');
			$query_phys = WhseitemphysicalcountQuery::create();
			$query_phys->filterBySessionid(session_id());
			$query_phys->filterByScan($scan);

			$item = $query_phys->findOne();
			$item->setItemid($input->$requestmethod->text('itemID'));
			$item->setLotserial($input->$requestmethod->text('lotserial'));
			$item->setLotserialref($input->$requestmethod->text('lotserialref'));
			$item->setBin($input->$requestmethod->text('binID'));
			$item->setQty($input->$requestmethod->text('qty'));
			$item->setProductiondate(date('Ymd', strtotime($input->$requestmethod->text('productiondate'))));
			$item->save();

			$session->receiving_itemid = $item->itemid;
			$session->receiving_bin = $item->bin;

			$data = array("DBNAME=$dplusdb", 'ACCEPTRECEIVING', "PONBR=$ponbr");

			$url = new Purl\Url($page->url);
			$url->query->set('action', 'verify-receiving-submit');
			$url->query->set('ponbr', $ponbr);
			$url->query->set('scan', $scan);
			$url->query->set('page', $input->$requestmethod->text('page'));
			$session->loc = $url->getUrl();
			break;
		case 'receiving-autosubmit':
			// Request:  Save Received Item to PO DETAIL Line
			// Response: Updates po_tran_lot_det & po_tran_det records
			$scan = $input->$requestmethod->text('scan');
			$ponbr = $input->$requestmethod->text('ponbr');
			$query_phys = WhseitemphysicalcountQuery::create();
			$query_phys->filterBySessionid(session_id());
			$query_phys->filterByScan($scan);

			$item = $query_phys->findOne();

			$session->receiving_itemid = $item->itemid;
			$session->receiving_bin = $item->bin;

			$data = array("DBNAME=$dplusdb", 'ACCEPTRECEIVING', "PONBR=$ponbr");

			$url = new Purl\Url($page->url);
			$url->query->set('action', 'verify-receiving-submit');
			$url->query->set('ponbr', $ponbr);
			$url->query->set('scan', $scan);
			$url->query->set('page', $input->$requestmethod->text('page'));
			$session->loc = $url->getUrl();
			break;
		case 'verify-receiving-submit':
			// Verifies if whseitemphysical count record has an error if it does, returns
			// to receiving page for that scanned item for the user
			$scan = $input->$requestmethod->text('scan');
			$ponbr = $input->$requestmethod->text('ponbr');

			$query_phys = WhseitemphysicalcountQuery::create();
			$query_phys->filterBySessionid(session_id());
			$query_phys->filterByScan($scan);

			echo $scan;
			$query_phys->findOne();
			echo "<br /> " . $dpluso->getLastExecutedQuery();

			$item = $query_phys->findOne();

			if ($input->$requestmethod->page) {
				$url = new Purl\Url($input->$requestmethod->text('page'));
			} else {
				$url = new Purl\Url($pages->get('pw_template=whse-receiving')->url);
				$url->query->set('ponbr', $ponbr);
			}

			if ($item->has_error()) {
				$url->query->set('scan', $scan);
			} else {
				$url->query->remove('scan');
			}
			$session->loc = $url->getUrl();
			break;
		case 'remove-received-item':
			// Request:  Remove one of the received lots / items
			// Response: Removes the requested records
			$ponbr    = $input->$requestmethod->text('ponbr');
			$linenbr  = $input->$requestmethod->int('linenbr');
			$sequence = $input->$requestmethod->int('sequence');

			$q = PurchaseOrderDetailLotReceivingQuery::create();
			$q->filterByPonbr($ponbr);
			$q->filterByLinenbr($linenbr);
			$q->filterBySequence($sequence);
			$item = $q->findOne();

			$data = array("DBNAME=$dplusdb", 'RECEIVEREMOVELOT', "PONBR=$ponbr", "LINENBR=$linenbr", "LOTSERIAL=$item->lotserial", "BIN=$item->bin");

			$url = new Purl\Url($pages->get('pw_template=whse-receiving')->url);
			$url->query->set('ponbr', $ponbr);
			$session->loc = $url->getUrl();
			$session->removefromline = $linenbr;
			break;
		case 'submit-receipt':
			// Request:  Sends Receipt to be posted
			// Response: POSTS receipt
			$ponbr    = $input->$requestmethod->text('ponbr');
			$data = array("DBNAME=$dplusdb", 'FINISHRECEIPT', "PONBR=$ponbr");
			$url = new Purl\Url($pages->get('pw_template=whse-receiving')->url);
			//$url->query->set('ponbr', $ponbr);
			$session->loc = $url->getUrl();
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
