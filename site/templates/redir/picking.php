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
	*	case 'cancel-order':
	*		Request cancel picking order
	*		Response: updates wmpickdet, whsesssion records, order locks
	*		DBNAME=$dplusdb
	*		CANCELSTART
	*		ORDERNBR=$ordn
	*		break;
	* }
	**/

	switch ($action) {
		case 'start-pick':
			$data = array("DBNAME=$dplusdb", 'PICKING');
			break;
		case 'start-pick-unguided':
			$data = array("DBNAME=$dplusdb", 'PICKUNGUIDED');
			break;
		case 'start-pick-pack':
			$data = array("DBNAME=$dplusdb", 'PACKING');
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
		case 'cancel-order':
			$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
			$data = array("DBNAME=$dplusdb", 'CANCELSTART', "ORDERNBR=$whsesession->ordn");
			$session->loc = $input->$requestmethod->text('page');
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
