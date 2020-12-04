<?php
	$config->po = ConfigPoQuery::create()->findOne();
	$html = $modules->get('HtmlWriter');
	$filter = $modules->get('FilterPurchaseOrders');
	$filter->init_query($user);
	$filter->filter_input($input);
	$filter->apply_sortby($page);
	$query = $filter->get_query();
	$orders = $query->paginate($input->pageNum, 10);

	$vendorID = $input->get->text('vendorID');
	$load_vendor = $modules->get('ViLoadVendorShipfrom');
	$load_vendor->set_vendorID($vendorID);
	$vendor = $load_vendor->get_vendor();

	if ($input->get->shipfromID) {
		$shipfromID = $input->get->text('shipfromID');
		$load_vendor->set_shipfromID($shipfromID);
		$shipfrom = $load_vendor->get_shipfrom();
	} else {
		$shipfromID = false;
	}

	$page->title = $shipfromID ? $shipfrom->name : $vendor->name;
	$page->title .= " Purchase Orders";

	$page->body = $config->twig->render('purchase-orders/vendor/search-form.twig', ['page' => $page, 'input' => $input]);
	$page->body .= $html->h3('', $orders->getNbResults() . " Purchase Orders");
	$page->body .= $config->twig->render('purchase-orders/vendor/purchase-orders-list-links.twig', ['page' => $page, 'config' => $config, 'purchaseorders' => $orders, 'orderpage' => '']);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'pagenbr' => $input->pageNum, 'resultscount'=> $orders->getNbResults()]);
	include __DIR__ . "/basic-page.php";
