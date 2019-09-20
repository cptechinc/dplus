<?php
	use CustomerQuery, Customer;

	$module_ci = $modules->get('MciPages');
	$module_ci->init_cipage();

	$html = $modules->get('HtmlWriter');

	$custID = $input->get->text('custID');
	$page->custID = $custID;

	if ($input->get->shiptoID) {
		$shiptoID = $input->get->text('shiptoID');
		$page->shiptoID = $shiptoID;
	}
	
	$customerquery = CustomerQuery::create();
	$customerquery->filterByCustid($custID);

	if ($input->get->custID) {
		if ($customerquery->count()) {
			$customer = $customerquery->findOneByCustid($custID);
		} else {
			$page->headline = $page->title = "Customer $custID could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => "Check if the Customer ID is correct"]);
		}
	} else {
		$page->headline = $page->title = "Customer ID was not provided";
		$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => "Customer ID was not provided"]);
	}
