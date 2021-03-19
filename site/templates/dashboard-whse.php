<?php
	$filter_salesorders = new Dplus\Filters\Mso\SalesOrder();
	$filter_salesorders->user($user);
	$filter_salesorders->filterInput($input);
	$filter_salesorders->query->orderByDate_ordered('DESC');
	$orders = $filter_salesorders->query->paginate($input->pageNum, 10);

	$twig_params['orders']       = $orders;
	$twig_params['orders_count'] = $orders->getNbResults();
	$twig_params['url_order']    = $pages->get('pw_template=sales-order-view')->url;
	$twig_params['url_order_list']    = $pages->get('pw_template=sales-orders')->url;

	$filter_saleshistory = new Dplus\Filters\Mso\SalesHistory();
	$filter_saleshistory->user($user);
	$filter_saleshistory->filterInput($input);
	$filter_saleshistory->query->orderByDate_ordered('DESC');

	$invoices = $filter_saleshistory->query->paginate($input->pageNum, 10);
	$twig_params['invoices']         = $invoices;
	$twig_params['invoices_count']   = $invoices->getNbResults();
	$twig_params['url_invoice']      = $pages->get('pw_template=sales-order-view')->url;
	$twig_params['url_invoice_list'] = $pages->get('pw_template=sales-history-orders')->url;

	$twig_params['module_useractions'] = $modules->get('FilterUserActions');
	$twig_params['user']  = $user;
	$twig_params['input'] = $input;

	$page->body = $config->twig->render("dashboard/$user->dplusrole.twig", $twig_params);
	include __DIR__ . "/basic-page.php";
