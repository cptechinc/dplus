<?php
	include_once('./ci-include.php');

	$query = CustindexQuery::create();
	$query->filterByCustid($custID);

	$page->show_breadcrumbs = false;


	if ($input->get->text('shiptoID')) {
		$shiptoID = $input->get->text('shiptoID');
		$query->filterByShiptoid($shiptoID);
	}

	$contactID = $input->get->text('contactID');
	$contact = $query->findOneByContact($contactID);

	$page->title = "$contact->contact";
	$page->body .= $config->twig->render('customers/ci/bread-crumbs.twig', ['page' => $page, 'customer' => $customer]);
	$page->title .= ", ";
	$page->title .= $input->get->shiptoID ? $shipto->name : $customer->name;
	$page->body .= $config->twig->render('customers/ci/ci-links.twig', ['page' => $page, 'custID' => $custID]);
	$page->body .= $config->twig->render('customers/ci/customer/contact/contact.twig', ['page' => $page, 'custID' => $custID, 'contact' => $contact]);

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
