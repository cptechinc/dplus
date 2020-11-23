<?php
	$config->po = ConfigPoQuery::create()->findOne();
	$html = $modules->get('HtmlWriter');
	$filter = $modules->get('FilterPurchaseOrders');
	$filter->init_query($user);
	$filter->filter_input($input);
	$filter->apply_sortby($page);
	$query = $filter->get_query();
	$orders = $query->paginate($input->pageNum, 10);

	$page->body = $config->twig->render('purchase-orders/search-form.twig', ['page' => $page, 'input' => $input]);
	$page->body .= $html->h3('', $orders->getNbResults() . " Purchase Orders");
	$page->body .= $config->twig->render('purchase-orders/purchase-orders-list-links.twig', ['page' => $page, 'config' => $config, 'purchaseorders' => $orders, 'orderpage' => $pages->get('pw_template=purchase-order-view')->url]);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'pagenbr' => $input->pageNum, 'resultscount'=> $orders->getNbResults()]);
	$page->js   .= $config->twig->render('purchase-orders/list.js.twig');
	include __DIR__ . "/basic-page.php";
