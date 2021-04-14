<?php
	$filter = new Dplus\Filters\Mso\SalesHistory();
	$filter->user($user);
	$filter->filterInput($input);
	if ($page->has_orderby) {
		$filter->sortby($page);
	}

	if ($page->has_orderby === false) {
		$filter->query->orderByDate_ordered('DESC');
	}

	$orders = $filter->query->paginate($input->pageNum, 10);

	$page->body = $config->twig->render('sales-orders/sales-history/search-form.twig', ['page' => $page, 'input' => $input]);
	$page->body .= $config->twig->render('sales-orders/sales-history/sales-history-list-links.twig', ['page' => $page, 'orders' => $orders, 'orderpage' => $pages->get('pw_template=sales-order-view')->url]);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'pagenbr' => $input->pageNum, 'resultscount'=> $orders->getNbResults()]);
	include __DIR__ . "/basic-page.php";
