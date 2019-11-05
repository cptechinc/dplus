<?php
	// Figure out page request method, then grab needed inputs
	$requestmethod = $input->requestMethod('POST') ? 'post' : 'get';

	$action = $input->$requestmethod->text('action');
	$dplusdb = $modules->get('DplusOnlineDatabase')->db_name;

	// Set up filename and sessionID in case this was made through cURL
	$filename  = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();
	$sessionID = ($input->$requestmethod->sessionID) ? $input->$requestmethod->text('sessionID') : session_id();

	$custID =  $input->$requestmethod->text('custID');

	/**
	* SALES ORDERS REDIRECT
	*
	*
	* switch ($action) {
	* 	case 'ci-contacts':
	* 		Request CI Contacts JSON file
	* 		Response: Creates CI Contacts JSON file
	*		DBNAME=$dplusdb
	*		CICONTACT
	*		CUSTID=$custID
	*		SHIPID=$shipID
	*		break;
	*	case 'ci-sales-orders':
	* 		Request CI Sales Orders JSON file
	* 		Response: Creates CI Sales Orders JSON file
	*		DBNAME=$dplusdb
	*		CISALESORDR
	*		CUSTID=$custID
	*		SHIPID=$shipID
	*		SALESORDRNBR=
	*		ITEMID=
	*		break;
	*	case 'ci-sales-history':
	* 		Request CI Sales History JSON file
	* 		Response: Creates CI Sales History JSON file
	*		DBNAME=$dplusdb
	*		CISALESHIST
	*		CUSTID=$custID
	*		SHIPID=$shipID
	*		DATE=$startdate
	*		SALESORDRNBR=
	*		ITEMID=$itemID
	*		break;
	*	case 'ci-quotes':
	* 		Request CI Quotes JSON file
	* 		Response: Creates CI Quotes JSON file
	*		DBNAME=$dplusdb
	*		CIQUOTE
	*		CUSTID=$custID
	*		break;
	*	case 'ci-open-invoices':
	* 		Request CI Open Invoices JSON file
	* 		Response: Creates CI Open Invoices JSON file
	*		DBNAME=$dplusdb
	*		CIOPENINV
	*		CUSTID=$custID
	*		break;
	*	case 'ci-payments':
	* 		Request CI Payments JSON file
	* 		Response: Creates CI Payments JSON file
	*		DBNAME=$dplusdb
	*		CIPAYMENT
	*		CUSTID=$custID
	*		break;
	*	case 'ci-purchase-orders':
	*		Request CI Purchase Orders JSON file
	* 		Response: Creates CI Purchase Orders JSON file
	* 		DBNAME=dplusdb
	* 		CICUSTPO
	* 		CUSTID=$custID
	* 		"SHIPID=$shipID
	* 		CUSTPO=$custpo
	* 		break;
	* 	case 'ci-credit':
	* 		Request CI Credit JSON file
	* 		Response: Creates CI Credit JSON file
	* 		DBNAME=$dplusdb
	* 		CICREDIT
	* 		CUSTID=$custID
	* 		break;
	* 	case 'ci-standing-orders':
	* 		Request CI Standing Orders JSON file
	* 		Response: Creates CI Standing Orders JSON file
	* 		DBNAME=$dplusdb
	* 		CISTANDORDR
	* 		CUSTID=$custID
	* 		SHIPID=$shipID
	* 		break;
	* 	case 'ci-documents':
	* 		Request CI Documents JSON file
	* 		Response: Creates CI Documents JSON file
	* 		DBNAME=$dplusdb
	* 		DOCVIEW
	* 		FLD1CD=CU
	* 		FLD1DATA=$custID
	* 		FLD1DESC=$custname
	* 		break;
	* }
	**/

	switch ($action) {
		case 'ci-pricing':
			$itemID = $input->get->text('itemID');
			$data = array("DBNAME=$dplusdb", 'CIPRICE', "ITEMID=$itemID", "CUSTID=$custID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = new Purl\Url($pages->get('pw_template=ci-pricing')->url);
				$url->query->set('custID', $custID);
				$url->query->set('itemID', $itemID);
				$session->loc = $url->getUrl();
			}
			break;
		case 'ci-contacts':
			$shiptoID = $input->$requestmethod->text('shiptoID');
			$data = array("DBNAME=$dplusdb", 'CICONTACT', "CUSTID=$custID", "SHIPID=$shiptoID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = new Purl\Url($pages->get('pw_template=ci-contacts')->url);
				$url->query->set('custID', $custID);
				if ($shiptoID) {
					$url->query->set('shiptoID', $shiptoID);
				}
				$session->loc = $url->getUrl();
			}
			break;
		case 'edit-contact':
			$custID = $input->$requestmethod->text('custID');
			$shiptoID = $input->$requestmethod->text('shipID');
			$contactID = $input->$requestmethod->text('contactID');
			$newcontactID = $input->$requestmethod->text('contact-name');

			$q = CustindexQuery::create()->filterByCustid($custID);

			if ($shiptoID) {
				$q->filterByShiptoid($shiptoID);
			}
			$editcontact = $q->findOneByContact($contactID);

			$editcontact->setTitle($input->$requestmethod->text('contact-title'));
			$editcontact->setPhone($input->$requestmethod->text('contact-phone'));
			$editcontact->setExtension($input->$requestmethod->text('contact-extension'));
			$editcontact->setFaxnbr($input->$requestmethod->text('contact-fax'));
			$editcontact->setCellphone($input->$requestmethod->text('contact-cellphone'));
			$editcontact->setEmail($input->$requestmethod->text('contact-email'));
			$editcontact->setArcontact($input->$requestmethod->text('arcontact') == 'Y' ? "Y" : "N");
			$editcontact->setDunningcontact($input->$requestmethod->text('duncontact') == 'Y' ? "Y" : "N");
			$editcontact->setBuyingcontact($input->$requestmethod->text('buycontact') == 'Y' ? "Y" : "N");
			$editcontact->setCertcontact($input->$requestmethod->text('certcontact') == 'Y' ? "Y" : "N");
			$editcontact->setAckcontact($input->$requestmethod->text('ackcontact') == 'Y' ? "Y" : "N");

			if ($newcontactID != $contactID) {
				$editcontact->setContact($newcontactID);
				$editcontact->save();

				// UPDATE USER ACTIONS TO FOLLOW NEW CONTACT NAME.
				$query = UseractionsQuery::create();
				$query->filterByCustomerlink($custID);

				if ($shiptoID) {
					$query->filterByShiptolink($shiptoID);
				}
				$query->filterByContactlink($contactID);

				if ($query->count()) {
					$query->update(array('Contactlink' => $editcontact->contact));
				}

				$data = array("DBNAME=$dplusdb", 'EDITCONTACT', "CUSTID=$custID", "SHIPID=$shiptoID", "CONTACT=$contactID", "OLDCONTACT=$contactID", "NEWCONTACT=$newcontactID");
			} else {
				$editcontact->save();
				$data = array("DBNAME=$dplusdb", 'EDITCONTACT', "CUSTID=$custID", "SHIPID=$shiptoID", "CONTACT=$contactID", "OLDCONTACT=", "NEWCONTACT=");
			}

			$url = new Purl\Url($pages->get('pw_template=ci-contact')->url);
			$url->query->set('custID', $custID);

			if ($shiptoID) {
				$url->query->set('shiptoID', $shiptoID);
			}
			$url->query->set('contactID', $editcontact->contact);
			$session->loc = $url->getUrl();
			break;
		case 'ci-sales-orders':
			$shipID = $input->$requestmethod->text('shipID');
			$data = array("DBNAME=$dplusdb", 'CISALESORDR', "CUSTID=$custID", "SHIPID=$shipID", "SALESORDRNBR= ","ITEMID=");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = new Purl\Url($pages->get('pw_template=ci-sales-orders')->url);
				$url->query->set('custID', $custID);
				$session->loc = $url->getUrl();
			}
			break;
		case 'ci-sales-history':
			$shipID = $input->$requestmethod->text('shipID');
			$itemID = $input->$requestmethod->text('itemID');

			$data = array("DBNAME=$dplusdb", 'CISALESHIST', "CUSTID=$custID", "SHIPID=$shipID", "SALESORDRNBR=", "ITEMID=$itemID");

			$date = $input->$requestmethod->text('date');

			if (!empty($date)) {
				$date_ymd = date('Ymd', strtotime($date));
				$data[] = "DATE=$date_ymd";
			}

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = new Purl\Url($pages->get('pw_template=ci-sales-history')->url);
				$url->query->set('custID', $custID);
				$session->loc = $url->getUrl();
			}
			break;
		case 'ci-purchase-orders':
			$custpo = $input->$requestmethod->text('custpo');
			$shipID = $input->$requestmethod->text('shipID');
			$data = array("DBNAME=$dplusdb", 'CICUSTPO', "CUSTID=$custID", "SHIPID=$shipID", "CUSTPO=$custpo");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = new Purl\Url($pages->get('pw_template=ci-customer-po')->url);
				$url->query->set('custID', $custID);
				$url->query->set('custpo', $custpo);
				$session->loc = $url->getUrl();
			}
			break;
		case 'ci-quotes':
			$shipID = $input->$requestmethod->text('shipID');
			$data = array("DBNAME=$dplusdb", 'CIQUOTE', "CUSTID=$custID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = new Purl\Url($pages->get('pw_template=ci-quotes')->url);
				$url->query->set('custID', $custID);
				$session->loc = $url->getUrl();
			}
			break;
		case 'ci-open-invoices':
			$shipID = $input->$requestmethod->text('shipID');
			$data = array("DBNAME=$dplusdb", 'CIOPENINV', "CUSTID=$custID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = new Purl\Url($pages->get('pw_template=ci-open-invoices')->url);
				$url->query->set('custID', $custID);
				$session->loc = $url->getUrl();
			}
			break;
		case 'ci-payments':
			$shipID = $input->$requestmethod->text('shipID');
			$data = array("DBNAME=$dplusdb", 'CIPAYMENT', "CUSTID=$custID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = new Purl\Url($pages->get('pw_template=ci-payments')->url);
				$url->query->set('custID', $custID);
				$session->loc = $url->getUrl();
			}
			break;
		case 'ci-credit':
			$data = array("DBNAME=$dplusdb", 'CICREDIT', "CUSTID=$custID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = new Purl\Url($pages->get('pw_template=ci-credit')->url);
				$url->query->set('custID', $custID);
				$session->loc = $url->getUrl();
			}
			break;
		case 'ci-standing-orders':
			$shipID = $input->$requestmethod->text('shipID');
			$data = array("DBNAME=$dplusdb", 'CISTANDORDR', "CUSTID=$custID", "SHIPID=$shipID");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = new Purl\Url($pages->get('pw_template=ci-standing-orders')->url);
				$url->query->set('custID', $custID);
				$session->loc = $url->getUrl();
			}
			break;
		case 'ci-documents':
			$custname = Customer::get_customernamefromid($custID);
			$data = array("DBNAME=$dplusdb", 'DOCVIEW', "FLD1CD=CU", "FLD1DATA=$custID", "FLD1DESC=$custname");

			if ($input->$requestmethod->page) {
				$session->loc = $input->$requestmethod->text('page');
			} else {
				$url = new Purl\Url($pages->get('pw_template=ci-documents')->url);
				$url->query->set('custID', $custID);
				$session->loc = $url->getUrl();
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
