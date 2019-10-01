<?php
	$filter_saleshistory = $modules->get('FilterSalesHistory');
	$filter_saleshistory->init_query($user);
	$filter_saleshistory->filter_query($input);
	$query = $filter_saleshistory->get_query();
	$query->orderByDate_ordered('DESC');

	$orders = $query->paginate($input->pageNum, 10);

	$page->body = $config->twig->render('sales-orders/sales-history/search-form.twig', ['page' => $page, 'input' => $input]);
	$page->body .= $config->twig->render('sales-orders/sales-history/sales-history-list.twig', ['orders' => $orders, 'orderpage' => $pages->get('pw_template=sales-order-view')->url]);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'pagenbr' => $input->pageNum, 'resultscount'=> $orders->getNbResults()]);
	include __DIR__ . "/basic-page.php";
