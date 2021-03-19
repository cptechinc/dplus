<?php
	$filter = new Dplus\Filters\Mso\SalesOrder();
	$filter->user($user);
	$filter->filterInput($input);
	$filter->sortby($page);
	$orders = $filter->query->paginate($input->pageNum, 10);

	$page->body = $config->twig->render('sales-orders/search-form.twig', ['page' => $page, 'input' => $input]);
	$page->body .= $config->twig->render('sales-orders/sales-orders-list-links.twig', ['page' => $page, 'orders' => $orders, 'orderpage' => $pages->get('pw_template=sales-order-view')->url]);
	$page->body .= '<div class="mb-3"></div>';
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'pagenbr' => $input->pageNum, 'resultscount'=> $orders->getNbResults()]);
	include __DIR__ . "/basic-page.php";
