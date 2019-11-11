<?php
	include_once('./vi-include.php');

	if ($vendorquery->count()) {
		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('vendors/vi/bread-crumbs.twig', ['page' => $page, 'vendor' => $vendor]);
		$page->body .= $config->twig->render('vendors/vi/vi-links.twig', ['page' => $page]);
		
		if ($input->get->shipfromID) {
			$shipfromID = $input->get->text('shipfromID');
			$load_shipfrom = $load_vendor;
			$query_shipfrom = VendorShipfromQuery::create()->filterByVendorid($vendorID)->filterByShipfromid($shipfromID);

			if ($load_shipfrom->shipfrom_exists()) {
				$shipfrom = $load_shipfrom->get_shipfrom();
				$page->title = "$vendor->name Shipfrom $shipfrom->id Contacts";
				$page->body .= $html->h2('', "$shipfrom->name");
				$page->body .= $config->twig->render('vendors/vi/contact/shipfrom/shipfrom-contact.twig', ['page' => $page, 'shipfrom' => $shipfrom]);
				$page->body .= $html->div('class=mb-4');
				$page->body .= $html->h2('', "$shipfrom->name Contacts");
				$page->body .= $config->twig->render('vendors/vi/contact/shipfrom/shipfrom-contacts.twig', ['page' => $page, 'shipfrom' => $shipfrom]);
			} else {
				$page->body = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "$vendorID Ship-to $shipfromID does not exist", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Check if shipfromID is correct"]);
			}
		} else {
			$page->title = "$vendor->name Contacts";
			$page->body .= $html->h2('', "$vendor->name");
			$page->body .= $config->twig->render('vendors/vi/contact/vendor-contact.twig', ['page' => $page, 'vendor' => $vendor]);
			$page->body .= $html->div('class=mb-4');
			$page->body .= $html->h2('', "$vendor->name Contacts");
			$page->body .= $config->twig->render('vendors/vi/contact/vendor-contacts.twig', ['page' => $page, 'vendor' => $vendor]);
			$page->body .= $html->div('class=mb-4');
			$page->body .= $html->h2('', "$vendor->name Shipfrom Contacts");
			$page->body .= $config->twig->render('vendors/vi/contact/shipfrom-contacts.twig', ['page' => $page, 'vendor' => $vendor]);
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
