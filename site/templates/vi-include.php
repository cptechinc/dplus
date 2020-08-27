<?php
	$module_vi = $modules->get('DpagesMvi');
	$module_vi->init_vipage();

	$html = $modules->get('HtmlWriter');

	$vendorID = $input->get->text('vendorID');
	$load_vendor = $modules->get('ViLoadVendorShipfrom');
	$load_vendor->set_vendorID($vendorID);

	if ($input->get->shipfromID) {
		$shipfromID = $input->get->text('shipfromID');
		$load_vendor->set_shipfromID($shipfromID);
	} else {
		$shipfromID = false;
	}

	$vendorquery = VendorQuery::create();
	$vendorquery->filterByVendorid($vendorID);

	if ($input->get->vendorID) {
		if ($vendorquery->count()) {
			$vendor = $vendorquery->findOneByVendorid($vendorID);
		} else {
			$page->headline = $page->title = "Vendor $vendorID could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => "Check if the Vendor ID is correct"]);
		}
	} else {
		$page->headline = $page->title = "Vendor ID was not provided";
		$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => "Vendor ID was not provided"]);
	}
