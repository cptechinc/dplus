<?php
	// Figure out page request method, then grab needed inputs
	$requestmethod = $input->requestMethod('POST') ? 'post' : 'get';
	$action = $input->$requestmethod->text('action');
	$dplusdb = $modules->get('DplusOnlineDatabase')->db_name;

	// Set up filename and sessionID in case this was made through cURL
	$filename  = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();
	$sessionID = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();

	switch ($action) {
		case 'inventory-search':
			$q = strtoupper($input->$requestmethod->text('scan'));
			$binID = $input->$requestmethod->text('binID');
			$data = array("DBNAME=$dplusdb", 'INVSEARCH', "QUERY=$q");
			$url = new Purl\Url($input->$requestmethod->text('page'));
			$url->query->set('scan', $q);
			$url->query->set('binID', $binID);
			$session->loc = $url->getUrl();
			break;
		case 'physical-count':
			$binID = $input->$requestmethod->text('binID');
			$itemID = $input->$requestmethod->text('itemID');
			$imitem = ItemMasterItem::load($itemID);
			$returnurl = new Purl\Url($input->$requestmethod->text('page'));
			$returnurl->query->remove('scan');
			$qty_total = 0;

			if (!empty($input->$requestmethod->serialnbr) | !empty($input->$requestmethod->lotnbr)) {
				if ($input->$requestmethod->serialnbr) {
					$lotserial = $input->$requestmethod->text('serialnbr');
				} elseif ($input->$requestmethod->lotnbr) {
					$lotserial = $input->$requestmethod->text('lotnbr');
				}
				$item = InventorySearchItem::load_from_lotserial(session_id(), $lotserial);
			} else {
				$item = InventorySearchItem::load_from_itemid(session_id(), $itemID);
			}
			$outerpacks = $input->$requestmethod->int('outer-pack-qty');
			$innerpacks = $input->$requestmethod->int('inner-pack-qty');

			$qty_outerpack = $outerpacks * $imitem->outerpackqty;
			$qty_innerpack = $innerpacks * $imitem->innerpackqty;
			$qty_each = $input->$requestmethod->int('each-qty');

			$qty_total = $qty_outerpack + $qty_innerpack + $qty_each;

			$data = array("DBNAME=$dplusdb", "ITEMTAG", "ITEMID=$item->itemid", "BIN=$binID");

			if ($item->is_lotted() || $item->is_serialized()) {
				$data[] = "LOTSERIAL=$item->lotserial";
			}

			$data[] = "QTY=$qty_total";
			$returnurl->query->remove('lotnbr');
			$returnurl->query->remove('serialnbr');
			$returnurl->query->remove('itemID');
			$returnurl->query->remove('itemid');
			$session->loc = $returnurl->getUrl();

			$itemhistory = array('binID' => $binID, 'itemID' => $item->itemid, 'qty' => $qty_total);

			if ($session->physicalcounthistory) {
				$history = json_decode($session->physicalcounthistory, true);
			} else {
				$history = array();
			}
			$history[$binID][$item->itemid] = $itemhistory;
			$session->physicalcounthistory = json_encode($history);
			break;
		case 'bin-inquiry':
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
		case 'init-label-print':
			$whsesession = WhseSession::load(session_id());
			$binID = $input->$requestmethod->text('binID');
			$itemID = $input->$requestmethod->text('itemID');
			$url = new Purl\Url($config->pages->inventory_printitemlabel);
			$url->query->set('binID', $binID);

			if (!empty($input->$requestmethod->serialnbr) | !empty($input->$requestmethod->lotnbr)) {
				if ($input->$requestmethod->serialnbr) {
					$lotserial = $input->$requestmethod->text('serialnbr');
				} elseif ($input->$requestmethod->lotnbr) {
					$lotserial = $input->$requestmethod->text('lotnbr');
				}
				$item = InventorySearchItem::load_from_lotserial(session_id(), $lotserial);
			} else {
				$item = InventorySearchItem::load_from_itemid(session_id(), $itemID);
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
		case 'print-thermal-label':
			$binID = $input->$requestmethod->text('binID');
			$itemID = $input->$requestmethod->text('itemID');
			$lotserial = $input->$requestmethod->text('lotserial');
			$whseID = $input->$requestmethod->text('whseID');

			if (LabelPrintSession::exists(session_id())) {
				$labelsession = LabelPrintSession::load(session_id());
			} else {
				$labelsession = new LabelPrintSession();
				$labelsession->set('sessionid', session_id());
				$labelsession->set('itemid', $itemID);
				$labelsession->set('bin', $binID);
				$labelsession->set('lotserial', $lotserial);
				$labelsession->set('whse', $whseID);
			}

			$labelsession->set('label_box', $input->$requestmethod->text('box-label'));
			$labelsession->set('printer_box', $input->$requestmethod->text('box-printer'));
			$labelsession->set('qty_box', $input->$requestmethod->text('box-qty'));
			$labelsession->set('nbr_box_labels', $input->$requestmethod->text('box-label-count'));

			$labelsession->set('label_master', $input->$requestmethod->text('masterpack-label'));
			$labelsession->set('printer_master', $input->$requestmethod->text('masterpack-printer'));
			$labelsession->set('qty_master', $input->$requestmethod->text('masterpack-qty'));
			$labelsession->set('nbr_master_labels', $input->$requestmethod->text('masterpack-label-count'));
			$labelsession->save();

			$data = array("DBNAME=$dplusdb", "ITEMCARTONPRINT");
			$session->loc = $input->$requestmethod->text('page');
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
