<?php
	$twig_params = array();
	$query = SalesOrderQuery::create();
	$orders = $query->limit(10)->filterbySalesPerson('RDB')->orderByOehdordrdate('DESC')->find();

	$orders_count = SalesOrderQuery::create()->filterbySalesPerson('RDB')->count();
	$twig_params['orders']       = $orders;
	$twig_params['orders_count'] = $orders_count;
	$twig_params['url_order']    = $pages->get('pw_template=sales-order-view')->url;
	$twig_params['url_order_list']    = $pages->get('pw_template=sales-orders')->url;

	$query = SalesHistoryQuery::create();
	$invoices = $query->limit(10)->find();
	$invoices_count = SalesHistoryQuery::create()->count();
	$twig_params['invoices']         = $invoices;
	$twig_params['invoices_count']   = $invoices_count;
	$twig_params['url_invoice']      = $pages->get('pw_template=sales-order-view')->url;
	$twig_params['url_invoice_list'] = $pages->get('pw_template=sales-history-orders')->url;

	$twig_params['module_useractions'] = $modules->get('FilterUserActions');
	$twig_params['user']  = $user;
	$twig_params['input'] = $input;

	$twig_params['dpluso'] = $dpluso;

	$page->body = $config->twig->render("dashboard/$user->dplusrole.twig", $twig_params);

	include __DIR__ . "/basic-page.php";
