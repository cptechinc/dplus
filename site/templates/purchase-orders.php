<?php
	$config->po = ConfigPoQuery::create()->findOne();
	$html = $modules->get('HtmlWriter');
	$filter = $modules->get('FilterPurchaseOrders');
	$filter->init_query($user);
	$filter->filter_input($input);
	$filter->apply_sortby($page);
	$query = $filter->get_query();
	$orders = $query->paginate($input->pageNum, 10);

	$page->body = $config->twig->render('purchase-orders/page.twig', ['page' => $page, 'input' => $input, 'config' => $config, 'orders' => $orders, 'orderpage' => $pages->get('pw_template=purchase-order-view')->url]);
	include __DIR__ . "/basic-page.php";
