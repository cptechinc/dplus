<?php
	// Figure out page request method, then grab needed inputs
	$requestmethod = $input->requestMethod('POST') ? 'post' : 'get';
	$action = $input->$requestmethod->text('action');
	$dplusdb = $modules->get('DplusOnlineDatabase')->db_name;

	// Set up filename and sessionID in case this was made through cURL
	$filename  = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();
	$sessionID = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();


	switch ($action) {
		case 'edit-order':
			$ordn = $input->get->text('ordn');
			if (SalesHistoryQuery::create()->filterByOrderNumber($ordn)->count()) {
				$custID = SalesHistoryQuery::create()->get_custid($ordn);
			} else {
				$custID = SalesOrderQuery::create()->get_custid($ordn);
			}
			$data = array("DBNAME=$dplusdb", "ORDRDET=$ordn", "CUSTID=$custID", "LOCK");
			$session->loc = $pages->get('pw_template=sales-order-edit')->url."?ordn=$ordn";
			break;
		case 'get-order-print':
			$ordn = $input->get->text('ordn');

			$custID = SalesOrderHistory::is_saleshistory($ordn) ? SalesOrderHistory::find_custid($ordn) : SalesOrder::find_custid($ordn);
			$data = array("DBNAME=$dplusdb", "ORDRDET=$ordn", "CUSTID=$custID");
			$session->loc = "{$config->pages->print}order/?ordn=$ordn";
			break;
		case 'get-order-details':
			$ordn = $input->get->text('ordn');
			$custID = SalesOrderHistory::is_saleshistory($ordn) ? SalesOrderHistory::find_custid($ordn) : SalesOrder::find_custid($ordn);
			$data = array("DBNAME=$dplusdb", "ORDRDET=$ordn", "CUSTID=$custID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = new Purl\Url($config->pages->ajaxload);
				$insertafter = ($input->get->text('type') == 'history') ? 'sales-history' : 'sales-orders';
				$url->path->add($insertafter);

				if ($input->get->custID) {
					$url->path->add('customer');
					$insertafter = $input->get->text('custID');
					$url->path->add($insertafter);

					if ($input->get->shipID) {
						$insertafter = "shipto-{$input->get->text('shipID')}";
						$url->path->add($insertafter);
					}
				}
				$url->query = "ordn=$ordn$linkaddon";
				Paginator::paginate_purl($url, $pagenumber, $insertafter);
				$session->loc = $url->getUrl();
			}

			break;
		case 'edit-new-order':
			$ordn = get_createdordn(session_id());
			$custID = SalesOrder::find_custid($ordn);
			$data = array("DBNAME=$dplusdb", "ORDRDET=$ordn", "CUSTID=$custID", 'LOCK');
			$session->createdorder = $ordn;
			$session->loc = "{$config->pages->editorder}?ordn=$ordn";
			break;
		case 'update-orderhead':
			$ordn = $input->$requestmethod->text("ordn");
			$intl = $input->$requestmethod->text("intl");

			$order = SalesOrderEdit::load(session_id(), $ordn);
			$order->set('shiptoid', $input->$requestmethod->text('shiptoid'));
			$order->set('custpo', $input->$requestmethod->text("custpo"));
			$order->set('shipname', $input->$requestmethod->text("shiptoname"));
			$order->set('shipaddress', $input->$requestmethod->text("shipto-address"));
			$order->set('shipaddress2', $input->$requestmethod->text("shipto-address2"));
			$order->set('shipcity', $input->$requestmethod->text("shipto-city"));
			$order->set('shipstate', $input->$requestmethod->text("shipto-state"));
			$order->set('shipzip', $input->$requestmethod->text("shipto-zip"));
			$order->set('contact', $input->$requestmethod->text('contact'));
			$order->set('phone', $input->$requestmethod->text("contact-phone"));
			$order->set('extension', $input->$requestmethod->text("contact-extension"));
			$order->set('faxnbr', $input->$requestmethod->text("contact-fax"));
			$order->set('email', $input->$requestmethod->text("contact-email"));
			$order->set('releasenbr', $input->$requestmethod->text("release-number"));
			$order->set('shipviacd', $input->$requestmethod->text('shipvia'));
			$order->set('rqstdate', $input->$requestmethod->text("rqstdate"));
			$order->set('shipcom', $input->$requestmethod->text("shipcomplete"));
			// $order->set('billname', $input->$requestmethod->text('cust-name'));
			// $order->set('custname', $input->$requestmethod->text('cust-name'));
			// $order->set('billaddress', $input->$requestmethod->text('cust-address'));
			// $order->set('billaddress2', $input->$requestmethod->text('cust-address2'));
			// $order->set('billcity', $input->$requestmethod->text('cust-city'));
			// $order->set('billstate', $input->$requestmethod->text('cust-state'));
			// $order->set('billzip', $input->$requestmethod->text('cust-zip'));

			if ($intl == 'Y') {
				$order->set('phone', $input->$requestmethod->text("office-accesscode") . $input->$requestmethod->text("office-countrycode") . $input->$requestmethod->text("intl-office"));
				$order->set('extension', $input->$requestmethod->text("intl-ofice-ext"));
				$order->set('faxnbr', $input->$requestmethod->text("fax-accesscode") . $input->$requestmethod->text("fax-countrycode") . $input->$requestmethod->text("intl-fax"));
			} else {
				$order->set('phone', $input->$requestmethod->text("contact-phone"));
				$order->set('extension', $input->$requestmethod->text("contact-extension"));
				$order->set('faxnbr', $input->$requestmethod->text("contact-fax"));
			}
			$custID = SalesOrder::find_custid($ordn);
			$session->sql = $order->update();

			$order->set('paymenttype', $input->$requestmethod->text("paytype"));

			if ($order->paymenttype == 'cc') {
				$order->set('cardnumber', $input->$requestmethod->text("ccno"));
				$order->set('cardexpire', $input->$requestmethod->text("xpd"));
				$order->set('cardcode', $input->$requestmethod->text("ccv"));
			}

			$session->sql .= '<br>'. $order->update_payment();
			$data = array("DBNAME=$dplusdb", 'SALESHEAD', "ORDERNO=$ordn", "CUSTID=$custID");

			if ($input->$requestmethod->exitorder) {
				$session->loc = $config->pages->orders."redir/?action=unlock-order&ordn=$ordn";
				$data['UNLOCK'] = false;
				$session->remove('createdorder');
			} else {
				$session->loc = $config->pages->editorder."?ordn=$ordn";
			}
			break;
		case 'add-to-order':
			$itemID = $input->$requestmethod->text('itemID');
			$qty = determine_qty($input, $requestmethod, $itemID);
			$ordn = $input->$requestmethod->text('ordn');
			$custID = SalesOrder::find_custid($ordn);
			$data = array("DBNAME=$dplusdb", 'SALEDET', "ORDERNO=$ordn", "ITEMID=$itemID", "QTY=$qty", "CUSTID=$custID");
			$session->loc = $input->$requestmethod->page;
			$session->editdetail = true;
			break;
		case 'add-multiple-items':
			$ordn = $input->$requestmethod->text('ordn');
			$itemids = $input->$requestmethod->itemID;
			$qtys = $input->$requestmethod->qty;
			$data = array("DBNAME=$dplusdb", 'ORDERADDMULTIPLE', "ORDERNO=$ordn");
			$data = writedataformultitems($data, $itemids, $qtys);
            $session->loc = $config->pages->edit."order/?ordn=".$ordn;
			$session->editdetail = true;
			break;
		case 'add-nonstock-item': // FIX
			$ordn = $input->$requestmethod->text('ordn');
			$qty = $input->$requestmethod->text('qty');
			$orderdetail = new SalesOrderDetail();
			$orderdetail->set('sessionid', session_id());
			$orderdetail->set('linenbr', '0');
			$orderdetail->set('recno', '0');
			$orderdetail->set('orderno', $ordn);
			$orderdetail->set('itemid', 'N');
			$orderdetail->set('vendoritemid', $input->$requestmethod->text('itemID'));
			$orderdetail->set('vendorid', $input->$requestmethod->text('vendorID'));
			$orderdetail->set('shipfromid', $input->$requestmethod->text('shipfromID'));
			$orderdetail->set('vendoritemid', $input->$requestmethod->text('itemID'));
			$orderdetail->set('desc1', $input->$requestmethod->text('desc1'));
			$orderdetail->set('desc2', $input->$requestmethod->text('desc2'));
			$orderdetail->set('qty', $input->$requestmethod->text('qty'));
			$orderdetail->set('price', $input->$requestmethod->text('price'));
			$orderdetail->set('cost', $input->$requestmethod->text('cost'));
			$orderdetail->set('uom', $input->$requestmethod->text('uofm'));
			$orderdetail->set('nsitemgroup', $input->$requestmethod->text('nsitemgroup'));
			$orderdetail->set('ponbr', $input->$requestmethod->text('ponbr'));
			$orderdetail->set('poref', $input->$requestmethod->text('poref'));
			$orderdetail->set('spcord', 'S');
			$orderdetail->set('date', date('Ymd'));
			$orderdetail->set('time', date('His'));
			$orderdetail->set('sublinenbr', '0');

			$session->sql = $orderdetail->save(true);
			$orderdetail->save();

			$data = array("DBNAME=$dplusdb", 'SALEDET', "ORDERNO=$ordn", "LINENO=0", "ITEMID=N", "QTY=$qty", "CUSTID=$custID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$session->loc = $config->pages->edit."order/?ordn=".$ordn;
			}
			$session->editdetail = true;
			break;
		case 'quick-update-line':
			$ordn = $input->$requestmethod->text('ordn');
			$linenbr = $input->$requestmethod->text('linenbr');
			$custID = SalesOrder::find_custid($ordn);
			$orderdetail = SalesOrderDetail::load(session_id(), $ordn, $linenbr);
			// $orderdetail->set('whse', $input->$requestmethod->text('whse'));
			$qty = determine_qty($input, $requestmethod, $orderdetail->itemid); // TODO MAKE IN CART DETAIL
			$orderdetail->set('qty', $qty);
			$orderdetail->set('price', $input->$requestmethod->text('price'));
			$orderdetail->set('rshipdate', $input->$requestmethod->text('rqstdate'));
			$session->sql = $orderdetail->update();
			$data = array("DBNAME=$dplusdb", 'SALEDET', "ORDERNO=$ordn", "LINENO=$linenbr", "CUSTID=$custID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$session->loc = $config->pages->edit."order/?ordn=$ordn";
			}
			$session->editdetail = true;
			break;
		case 'update-line':
			$ordn = $input->$requestmethod->text('ordn');
			$linenbr = $input->$requestmethod->text('linenbr');
			$orderdetail = SalesOrderDetail::load(session_id(), $ordn, $linenbr);
			$qty = determine_qty($input, $requestmethod, $orderdetail->itemid); // TODO MAKE IN CART DETAIL
			$orderdetail->set('price', $input->$requestmethod->text('price'));
			$orderdetail->set('discpct', $input->$requestmethod->text('discount'));
			$orderdetail->set('qty', $qty);
			$orderdetail->set('rshipdate', $input->$requestmethod->text('rqstdate'));
			$orderdetail->set('whse', $input->$requestmethod->text('whse'));
			$orderdetail->set('linenbr', $input->$requestmethod->text('linenbr'));
			$orderdetail->set('spcord', $input->$requestmethod->text('specialorder'));
			$orderdetail->set('vendorid', $input->$requestmethod->text('vendorID'));
			$orderdetail->set('shipfromid', $input->$requestmethod->text('shipfromID'));
			$orderdetail->set('vendoritemid', $input->$requestmethod->text('vendoritemID'));
			$orderdetail->set('nsitemgroup', $input->$requestmethod->text('nsgroup'));
			$orderdetail->set('ponbr', $input->$requestmethod->text('ponbr'));
			$orderdetail->set('poref', $input->$requestmethod->text('poref'));
			$orderdetail->set('uom', $input->$requestmethod->text('uofm'));

			if ($orderdetail->spcord != 'N') {
				$orderdetail->set('desc1', $input->$requestmethod->text('desc1'));
				$orderdetail->set('desc2', $input->$requestmethod->text('desc2'));
			}
			$custID = SalesOrder::find_custid($ordn);
			$session->sql = $orderdetail->update();
			$data = array("DBNAME=$dplusdb", 'SALEDET', "ORDERNO=$ordn", "LINENO=$linenbr", "CUSTID=$custID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$session->loc = $config->pages->edit."order/?ordn=$ordn";
			}
			$session->editdetail = true;
			break;
		case 'remove-line':
			$ordn = $input->$requestmethod->text('ordn');
			$linenbr = $input->$requestmethod->text('linenbr');
			$orderdetail = SalesOrderDetail::load(session_id(), $ordn, $linenbr);
			$orderdetail->set('qty', '0');
			$session->sql = $orderdetail->update();
			$session->editdetail = true;
			$custID = SalesOrder::find_custid($ordn);
			$data = array("DBNAME=$dplusdb", 'SALEDET', "ORDERNO=$ordn", "LINENO=$linenbr", "QTY=0", "CUSTID=$custID");
			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$session->loc = $config->pages->edit."order/?ordn=".$ordn;
			}
			break;
		case 'remove-line-get':
			$ordn = $input->get->text('ordn');
			$linenbr = $input->get->text('linenbr');
			$orderdetail = SalesOrderDetail::load(session_id(), $ordn, $linenbr);
			$orderdetail->set('qty', '0');
			$session->sql = $orderdetail->update();
			$custID = SalesOrder::find_custid($ordn);
			$data = array("DBNAME=$dplusdb", 'SALEDET', "ORDERNO=$ordn", "LINENO=$linenbr", "QTY=0", "CUSTID=$custID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$session->loc = $config->pages->edit."order/?ordn=".$ordn;
			}
			$session->editdetail = true;
			break;
		case 'unlock-order':
			$ordn = $input->get->text('ordn');
			$data = array("DBNAME=$dplusdb", 'UNLOCK', "ORDERNO=$ordn");
			$session->remove('createdorder');
			$session->loc = $config->pages->confirmorder."?ordn=$ordn";
			break;
		default:
			$data = array("DBNAME=$dplusdb", 'REPORDRHED', "TYPE=O");
			$session->loc = $config->pages->ajax."load/orders/salesrep/".urlencode($custID)."/?ordn=".$linkaddon."";
			$session->{'orders-loaded-for'} = $user->loginid;
			$session->{'orders-updated'} = date('m/d/Y h:i A');
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
