<?php
	$html = $modules->get('HtmlWriter');
	$filter_purchasehistory = $modules->get('FilterApInvoices');
	$filter_purchasehistory->init_query($user);
	$filter_purchasehistory->filter_query($input);
	$filter_purchasehistory->apply_sortby($page);
	$query = $filter_purchasehistory->get_query();
	$invoices = $query->paginate($input->pageNum, 10);

	$invpage = $pages->get('pw_template=purchase-order-view')->url;

	$page->body = $config->twig->render('purchase-orders/invoices/search-form.twig', ['page' => $page, 'input' => $input]);
	$page->body .= $html->h3('', $invoices->getNbResults() . " Invoices");
	$page->body .= $config->twig->render('purchase-orders/invoices/invoices-list-links.twig', ['page' => $page, 'invoices' => $invoices, 'invpage' => $invpage]);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'pagenbr' => $input->pageNum, 'resultscount'=> $invoices->getNbResults()]);
	include __DIR__ . "/basic-page.php";
