<?php
	$config_salesorders = $modules->get('ConfigsSalesOrders');
	$modules->get('DpagesMso')->init_salesorder_hooks();
	$html = $modules->get('HtmlWriter');
	$http = new ProcessWire\WireHttp();

	if ($input->get->ordn) {
		$ordn = $input->get->text('ordn');

		if (SalesOrderQuery::create()->filterByOrdernumber($ordn)->count()) {
			if (!OrdrhedQuery::create()->filterBySessionidOrder(session_id(), $qnbr)->count()) {
				$http->get($page->edit_orderURL($ordn));
			}
			$module_edit = $modules->get('SalesOrderEdit');
			$module_edit->set_ordn($ordn);
			$order = $module_edit->get_order_edit();
			$order_items = OrdrdetQuery::create()->filterBySessionidOrder(session_id(), $ordn)->find();
			$customer = CustomerQuery::create()->findOneByCustid($order->custid);
			$page->title = "Editing Sales Order #$ordn";
			$page->listpage = $pages->get('pw_template=sales-orders');
			$page->formurl = $pages->get('template=dplus-menu, name=mso')->child('template=redir')->url;
			$page->lookupURL = $pages->get('pw_template=ii-item-lookup')->httpUrl;

			$page->body .= $config->twig->render('sales-orders/sales-order/edit/links-header.twig', ['page' => $page, 'user' => $user, 'order' => $order]);
			$page->body .= $config->twig->render('sales-orders/sales-order/edit/sales-order-header.twig', ['page' => $page, 'customer' => $customer, 'order' => $module_edit->get_order_static()]);

			if ($user->is_editingorder($order->ordernumber)) {
				$page->body .= $config->twig->render('sales-orders/sales-order/edit/edit-form.twig', ['page' => $page, 'order' => $order, 'states' => $module_edit->get_states(), 'shipvias' => $module_edit->get_shipvias(), 'warehouses' => $module_edit->get_warehouses(), 'shiptos' => $customer->get_shiptos()]);
			}
			$page->body .= $config->twig->render('sales-orders/sales-order/edit/order-items.twig', ['page' => $page,'order' => $order, 'order_items' => $order_items, 'user' => $user]);

			if ($user->is_editingorder($order->ordernumber)) {
				$page->body .= $html->h3('class=text-secondary', 'Add Item');
				$page->body .= $config->twig->render('sales-orders/sales-order/edit/add-item-form.twig', ['page' => $page, 'order' => $order]);
				$page->js .= $config->twig->render('sales-orders/sales-order/edit/item-lookup.js.twig', ['page' => $page]);

				if ($input->get->q) {
					$q = $input->get->text('q');
					$module_edit->request_itemsearch($q);
					$results = PricingQuery::create()->findBySessionid(session_id());
					$page->body .= $config->twig->render('cart/lookup-results.twig', ['q' => $q, 'results' => $results]);
				}

				$page->body .= $config->twig->render('util/js-variables.twig', ['variables' => array('shiptos' => $module_edit->get_shiptos_json_array())]);
				$config->scripts->append(hash_templatefile('scripts/orders/edit-order.js'));
				$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
			}
			$page->body .= $html->div('class=mb-3');
			$notes = SalesOrderNotesQuery::create()->filterByOrdernumber($ordn)->filterByLine(0)->find();
			$page->body .= $config->twig->render('sales-orders/sales-order/qnotes.twig', ['page' => $page, 'notes' => $notes, 'ordn' => $ordn]);
			$page->body .= $config->twig->render('sales-orders/sales-order/notes/add-note-modal.twig', ['page' => $page, 'ordn' => $onrd]);
			$config->scripts->append(hash_templatefile('scripts/quotes/quote-notes.js'));
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
