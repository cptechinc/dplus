<?php
	$config_salesorders = $modules->get('SalesOrdersConfig');
	$modules->get('DplusoPagesSalesOrder')->init_salesorder_hooks();
	$http = new ProcessWire\WireHttp();

	if ($input->get->ordn) {
		$ordn = $input->get->text('ordn');

		if (SalesOrderQuery::create()->filterByOrdernumber($ordn)->count()) {
			$order = OrdrhedQuery::create()->findOneBySessionidOrder(session_id(), $ordn);
			$order_items = OrdrdetQuery::create()->filterBySessionidOrder(session_id(), $ordn)->find();
			$customer = CustomerQuery::create()->findOneByCustid($order->custid);
			$page->title = "Editing Sales Order #$ordn";
			$page->listpage = $pages->get('pw_template=sales-orders');
			$page->formurl = $pages->get('template=dplus-menu')->child('template=redir')->url;

			$page->body .= $config->twig->render('sales-orders/sales-order/edit/links-header.twig', ['page' => $page, 'user' => $user, 'order' => $order]);
			$page->body .= $config->twig->render('sales-orders/sales-order/edit/sales-order-header.twig', ['page' => $page, 'customer' => $customer, 'order' => SalesOrderQuery::create()->findOneByOrdernumber($ordn)]);

			if ($user->is_editingorder($order->ordernumber)) {
				$states = StatesQuery::create()->select(['name', 'abbreviation'])->find();
				$shipvias = ShipviaQuery::create()->find();

				$page->body .= $config->twig->render('sales-orders/sales-order/edit/edit-form.twig', ['page' => $page, 'order' => $order, 'states' => $states, 'shipvias' => $shipvias]);
			}
			$page->body .= $config->twig->render('sales-orders/sales-order/edit/order-items.twig', ['page' => $page,'order' => $order, 'order_items' => $order_items, 'user' => $user]);

			if ($user->is_editingorder($ordn)) {
				$config->scripts->append(hash_templatefile('scripts/orders/edit-order.js'));
			}
		} elseif (SalesHistoryQuery::create()->filterByOrdernumber($ordn)->count()) {
			$page->headline = $page->title = "Sales Order #$ordn is not editable";
			$page->body = $config->twig->render('util/error-page.twig', ['msg' => "Sales Order #$ordn is in Sales History"]);
		} else {
			$page->headline = $page->title = "Sales Order #$ordn could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['msg' => "Check if the Order Number is correct or if it is in Sales History"]);
		}
	} else {
		$page->body = $config->twig->render('sales-orders/sales-order-lookup.twig', ['page' => $page]);
	}
	include __DIR__ . "/basic-page.php";
