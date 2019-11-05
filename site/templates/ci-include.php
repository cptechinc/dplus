<?php
	use CustomerQuery, Customer;

	$module_ci = $modules->get('DpagesMci');
	$module_ci->init_cipage();

	$html = $modules->get('HtmlWriter');

	$custID = $input->get->text('custID');
	$load_customer = $modules->get('CiLoadCustomerShipto');
	$load_customer->set_custID($custID);

	if ($input->get->shiptoID) {
		$shiptoID = $input->get->shiptoID;
		$load_customer->set_shiptoID($shiptoID);
	}

	$customerquery = CustomerQuery::create();
	$customerquery->filterByCustid($custID);

	if ($input->get->custID) {
		if ($customerquery->count()) {
			$customer = $customerquery->findOneByCustid($custID);

			if (!$user->has_customer($custID)) {
				$customerquery = CustomerQuery::create();
				$customerquery->filterByCustid(session_id());
				$page->headline = $page->title = "You do not have access to customer $custID";
				$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => "Please verify if this is correct"]);
			}
		} else {
			$page->headline = $page->title = "Customer $custID could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => "Check if the Customer ID is correct"]);
		}
	} else {
		$page->headline = $page->title = "Customer ID was not provided";
		$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => "Customer ID was not provided"]);
	}
