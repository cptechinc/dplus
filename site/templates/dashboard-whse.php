<?php
	$filter_salesorders = $modules->get('FilterSalesOrders');
	$filter_salesorders->init_query($user);
	$filter_salesorders->filter_query($input);
	$query = $filter_salesorders->get_query();
	$query->orderByDate_ordered('DESC');
	$orders = $query->paginate($input->pageNum, 10);

	$twig_params['orders']       = $orders;
	$twig_params['orders_count'] = $orders->getNbResults();
	$twig_params['url_order']    = $pages->get('pw_template=sales-order-view')->url;
	$twig_params['url_order_list']    = $pages->get('pw_template=sales-orders')->url;

	$filter_saleshistory = $modules->get('FilterSalesHistory');
	$filter_saleshistory->init_query($user);
	$filter_saleshistory->filter_query($input);
	$query = $filter_saleshistory->get_query();
	$query->orderByDate_ordered('DESC');

	$invoices = $query->paginate($input->pageNum, 10);
	$twig_params['invoices']         = $invoices;
	$twig_params['invoices_count']   = $invoices->getNbResults();
	$twig_params['url_invoice']      = $pages->get('pw_template=sales-order-view')->url;
	$twig_params['url_invoice_list'] = $pages->get('pw_template=sales-history-orders')->url;

	$twig_params['module_useractions'] = $modules->get('FilterUserActions');
	$twig_params['user']  = $user;
	$twig_params['input'] = $input;

	$page->body = $config->twig->render("dashboard/$user->dplusrole.twig", $twig_params);
	include __DIR__ . "/basic-page.php";
