<?php
	$html = $modules->get('HtmlWriter');
	$filter_purchasehistory = $modules->get('FilterApInvoices');
	$filter_purchasehistory->init_query($user);
	$filter_purchasehistory->filter_query($input);
	$query = $filter_purchasehistory->get_query();
	$query->orderByDate_invoiced('DESC');
	$invoices = $query->paginate($input->pageNum, 10);

	$vendorID = $input->get->text('vendorID');
	$load_vendor = $modules->get('ViLoadVendorShipfrom');
	$load_vendor->set_vendorID($vendorID);
	$vendor = $load_vendor->get_vendor();

	$page->title = "$vendor->name Purchase History";
	$invpage = $pages->get('pw_template=purchase-order-view')->url;
	
	//$page->body = $config->twig->render('purchase-historys/search-form.twig', ['page' => $page, 'input' => $input]);
	$page->body .= $html->h3('', $invoices->getNbResults() . " Invoices");
	$page->body .= $config->twig->render('purchase-orders/invoices/invoices-list.twig', ['invoices' => $invoices, 'invpage' => $invpage]);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'pagenbr' => $input->pageNum, 'resultscount'=> $invoices->getNbResults()]);
	include __DIR__ . "/basic-page.php";
