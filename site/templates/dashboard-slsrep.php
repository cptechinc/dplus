<?php
	$module_dashboard = $modules->get('Dashboard');

	$params_useractions['module_useractions'] = $modules->get('FilterUserActions');
	$params_useractions['user']  = $user;
	$params_useractions['input'] = $input;
	$page->body .= $config->twig->render("dashboard/components/user-actions.twig", $params_useractions);

	$filter_bookings = $modules->get('FilterBookings');

	if ($filter_bookings->data['option_bookings_dashboard']) {
		$modules->get('DpagesBookings')->init_bookingspage();
		$filter_bookings->set_user($user);
		$bookings = $filter_bookings->bookings_user->get_bookings($input)->find();
		$bookings_customer = $filter_bookings->bookings_customer->get_bookings_by_customer($input)->find();

		$page->body .= $config->twig->render("dashboard/components/bookings.twig", ['page' => $page, 'bookings' => $bookings, 'customers' => $bookings_customer, 'interval' => $filter_bookings->bookings_user->interval, 'todaysbookings' => $filter_bookings->bookings_user->get_todaysbookingstotal()]);
		$page->js .= $config->twig->render("bookings/user/js/bookings.js.twig", ['page' => $page, 'bookingsdata' => $filter_bookings->convert_bookings_for_js($bookings), 'interval' => $filter_bookings->bookings_user->interval]);
	}

	$customers_topselling = $module_dashboard->get_top_x_customers(25);
	$piedata = array();

	foreach ($customers_topselling as $cust) {
		$piedata[] = array(
			'label'  => $cust->get_name(),
			'custid' => $cust->custid,
			'value'  => $cust->amountsold
		);
	}

	$page->body .= $config->twig->render("dashboard/components/top-selling-customers.twig", ['page' => $page, 'customers' => $customers_topselling]);
	$page->js .= $config->twig->render("dashboard/components/js/top-selling.js.twig", ['piedata' => $piedata, 'module_dashboard' => $module_dashboard]);

	$config->styles->append(hash_templatefile('styles/lib/morris.css'));
	$config->scripts->append(hash_templatefile('scripts/lib/raphael.js'));
	$config->scripts->append(hash_templatefile('scripts/lib/morris.js'));


	$filter_salesorders = $modules->get('FilterSalesOrders');
	$filter_salesorders->init_query($user);
	$filter_salesorders->filter_query($input);
	$query = $filter_salesorders->get_query();
	$query->orderByDate_ordered('DESC');
	$orders = $query->paginate($input->pageNum, 10);

	$params_order['orders']         = $orders;
	$params_order['count']          = $orders->getNbResults();
	$params_order['url_order']      = $pages->get('pw_template=sales-order-view')->url;
	$params_order['url_order_list'] = $pages->get('pw_template=sales-orders')->url;
	$page->body .= $config->twig->render("dashboard/components/sales-orders.twig", $params_order);

	$filter_saleshistory = $modules->get('FilterSalesHistory');
	$filter_saleshistory->init_query($user);
	$filter_saleshistory->filter_query($input);
	$query = $filter_saleshistory->get_query();
	$query->orderByDate_ordered('DESC');
	$invoices = $query->paginate($input->pageNum, 10);

	$params_invoice['invoices']         = $invoices;
	$params_invoice['count']   = $invoices->getNbResults();
	$params_invoice['url_invoice']      = $pages->get('pw_template=sales-order-view')->url;
	$params_invoice['url_invoice_list'] = $pages->get('pw_template=sales-history-orders')->url;
	$page->body .= $config->twig->render("dashboard/components/sales-history.twig", $params_invoice);

	$filter_quotes = $modules->get('FilterQuotes');
	$filter_quotes->init_query($user);
	$filter_quotes->filter_query($input);
	$query = $filter_quotes->get_query();
	$query->orderByDate_quoted('DESC');
	$quotes = $query->paginate($input->pageNum, 10);

	$params_quote['quotes']         = $quotes;
	$params_quote['count']          = $quotes->getNbResults();
	$params_quote['url_quote']      = $pages->get('pw_template=quote-view')->url;
	$params_quote['url_quote_list'] = $pages->get('pw_template=quotes')->url;
	$page->body .= $config->twig->render("dashboard/components/quotes.twig", $params_quote);

	include __DIR__ . "/basic-page.php";
