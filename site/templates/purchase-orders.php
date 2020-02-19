<?php
	$html = $modules->get('HtmlWriter');
	$filter_purchaseorders = $modules->get('FilterPurchaseOrders');
	$filter_purchaseorders->init_query($user);
	$filter_purchaseorders->filter_query($input);
	$filter_purchaseorders->apply_sortby($page);
	$query = $filter_purchaseorders->get_query();
	$orders = $query->paginate($input->pageNum, 10);

	$page->body = $config->twig->render('purchase-orders/search-form.twig', ['page' => $page, 'input' => $input]);
	$page->body .= $html->h3('', $orders->getNbResults() . " Purchase Orders");
	$page->body .= $config->twig->render('purchase-orders/purchase-orders-list-links.twig', ['page' => $page, 'purchaseorders' => $orders, 'orderpage' => $pages->get('pw_template=purchase-order-view')->url]);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'pagenbr' => $input->pageNum, 'resultscount'=> $orders->getNbResults()]);
	include __DIR__ . "/basic-page.php";
