<?php
	include_once('./ci-include.php');

	$query = CustindexQuery::create();
	$custID = $input->get->text('custID');
	$query->filterByCustid($custID);

	$page->show_breadcrumbs = false;
	$page->body .= $config->twig->render('customers/ci/bread-crumbs.twig', ['page' => $page, 'customer' => $customer]);

	if ($input->get->text('shiptoID')) {
		$shiptoID = $input->get->text('shiptoID');
		$query->filterByShiptoid($shiptoID);
	}

	$contactID = $input->get->text('contactID');
	$contact = $query->findOneByContact($contactID);

	$page->body .= $config->twig->render('customers/ci/ci-links.twig', ['page' => $page, 'custID' => $custID]);
	$page->body .= $config->twig->render('customers/ci/customer/contact/contact-edit.twig', ['page' => $page, 'custID' => $custID, 'contact' => $contact]);

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
