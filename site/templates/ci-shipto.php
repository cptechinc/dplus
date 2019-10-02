<?php
	include_once('./ci-include.php');

	if ($customerquery->count()) {
		$page->show_breadcrumbs = false;

		if ($input->get->shiptoID) {
			$shiptoID = $input->get->text('shiptoID');
			$load_shipto = $load_customer;
			$query_shipto = CustomerShiptoQuery::create()->filterByCustid($custID)->filterByShiptoid($shiptoID);

			if ($load_shipto->shipto_exists()) {
				$shipto = $load_shipto->get_shipto();
				$page->title = "$shipto->id";
				$page->body .= $config->twig->render('customers/ci/bread-crumbs.twig', ['page' => $page, 'customer' => $customer]);
				$page->title = "$customer->name Ship-to: $shipto->id";

				$function_pages = $pages->find('pw_template=ci-contacts');
				$toolbar = $config->twig->render('customers/ci/shiptos/toolbar.twig', ['shipto' => $shipto, 'pages' => $function_pages]);
				$header  = $config->twig->render('customers/ci/shiptos/header.twig', ['page' => $page, 'customer' => $customer, 'shipto' => $shipto, 'module_shipto' => $load_shipto]);

				$page->body .= $config->twig->render('customers/ci/ci-links.twig', ['page' => $page]);
				$page->body .= "<div class='row'>";
					$page->body .= $html->div('class=col-sm-2', $toolbar);
					$page->body .= $html->div('class=col-sm-10', $header);
				$page->body .= "</div>";

				$orders = $load_shipto->get_salesorders();
				$orders_history = $load_shipto->get_saleshistory();
				$page->body .= $config->twig->render('customers/ci/customer/sales-orders-panel.twig', ['page' => $page, 'orders' => $orders, 'resultscount'=> $orders->getNbResults(), 'orderpage' => $pages->get('pw_template=sales-order-view')->url, 'sales_orders_list' => $load_shipto->get_salesordersURL()]);
				$page->body .= $config->twig->render('customers/ci/customer/shipped-orders-panel.twig', ['page' => $page, 'orders' => $orders_history, 'resultscount'=> $orders_history->getNbResults(), 'orderpage' => $pages->get('pw_template=sales-order-view')->url, 'shipped_orders_list' => $load_shipto->get_saleshistoryURL()]);
			} else {
				$page->body = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "$custID Ship-to $shiptoID does not exist", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Check if shiptoID is correct"]);
			}
		} else {
			$page->body .= $config->twig->render('customers/ci/bread-crumbs.twig', ['page' => $page, 'customer' => $customer]);
			$page->title = "Select a $customer->name Ship-to";
			$shiptos = CustomerShiptoQuery::create()->filterByCustid($custID)->find();
			$page->body .= $config->twig->render('customers/ci/shiptos/shipto-list.twig', ['page' => $page, 'customer' => $customer, 'shiptos' => $shiptos]);
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
