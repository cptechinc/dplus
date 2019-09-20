<?php
	use Propel\Runtime\ActiveQuery\Criteria;

	$filter_salesorders = $modules->get('FilterSalesOrders');
	$filter_salesorders->init_query($user);

	$filter_salesorders->filter_query($input);
	$query = $filter_salesorders->get_query();

	$module_customer_salesorders = $modules->get('SalesOrdersCustomer');
	$module_customer_salesorders->set_custID($input->get->text('custID'));

	$customer = $module_customer_salesorders->get_customer();
	$page->title = "$customer->name Sales Orders";

	if ($input->get->shiptoID) {
		$module_customer_salesorders->set_shiptoID($input->get->text('shiptoID'));
		$shipto = $module_customer_salesorders->get_shipto();
		$page->title = "$shipto->name Sales Orders";
	}

	$query->orderByDate_ordered('DESC');
	$orders = $query->paginate($input->pageNum, 10);
	$page->body = $config->twig->render('sales-orders/customer/search-form.twig', ['page' => $page, 'input' => $input, 'custid' => $custID]);
	$page->body .= $config->twig->render('sales-orders/customer/sales-order-list.twig', ['orders' => $orders, 'orderpage' => $pages->get('pw_template=sales-order-view')->url]);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'pagenbr' => $input->pageNum, 'resultscount'=> $orders->getNbResults()]);
	include __DIR__ . "/basic-page.php";
