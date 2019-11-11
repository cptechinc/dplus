<?php
	$html = $modules->get('HtmlWriter');
	$filter_purchaseorders = $modules->get('FilterPurchaseOrders');
	$filter_purchaseorders->init_query($user);
	$filter_purchaseorders->filter_query($input);
	$query = $filter_purchaseorders->get_query();
	$query->orderByDate_ordered('DESC');
	$orders = $query->paginate($input->pageNum, 10);

	//$page->body = $config->twig->render('purchase-orders/search-form.twig', ['page' => $page, 'input' => $input]);
	$page->body .= $html->h3('', $orders->getNbResults() . " Purchase Orders");
	$page->body .= $config->twig->render('purchase-orders/purchase-orders-list.twig', ['purchaseorders' => $orders, 'orderpage' => '']);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'pagenbr' => $input->pageNum, 'resultscount'=> $orders->getNbResults()]);
	include __DIR__ . "/basic-page.php";
